<?php namespace App\Http\Controllers;

	use crocodicstudio\crudbooster\helpers\CRUDBooster;
    use Session;
	use Request;
	use DB;

	class AdminPartners14Controller extends CustomController {

        public static function showDates($row){
            $ll = DB::select(
                'Select dates.date, date_types.type  FROM dates 
                INNER JOIN partners 
                ON partners.id = dates.partner_id
                 INNER JOIN date_types 
                ON date_types.id = dates.date_type_id 
                 WHERE partners.id = :id'
                , ['id' => $row->id]);
            $html = '';
            if(count($ll)!==0)
            {
                logger($ll);
                foreach($ll as $k){
                    $html .= $k->type .': '.$k->date .', ';
                }
                $html = substr($html, 0, -2);
            }else $html = 'Sem datas';
            return $html;
        }
        public static function showDocuments($row){
            $ll = DB::select(
                'Select documents.document_number, document_types.type  FROM documents 
                INNER JOIN partners 
                ON partners.id = documents.partner_id
                 INNER JOIN document_types 
                ON document_types.id = documents.document_type_id 
                 WHERE partners.id = :id'
                , ['id' => $row->id]);
            $html = '';
            if(count($ll)!==0)
            {
                logger($ll);
                foreach($ll as $k){
                    $html .= $k->type .': '.$k->document_number .', ';
                }
                $html = substr($html, 0, -2);
            }else $html = 'Sem documentos';
            return $html;
        }
        public static function showMatricula($row){
            $ll = DB::select(
                'Select documents.document_number, document_types.type  FROM documents 
                INNER JOIN partners 
                ON partners.id = documents.partner_id
                 INNER JOIN document_types 
                ON document_types.id = documents.document_type_id 
                 WHERE partners.id = :id AND documents.document_type_id=4'
                , ['id' => $row->id]);
            $html = '';
            if(count($ll)!==0)
            {
                logger($ll);
                foreach($ll as $k){
                    $html .= $k->document_number .', ';
                }
                $html = substr($html, 0, -2);
            }else $html = 'Sem Matrícula';
            return $html;
        }


	    public function cbInit() {
            $this->uuid_field = true;

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "name";
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
			$this->button_import = true;
			$this->button_export = true;
			$this->table = "partners";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
            $this->col[] = ["label"=>"Código","name"=>"cod"];
            $this->col[] = ["label"=>"Matrícula","name"=>"id","callback_php"=>'App\Http\Controllers\AdminPartners14Controller::showMatricula($row)'];
			$this->col[] = ["label"=>"Nome","name"=>"name"];
			$this->col[] = ["label"=>"Tipo de Cadastro","name"=>"partner_type_id","join"=>"partner_types,type"];
			$this->col[] = ["label"=>"Tanque","name"=>"cost_center_id","join"=>"cost_centers,name","callback_php"=>'($row->cost_centers_cod." - ".$row->cost_centers_name)'];
            $this->col[] = ["label"=>"Datas","name"=>"id","callback_php"=>'App\Http\Controllers\AdminPartners14Controller::showDates($row)'];
//            $this->col[] = ["label"=>"Documentos","name"=>"id","callback_php"=>'App\Http\Controllers\AdminPartners14Controller::showDocuments($row)'];


			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Nome','name'=>'name','type'=>'text','validation'=>'required|string|min:3|max:70','width'=>'col-sm-10','placeholder'=>'Você pode digitar somente letras'];
			$this->form[] = ['label'=>'Tipo de Cadastro','name'=>'partner_type_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'partner_types,type'];
			$this->form[] = ['label'=>'Tanque','name'=>'cost_center_id','type'=>'select2','width'=>'col-sm-10','datatable'=>'cost_centers,name',"datatable_format"=>"cod,' - ',name"];

			$columns = [];
            $columns[] = ["showInDetail"=>false,"name"=>"owner_id","type"=>"hidden","value"=>CRUDBooster::me()->owner_id];
            $columns[] = ['label'=>'Data','name'=>'date','type'=>'date','required'=>true];
            $columns[] = ['label'=>'Tipo de Data','name'=>'date_type_id','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'date_types,type','required'=>true];
            $this->form[] = ['label'=>'Datas','name'=>'dates_child','type'=>'child',
                'columns'=>$columns,'table'=>'dates','foreign_key'=>'partner_id'];

            $columns = [];
            $columns[] = ["showInDetail"=>false,"name"=>"owner_id","type"=>"hidden","value"=>CRUDBooster::me()->owner_id];
            $columns[] = ['label'=>'Número do Documento','name'=>'document_number','type'=>'text','validation'=>'required|min:3|max:70','required'=>true];
            $columns[] = ['label'=>'Tipo do Documento','name'=>'document_type_id','type'=>'select','datatable'=>'document_types,type','required'=>true];
            $this->form[] = ['label'=>'Documentos','name'=>'documents_child','type'=>'child',
                'columns'=>$columns,'table'=>'documents','foreign_key'=>'partner_id'];

            $columns = [];
            $columns[] = ["showInDetail"=>false,"name"=>"owner_id","type"=>"hidden","value"=>CRUDBooster::me()->owner_id];
            $columns[] = ['label'=>'Contato','name'=>'contact','type'=>'text','validation'=>'required|min:3|max:70','required'=>true];
            $columns[] = ['label'=>'Tipo do Contato','name'=>'contact_type_id','type'=>'select','datatable'=>'contact_types,type','required'=>true];
            $this->form[] = ['label'=>'Contatos','name'=>'contacts_child','type'=>'child',
                'columns'=>$columns,'table'=>'contacts','foreign_key'=>'partner_id'];

            $columns = [];
            $columns[] = ["showInDetail"=>false,"name"=>"owner_id","type"=>"hidden","value"=>CRUDBooster::me()->owner_id];
            $columns[] = ['label'=>'Logradouro','name'=>'street','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10','required'=>true];
            $columns[] = ['label'=>'CEP','name'=>'postal_code','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
            $columns[] = ['label'=>'Bairro','name'=>'district','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
            $columns[] = ['label'=>'Cidade','name'=>'city','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
            $columns[] = ['label'=>'País','name'=>'country','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
            $columns[] = ['label'=>'Complemento','name'=>'note','type'=>'text','validation'=>'min:1|max:255','width'=>'col-sm-10'];
//            $columns[] = ['label'=>'Endereço Principal','name'=>'default','type'=>'radio','width'=>'col-sm-10','dataenum'=>'1|Sim;0|Não','value'=>'1','validation'=>''];
            $this->form[] = ['label'=>'Endereços','name'=>'addresses_child','type'=>'child',
                'columns'=>$columns,'table'=>'addresses','foreign_key'=>'partner_id'];


            $columns = [];
            $columns[] = ["showInDetail"=>false,"name"=>"owner_id","type"=>"hidden","value"=>CRUDBooster::me()->owner_id];
            $columns[] = ['label'=>'Agência','name'=>'agency_number','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10','required'=>true];
            $columns[] = ['label'=>'Conta','name'=>'account_number','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10','required'=>true];
            $columns[] = ['label'=>'Tipo de Conta','name'=>'account_type_id','type'=>'select','validation'=>'required|min:1|max:255','width'=>'col-sm-10','datatable'=>'account_types,type','required'=>true];
            $columns[] = ['label'=>'Banco','name'=>'bank_id','type'=>'select','validation'=>'required|min:1|max:255','width'=>'col-sm-10','datatable'=>'banks,bank_name',"datatable_format"=>"bank_code,' - ',bank_name",'required'=>true];
            $this->form[] = ['label'=>'Dados Bancários','name'=>'bank_data_child','type'=>'child',
                'columns'=>$columns,'table'=>'bank_data','foreign_key'=>'partner_id'];

            # END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ['label'=>'Nome','name'=>'name','type'=>'text','validation'=>'required|string|min:3|max:70','width'=>'col-sm-10','placeholder'=>'Você pode digitar somente letras'];
			//$this->form[] = ['label'=>'Tipo de Cadastro','name'=>'partner_type_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'partner_types,type'];
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
//            $this->sub_module[] = ['label'=>'Datas','path'=>'dates','foreign_key'=>'partner_id','parent_columns'=>'name','button_color'=>'primary','button_icon'=>'fa fa-bars'];
            $this->sub_module[] = ['label'=>'Documentos','path'=>'documents','foreign_key'=>'partner_id','parent_columns'=>'name','button_color'=>'primary','button_icon'=>'fa fa-bars'];
            $this->sub_module[] = ['label'=>'Contatos','path'=>'contacts','foreign_key'=>'partner_id','parent_columns'=>'name','button_color'=>'primary','button_icon'=>'fa fa-bars'];
            $this->sub_module[] = ['label'=>'Endereços','path'=>'addresses','foreign_key'=>'partner_id','parent_columns'=>'name','button_color'=>'primary','button_icon'=>'fa fa-bars'];
            $this->sub_module[] = ['label'=>'Dados Bancários','path'=>'bank_data','foreign_key'=>'partner_id','parent_columns'=>'name','button_color'=>'primary','button_icon'=>'fa fa-bars'];

	        //$this->sub_module = array();


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
	        $this->index_button = array();



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
	        |
	        */
	        $this->script_js = NULL;


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
	        $this->post_index_html = null;
	        
	        
	        
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


	}