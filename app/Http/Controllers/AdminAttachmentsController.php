<?php namespace App\Http\Controllers;

	use crocodicstudio\crudbooster\helpers\CB;
    use Illuminate\Support\Facades\Storage;
    use Ramsey\Uuid\Uuid;
    use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use Pbmedia\LaravelFFMpeg\FFMpegFacade as FFMpeg;
	//use Spatie\Glide\GlideImageFacade as GlideImage;

	use Intervention\Image\ImageManagerStatic as Image;
	use GifCreator\GifCreator;
	//use FFMpeg\FFprobe;
	//use FFMpeg\FFMpeg;
	use FFMpeg\Coordinate\TimeCode;

	class AdminAttachmentsController extends CustomController {

	    public function cbInit() {
            $this->uuid_field = true;
			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "attachments";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Arquivo","name"=>"file"];
			$this->col[] = ["label"=>"Check Referer","name"=>"check_referer",'callback_php'=>'($row->check_referer?"Ativado":"Desativado")'];
			$this->col[] = ["label"=>"Referers","name"=>"referers"];
			$this->col[] = ["label"=>"Check Permissions","name"=>"check_permissions",'callback_php'=>'($row->check_permissions?"Ativado":"Desativado")'];
			$this->col[] = ["label"=>"Grupo de Acesso","name"=>"cms_privilege_id","join"=>"cms_privileges,name"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>"Arquivo (max size ".ini_get('upload_max_filesize').")",'name'=>'file','type'=>'file','validation'=>'required','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Check Referer','name'=>'check_referer','type'=>'checkbox','width'=>'col-sm-10','dataenum'=>'1|(Ativar - acesso somente dentro do site)'];
			$this->form[] = ['label'=>'Referers','name'=>'referers','type'=>'text','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Check Permissions','name'=>'check_permissions','type'=>'checkbox','width'=>'col-sm-10','dataenum'=>'1|(Ativar - acesso somente do grupo)'];
			$this->form[] = ['label'=>'Grupo de Acesso','name'=>'cms_privilege_id','type'=>'select2','width'=>'col-sm-10','datatable'=>'cms_privileges,name'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ['label'=>'Arquivo','name'=>'file','type'=>'upload','validation'=>'required','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'Check Referer','name'=>'check_referer','type'=>'checkbox','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'Check Permissions','name'=>'check_permissions','type'=>'checkbox','width'=>'col-sm-10'];
			//$this->form[] = ['label'=>'Grupo de Acesso','name'=>'cms_privilege_id','type'=>'select2','validation'=>'required','width'=>'col-sm-10','datatable'=>'cms_privileges,name'];
			# OLD END FORM

			/* 
	        | ---------------------------------------------------------------------- 
	        | Sub Module
	        | ----------------------------------------------------------------------     
			| @label          = Label of action 
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class  
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        | 
	        */
	        $this->sub_module = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)     
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        | 
	        */
	        $this->addaction = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Button Selected
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button 
	        | Then about the action, you should code at actionButtonSelected method 
	        | 
	        */
	        $this->button_selected = array();

	                
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------     
	        | @message = Text of message 
	        | @type    = warning,success,danger,info        
	        | 
	        */
	        $this->alert        = array();
	                

	        
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add more button to header button 
	        | ----------------------------------------------------------------------     
	        | @label = Name of button 
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        | 
	        */
	        $this->index_button = [];



	        /* 
	        | ---------------------------------------------------------------------- 
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------     
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.        
	        | 
	        */
	        $this->table_row_color = array();     	          

	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | You may use this bellow array to add statistic at dashboard 
	        | ---------------------------------------------------------------------- 
	        | @label, @count, @icon, @color 
	        |
	        */
	        $this->index_statistic = array();



	        /*
	        | ---------------------------------------------------------------------- 
	        | Add javascript at body 
	        | ---------------------------------------------------------------------- 
	        | javascript code in the variable 
	        | $this->script_js = "function() { ... }";
	        |$( '#result' ).load( 'http://crudbooster.localhost.com/attachments/2019-02/logo2.png' );
	        */
	        $this->script_js = "";


            /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code before index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
	        $this->pre_index_html = null;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code after index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = "";
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include Javascript File 
	        | ---------------------------------------------------------------------- 
	        | URL of your javascript each array 
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
	        $this->load_js = array();
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Add css style at body 
	        | ---------------------------------------------------------------------- 
	        | css code in the variable 
	        | $this->style_css = ".style{....}";
	        |
	        */
	        $this->style_css = NULL;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include css File 
	        | ---------------------------------------------------------------------- 
	        | URL of your css each array 
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
	        $this->load_css = array();
	        
	        
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for button selected
	    | ---------------------------------------------------------------------- 
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate query of index result 
	    | ---------------------------------------------------------------------- 
	    | @query = current sql query 
	    |
	    */
	    public function hook_query_index(&$query) {
	        //Your code here
            if (CRUDBooster::isSuperadmin())
                $this->seedAttachments();
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate row of index table html 
	    | ---------------------------------------------------------------------- 
	    |
	    */    
	    public function hook_row_index($column_index,&$column_value) {	        
	    	//Your code here
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before add data is execute
	    | ---------------------------------------------------------------------- 
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {        
	        //Your code here

            foreach ($this->data_inputan as $ro) {
                if($ro['type']=='file'){
                    $postdata[$ro['name']] = $this->uploadFile($ro['name'],
                        $ro['encrypt'] || $ro['upload_encrypt'],
                        $ro['resize_width'],
                        $ro['resize_height'], CB::myId(), 'attachments/'.date('Y-m'));

                    if (! $postdata[$ro['name']]) {
                        $postdata[$ro['name']] = Request::get('_'.$ro['name']);
                    }
                }
            }


            if(!isset($postdata['check_referer'])) $postdata['check_referer']=false;
            if(!isset($postdata['check_permissions'])) $postdata['check_permissions']=false;


	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after add public static function called 
	    | ---------------------------------------------------------------------- 
	    | @id = last insert id
	    | 
	    */
	    public function hook_after_add($id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before update data is execute
	    | ---------------------------------------------------------------------- 
	    | @postdata = input post data 
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            if(!isset($postdata['check_referer'])) $postdata['check_referer']=false;
            if(!isset($postdata['check_permissions'])) $postdata['check_permissions']=false;
	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_edit($id) {
	        //Your code here 

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_delete($id) {
	        //Your code here
            $attachment = $this->firstByID($id);
            if (!$attachment) $this->abort('Data Not Found', 404);

            if(Storage::exists($attachment->file))
                Storage::delete($attachment->file);

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_delete($id) {
	        //Your code here

	    }



	    //By the way, you can still create your own method in here... :) 

        public function uploadFile($name, $encrypt = false, $resize_width = null, $resize_height = null, $id = null, $filePath = null)
        {
            function getMyId($id){
                if (! CRUDBooster::myId()) {
                    $userID = 0;
                } else {
                    $userID = CRUDBooster::myId();
                }

                if ($id) {
                    $userID = $id;
                }
                return $userID;
            }


            if (Request::hasFile($name)) {
                $userID = getMyId($id);

                $file = Request::file($name);
                $ext = $file->getClientOriginalExtension();
                $filename = str_slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $filesize = $file->getClientSize() / 1024;

                if (is_null($filePath))
                    $file_path = 'uploads/'.$userID.'/'.date('Y-m');
                else
                    $file_path = $filePath;

                        //Create Directory Monthly
                Storage::makeDirectory($file_path);

                if ($encrypt == true) {
                    $filename = md5(str_random(5)).'.'.$ext;
                } else {
                    $filename = str_slug($filename, '_').'.'.$ext;
                }

                if (Storage::putFileAs($file_path, $file, $filename)) {
//                    CRUDBooster::resizeImage($file_path.'/'.$filename, $resize_width, $resize_height);

                    return $file_path.'/'.$filename;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        private function seedAttachments()
        {
            $attachments = Storage::allFiles($this->table);
            foreach ($attachments as $attachment) {

            	$this->createThumbnails($attachment);            	

                if(empty($this->firstAttachment($attachment))){
                    $this->insertAttachment($attachment);
                }

            }
        }

        private function insertAttachment($attachment)
        {
            $result = DB::table($this->table)->insert([
                'id'=>Uuid::uuid4(),
                'owner_id'=>CRUDBooster::me()->owner_id,
                'file'=>$attachment
            ]);
        }

        private function firstAttachment($fullFilePath)
        {
            $data = DB::table($this->table)
                ->where('file', $fullFilePath)
                ->first();
            return $data;
        }

        private function firstByID($id)
        {
            $data = DB::table($this->table)
                ->where('id', $id)
                ->first();
            return $data;
        }

        private function abort($message = '', $code = 403)
        {
            logger($message);
            CRUDBooster::insertLog($message);
            abort($code);
        }

        private function checkFfmpeg()
        {
            $conf_ffmpeg = config('laravel-ffmpeg.ffmpeg.binaries');
            return (is_string($conf_ffmpeg) 
            	&& is_file($conf_ffmpeg) 
            	&& is_executable($conf_ffmpeg));
        }

        private function checkAttach($attachment)
        {
        	$extension = pathinfo(Storage::path($attachment), PATHINFO_EXTENSION);
        	return (Storage::exists($attachment) && $extension=='avi');
        }

        private function createThumbnails($attachment)
        {
        	if (!$this->checkAttach($attachment)) {
        		logger('checkAttach failed: '.$attachment);
        		return null;
        	}
        	
        	if (!$this->checkFfmpeg()) {
        		logger('checkFfmpeg failed');
        		return null;
        	}

        	$video_path = Storage::path($attachment);
        	$thumb_path = storage_path('app/attachments/thumnails');

        	//if ($this->checkAttach($attachment))
        	$this->build_video_thumbnail($video_path, $thumb_path);


        	/*
        	if($this->checkFfmpeg() && $this->checkAttach($attachment)){
				$video_opened = FFMpeg::fromDisk('local')
			    ->open($attachment);

			    $durationInSeconds = $video_opened->getDurationInSeconds();
			    $frame_sec_thumb = intdiv($durationInSeconds,10);
			    $thumb_name = $attachment.'-thumb-'.$frame_sec_thumb.'.png';
			    $thumb_name_resized = Storage::path($thumb_name).'-resized.png';

			    if (!is_file($thumb_name_resized)){
					$video_opened->getFrameFromSeconds($frame_sec_thumb)
				    ->export()
					->toDisk('local')
				    ->save($thumb_name);

				    if (Storage::exists($thumb_name)){
				    	GlideImage::create(Storage::path($thumb_name))
							->modify(['w'=> 200,'h'=> 200, 'fit'=>'fill'])
							->save($thumb_name_resized);
						Storage::delete($thumb_name);
				    } else logger('file not found for GlideImage: '.$thumb_name);
					    
			    } else logger('file already thumbed: '.$thumb_name_resized);	    
			    
			}*/
        }

        public function build_video_thumbnail($video_path, $thumb_path) {

		    // Create a temp directory for building.
		    $temp = sys_get_temp_dir() . "/build";

		    // Use FFProbe to get the duration of the video.
		    /*$ffprobe = FFprobe::create();
		    $duration = floor($ffprobe
		        ->format($video_path)
		        ->get('duration'));*/
		    $video_opened = FFMpeg::fromDisk('local')
			    ->open($video_path);
		    $duration = $video_opened->getDurationInSeconds();    

		    // If we couldn't get the direction or it was zero, exit.		    
		    if (empty($duration)) {
		        return;
		    }

		    // Create an FFMpeg instance and open the video.
		    //$ffmpeg = FFMpeg::create();
		    $video = $video_opened;

		    // This array holds our "points" that we are going to extract from the
		    // video. Each one represents a percentage into the video we will go in
		    // extracitng a frame. 0%, 10%, 20% ..
		    $points = range(0, 100, 10);

		    // This will hold our finished frames.
		    $frames = [];

		    foreach ($points as $point) {

		        // Point is a percent, so get the actual seconds into the video.
		        $time_secs = floor($duration * ($point / 100));

		        // Created a var to hold the point filename.
		        $point_file = "$temp/$point.jpg";

		        // Extract the frame.
		        $frame = $video->frame(TimeCode::fromSeconds($time_secs));
		        $frame->save($point_file);

		        // If the frame was successfully extracted, resize it down to
		        // 320x200 keeping aspect ratio.
		        if (file_exists($point_file)) {
		            $img = Image::make($point_file)->resize(300, 200, function ($constraint) {
		                $constraint->aspectRatio();
		                $constraint->upsize();
		            });

		            $img->save($point_file, 40);
		            $img->destroy();
		        }

		        // If the resize was successful, add it to the frames array.
		        if (file_exists($point_file)) {
		            $frames[] = $point_file;
		        }
		    }

		    // If we have frames that were successfully extracted.
		    if (!empty($frames)) {

		        // We show each frame for 100 ms.
		        $durations = array_fill(0, count($frames), 100);

		        // Create a new GIF and save it.
		        $gc = new GifCreator();
		        $gc->create($frames, $durations, 0);
		        file_put_contents($thumb_path, $gc->getGif());

		        // Remove all the temporary frames.
		        foreach ($frames as $file) {
		            unlink($file);
		        }
		    }
		}
    }