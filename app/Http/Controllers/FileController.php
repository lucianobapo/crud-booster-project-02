<?php namespace App\Http\Controllers;

use App\Helpers\VideoStream;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use File;
use Ramsey\Uuid\Uuid;
use Session;
use Image;
use Request;
use Response;
use Storage;
use DB;

class FileController extends Controller
{

    private $basic_dir = 'attachments';

    public function getPreview($one, $two = null, $three = null, $four = null, $five = null, \Illuminate\Http\Request $request)
    {
        if (CRUDBooster::isSuperadmin())
            $this->seedAttachments();

        if ($two) {
            $fullFilePath = $this->basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two;
            $filename = $two;
            if ($three) {
                $fullFilePath = $this->basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three;
                $filename = $three;
                if ($four) {
                    $fullFilePath = $this->basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three.DIRECTORY_SEPARATOR.$four;
                    $filename = $four;
                    if ($five) {
                        $fullFilePath = $this->basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three.DIRECTORY_SEPARATOR.$four.DIRECTORY_SEPARATOR.$five;
                        $filename = $five;
                    }
                }
            }
        }
        else {
            $fullFilePath = $this->basic_dir.DIRECTORY_SEPARATOR.$one;
            $filename = $one;
        }


        if ($this->getFileData($fullFilePath)->check_referer)
            $this->checkReferer($request, $fullFilePath);
        if ($this->getFileData($fullFilePath)->check_permissions)
            $this->checkPermissions($fullFilePath);

        $fullStoragePath = storage_path('app/'.$fullFilePath);
        $lifetime = 31556926; // One year in seconds

        $handler = new \Symfony\Component\HttpFoundation\File\File(storage_path('app/'.$fullFilePath));

        if (! Storage::exists($fullFilePath)) {
            CRUDBooster::insertLog('File not Found: '.$fullFilePath);
            abort(404);
        }


        $extension = strtolower(File::extension($fullStoragePath));
        $images_ext = config('crudbooster.IMAGE_EXTENSIONS', 'jpg,png,gif,bmp');
        $images_ext = explode(',', $images_ext);
        $imageFileSize = 0;

        if (in_array($extension, $images_ext)) {
            $defaultThumbnail = config('crudbooster.DEFAULT_THUMBNAIL_WIDTH');
            if ($defaultThumbnail != 0) {
                $w = Request::get('w') ?: $defaultThumbnail;
                $h = Request::get('h') ?: $w;
            } else {
                $w = Request::get('w');
                $h = Request::get('h') ?: $w;
            }

            $imgRaw = Image::cache(function ($image) use ($fullStoragePath, $w, $h) {
                $im = $image->make($fullStoragePath);
                if ($w) {
                    if (! $h) {
                        $im->fit($w);
                    } else {
                        $im->fit($w, $h);
                    }
                }

                return $im;
            });

            $imageFileSize = mb_strlen($imgRaw, '8bit') ?: 0;
        }

        /**
         * Prepare some header variables
         */
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)

        $header_content_type = $handler->getMimeType();
        $header_content_length = ($imageFileSize) ?: $handler->getSize();
        $header_etag = md5($file_time.$fullFilePath);
        $header_last_modified = gmdate('r', $file_time);
        $header_expires = gmdate('r', $file_time + $lifetime);

        $headers = [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Last-Modified' => $header_last_modified,
            'Cache-Control' => 'must-revalidate',
            'Expires' => $header_expires,
            'Pragma' => 'public',
            'Etag' => $header_etag,
        ];

        /**
         * Is the resource cached?
         */
        $h1 = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified;
        $h2 = isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag;

        $headers = array_merge($headers, [
            'Content-Type' => $header_content_type,
            'Content-Length' => $header_content_length,
        ]);


        if (in_array($extension, $images_ext)) {
            if ($h1 || $h2) {
                return Response::make('', 304, $headers); // File (image) is cached by the browser, so we don't have to send it again
            } else {
                return Response::make($imgRaw, 200, $headers);
            }
        }

        elseif($extension=='mp4'){
            $stream = new VideoStream(storage_path('app/'.$fullFilePath));
            $stream->start();
        }

        else {
            if (Request::get('download')) {
//                return Response::download(storage_path('app/'.$fullFilePath), $filename, $headers);
                return Response::file(storage_path('app/'.$fullFilePath), $headers);
            } else {
                return Response::file(storage_path('app/'.$fullFilePath), $headers);
            }
        }
    }

    private function isView()
    {
        $session = Session::get('admin_privileges_roles');
        if(!empty($session))
            foreach ($session as $v) {
                if ($v->path == $this->basic_dir) {
                    return (bool) $v->is_visible;
                }
            }
        return false;
    }

    private function checkPermissions($fullFilePath)
    {
        if (! $this->isView() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_view', ['module' => $this->basic_dir]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $privilege_id = $this->getFileData($fullFilePath)->cms_privilege_id;

        if($privilege_id != CRUDBooster::myPrivilegeId()){
            CRUDBooster::insertLog(trans('crudbooster.log_try_view', ['module' => $this->basic_dir]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }
    }

    private function checkReferer(\Illuminate\Http\Request $request, string $fullFilePath)
    {
        $referers = $this->getFileData($fullFilePath)->referers;
        $referers = explode(';',$referers);

        $abort = true;
        $header = $request->header('referer');
        logger($header);
        foreach ($referers as $referer) {
            if ($abort)
            $abort = (strpos($header, $referer)===false);

        }

        if($abort){
            CRUDBooster::insertLog('Referer error: '.
                $header .' ::: '.
                $request->getHttpHost().' ::: '.
                $fullFilePath);
            abort(403);
        }


    }

    private function getFileData($fullFilePath)
    {

        $data = $this->firstAttachment($fullFilePath);
        if(empty($data)){
            CRUDBooster::insertLog('File not Found on Database: '.$fullFilePath);
            abort(404);
        }

        return $data;
    }

    private function seedAttachments()
    {
        $attachments = Storage::allFiles($this->basic_dir);

        foreach ($attachments as $attachment) {

            if(empty($this->firstAttachment($attachment))){
                $this->insertAttachment($attachment);
            }

        }
    }

    private function insertAttachment($attachment)
    {
        $result = DB::table('attachments')->insert([
            'id'=>Uuid::uuid4(),
            'owner_id'=>CRUDBooster::me()->owner_id,
            'file'=>$attachment
        ]);
    }

    private function firstAttachment($fullFilePath)
    {
        $data = DB::table($this->basic_dir)
            ->where('file', $fullFilePath)
            ->first();
        return $data;
    }
}
