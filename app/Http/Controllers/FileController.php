<?php namespace App\Http\Controllers;

use App\Helpers\VideoStream;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use File;
use Image;
use Request;
use Response;
use Storage;

class FileController extends Controller
{
    public function getPreview($one, $two = null, $three = null, $four = null, $five = null)
    {
        $module = CRUDBooster::getCurrentModule();

        if (! CRUDBooster::isView() && $this->global_privilege == false) {
            CRUDBooster::insertLog(trans('crudbooster.log_try_view', ['module' => $module->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        $basic_dir = 'attachments';
        if ($two) {
            $fullFilePath = $basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two;
            $filename = $two;
            if ($three) {
                $fullFilePath = $basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three;
                $filename = $three;
                if ($four) {
                    $fullFilePath = $basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three.DIRECTORY_SEPARATOR.$four;
                    $filename = $four;
                    if ($five) {
                        $fullFilePath = $basic_dir.DIRECTORY_SEPARATOR.$one.DIRECTORY_SEPARATOR.$two.DIRECTORY_SEPARATOR.$three.DIRECTORY_SEPARATOR.$four.DIRECTORY_SEPARATOR.$five;
                        $filename = $five;
                    }
                }
            }
        } else {
            $fullFilePath = $basic_dir.DIRECTORY_SEPARATOR.$one;
            $filename = $one;
        }

        $fullStoragePath = storage_path('app/'.$fullFilePath);
        $lifetime = 31556926; // One year in seconds

        $handler = new \Symfony\Component\HttpFoundation\File\File(storage_path('app/'.$fullFilePath));

        if (! Storage::exists($fullFilePath)) {
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
}
