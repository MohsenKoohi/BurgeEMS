<?php
class Class_post_manager_model extends CI_Model
{
	private $class_post_table_name="class_post";
	private $class_post_text_table_name="class_post_text";
	private $class_post_comment_table_name="class_post_comment";
	
	private $class_post_writable_props=array(
		"cp_start_date","cp_end_date","cp_class_id","cp_active","cp_allow_comment","cp_allow_file"
	);
	private $class_post_text_writable_props=array(
		"cpt_title","cpt_content","cpt_gallery"
	);

	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$tbl=$this->db->dbprefix($this->class_post_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`cp_id` INT  NOT NULL AUTO_INCREMENT
				,`cp_academic_time_id` INT NOT NULL
				,`cp_start_date` CHAR(20)
				,`cp_end_date` CHAR(20)
				,`cp_teacher_id` INT NOT NULL DEFAULT 0
				,`cp_class_id` INT NOT NULL
				,`cp_active` BIT(1) NOT NULL DEFAULT 0
				,`cp_assignment` BIT(1) NOT NULL DEFAULT 0
				,`cp_allow_comment` BIT(1) NOT NULL DEFAULT 0
				,`cp_allow_file` BIT(1) NOT NULL DEFAULT 0
				,`cp_comment_count` INT NOT NULL DEFAULT 0
				,PRIMARY KEY (cp_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl=$this->db->dbprefix($this->class_post_text_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`cpt_cp_id` INT  NOT NULL
				,`cpt_lang_id` CHAR(2) NOT NULL
				,`cpt_title` TEXT
				,`cpt_content` MEDIUMTEXT
				,`cpt_gallery` TEXT
				,PRIMARY KEY (cpt_cp_id, cpt_lang_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl=$this->db->dbprefix($this->class_post_comment_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`cpc_id` INT  NOT NULL AUTO_INCREMENT
				,`cpc_cp_id` INT  NOT NULL
				,`cpc_customer_id` INT NOT NULL
				,`cpc_comment` TEXT
				,`cpc_active` BIT(1) NOT NULL DEFAULT 1
				,`cpc_file` CHAR(10) DEFAULT NULL
				,`cpc_date` CHAR(20)
				,PRIMARY KEY (cpc_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("class_post","class_post_manager");
		$this->module_manager_model->add_module_names_from_lang_file("class_post");
		
		return;
	}

	public function uninstall()
	{
		return;
	}
	
	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
	
		$data['classes']=$this->get_statistics();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("class_post_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	private function get_statistics()
	{
		return $this->db
			->select("count(*) as count ,class_name")
			->from($this->class_post_table_name)
			->join("class","class_id = cp_class_id","LEFT")
			->where("!ISNULL(cp_class_id) AND cp_class_id")
			->group_by("cp_class_id")
			->order_by("class_order")
			->get()
			->result_array();
	}

	public function add_class_post($ins)
	{
		$this->load->model("time_manager_model");
		$current_academic_time=$this->time_manager_model->get_current_academic_time();

		$props=array(
			"cp_academic_time_id"		=> $current_academic_time['time_id']
			,"cp_start_date"				=> get_current_time()
			,'cp_assignment'				=> $ins['assignment']
			,'cp_teacher_id'				=> $ins['teacher_id']
		);

		$this->db->insert($this->class_post_table_name,$props);
		
		$new_class_post_id=$this->db->insert_id();
		$props['class_post_id']=$new_class_post_id;

		$this->log_manager_model->info("CLASS_POST_ADD",$props);	
		$this->customer_manager_model->add_customer_log($ins['teacher_id'],'CLASS_POST_ADD',$props);

		$post_texts=array();
		foreach($this->language->get_languages() as $index=>$lang)
			$post_texts[]=array(
				"cpt_cp_id"=>$new_class_post_id
				,"cpt_lang_id"=>$index
			);
		$this->db->insert_batch($this->class_post_text_table_name,$post_texts);

		//creating directories
		@mkdir(get_class_post_directory_path($new_class_post_id),0777);
		@mkdir(get_class_post_gallery_image_path($new_class_post_id,''),0777);
		@mkdir(get_class_post_content_path($new_class_post_id),0777);
		@mkdir(get_class_post_comment_path($new_class_post_id),0777);
		
		
		return $new_class_post_id;
	}

	public function add_comment($cp_id,$customer_id,$comment,$file)
	{
		$ins=array(
			"cpc_cp_id"				=> $cp_id
			,"cpc_customer_id"	=> $customer_id
			,"cpc_comment"			=> $comment
			,"cpc_file"				=> $file
			,"cpc_date"				=> get_current_time()
		);

		$this->db->insert($this->class_post_comment_table_name,$ins);

		$comment_id=$this->db->insert_id();
		$ins['comment_id']=$comment_id;

		$this->log_manager_model->info("CLASS_POST_COMMENT",$ins);	
		$this->customer_manager_model->add_customer_log($customer_id,'CLASS_POST_COMMENT',$ins);

		return $comment_id;
	}

	public function get_comments($cp_id,$filter)
	{
		$this->db
			->select($this->class_post_comment_table_name.".*")
			->select("customer_name")
			->from($this->class_post_comment_table_name)
			->join("customer","cpc_customer_id = customer_id","LEFT")
			->where("cpc_cp_id",$cp_id);

		if(isset($filter['customer_id']))
			$this->db->where("cpc_customer_id",$filter['customer_id']);

		if(isset($filter['active']))
			$this->db->where("cpc_active",1);

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);
		else
			$this->db->order_by("cpc_id ASC");

		return $this->db			
			->get()
			->result_array();	
	}

	public function verify_comments($cp_id,$acts,$inacts,$teacher_id)
	{
		if($acts)
			$this->db
				->set("cpc_active",1)
				->where("cpc_id IN ($acts)")
				->where('cpc_cp_id',$cp_id)
				->update($this->class_post_comment_table_name);

		if($inacts)
			$this->db
				->set("cpc_active",0)
				->where("cpc_id IN ($inacts)")
				->where('cpc_cp_id',$cp_id)
				->update($this->class_post_comment_table_name);

		$props=array(
			'cp_id' => $cp_id
			,'actives'=>$acts
			,'inactives'=>$inacts
			,'teacher_id'=>$teacher_id
		);

		$this->customer_manager_model->add_customer_log($teacher_id,'CLASS_POST_VERIFY',$props);		
		$this->log_manager_model->info("CLASS_POST_VERIFY",$props);	

		return;
	}

	public function get_class_posts($filter)
	{
		$this->db
			->select($this->class_post_table_name.".* ")
			->select($this->class_post_text_table_name.".* ")
			->select("time.time_name as academic_time")
			->select("customer_name as teacher_name, customer_subject as teacher_subject")
			->select("class_name")
			->from($this->class_post_table_name)
			->join($this->class_post_text_table_name,"cpt_cp_id = cp_id","LEFT")
			->join("customer","cp_teacher_id = customer_id","LEFT")
			->join("time","cp_academic_time_id = time_id","LEFT")
			->join("class","cp_class_id = class_id","LEFT");
		
		$this->set_class_post_query_filter($filter);

		$results=$this->db->get();

		$rows=$results->result_array();
		foreach($rows as &$row)
			$row['cpt_gallery']=json_decode($row['cpt_gallery'],TRUE);
		
		return $rows;
	}

	public function get_class_posts_total($filter)
	{
		$this->db
			->select("COUNT( cp_id ) as count")
			->from($this->class_post_table_name);
			
		unset($filter['lang']);
		$this->set_class_post_query_filter($filter);
	
		$row=$this->db->get()->row_array();

		return $row['count'];
	}

	private function set_class_post_query_filter($filter)
	{
		if(isset($filter['lang']))
			$this->db->where("cpt_lang_id",$filter['lang']);

		if(isset($filter['teacher_id']))
			$this->db->where("cp_teacher_id",(int)$filter['teacher_id']);

		if(isset($filter['teacher_id_in']))
			$this->db->where_in("cp_teacher_id",$filter['teacher_id_in']);

		if(isset($filter['class_id']))
			$this->db->where("cp_class_id",(int)$filter['class_id']);

		if(isset($filter['assignment']))
			$this->db->where("cp_assignment",(int)$filter['assignment']);

		if(isset($filter['active']))
			$this->db->where("cp_active",(int)$filter['active']);

		if(isset($filter['start_date']))
			$this->db->where("cp_start_date <=",$filter['start_date']);

		if(isset($filter['academic_time']))
			$this->db->where("cp_academic_time_id",(int)$filter['academic_time']);

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);
		else
			$this->db->order_by("cp_id DESC");	

		if(isset($filter['start']))
			$this->db->limit($filter['count'],$filter['start']);

		if(isset($filter['title']))
		{
			$title=trim($filter['title']);
			$title="%".str_replace(" ","%",$title)."%";
			$this->db->where("( `cpt_title` LIKE '$title')");
		}

	
		return;
	}

	public function get_class_post($class_post_id,$filter=array())
	{
		$this->db
			->select($this->class_post_table_name.".* ")
			->select($this->class_post_text_table_name.".* ")
			->select("time.time_name as academic_time")
			->select("customer_name as teacher_name, customer_subject as teacher_subject")
			->select("class_name")
			->from($this->class_post_table_name)
			->join($this->class_post_text_table_name,"cpt_cp_id = cp_id","left")
			->join("customer","cp_teacher_id = customer_id","left")
			->join("time","cp_academic_time_id = time_id","left")
			->join("class","cp_class_id = class_id","left")
			->where("cp_id",$class_post_id);

		$this->set_class_post_query_filter($filter);

		$results=$this->db
			->get()
			->result_array();

		$this->set_galleries($results);

		return $results;
	}

	private function set_galleries(&$posts)
	{
		foreach($posts as &$post)
		{
			$gallery=array(
				'last_index'	=> 0
				,'images'		=> array()
			);

			if($post['cpt_gallery'])
				$gallery=json_decode($post['cpt_gallery'],TRUE);

			$post['cpt_gallery']=$gallery;
		}

		return;
	}

	public function set_class_post_props($cp_id, $props, $text_props,$teacher_id)
	{
		$props=select_allowed_elements($props,$this->class_post_writable_props);

		if($props)
		{
			foreach ($props as $prop => $value)
				$this->db->set($prop,$value);

			$this->db
				->where("cp_id",$cp_id)
				->update($this->class_post_table_name);
		}

		foreach($text_props as $text)
		{
			$lang=$text['cpt_lang_id'];

			$text['cpt_gallery']=json_encode($text['cpt_gallery']);
			$text=select_allowed_elements($text,$this->class_post_text_writable_props);
			if(!$text)
				continue;

			foreach($text as $prop => $value)
			{
				$this->db->set($prop,$value);
				$props[$lang."_".$prop]=$value;
			}

			$this->db
				->where("cpt_cp_id",$cp_id)
				->where("cpt_lang_id",$lang)
				->update($this->class_post_text_table_name);
		}

		$this->customer_manager_model->add_customer_log($teacher_id,'CLASS_POST_CHANGE',$props);		
		$props['teacher_id']=$teacher_id;
		$this->log_manager_model->info("CLASS_POST_CHANGE",$props);	

		return;
	}

	public function delete_class_post($cp_id,$teacher_id)
	{
		$props=array("class_post_id"=>$cp_id);

		$this->db
			->where("cp_id",$cp_id)
			->delete($this->class_post_table_name);

		$this->db
			->where("cpt_cp_id",$cp_id)
			->delete($this->class_post_text_table_name);

		$this->db
			->where("cpc_cp_id",$cp_id)
			->delete($this->class_post_comment_table_name);
		
		$this->customer_manager_model->add_customer_log($teacher_id,'CLASS_POST_DELETE',$props);

		$props['teacher_id']=$teacher_id;
		$this->log_manager_model->info("CLASS_POST_DELETE",$props);	

		return;

	}

	public function check_file_manager_access($module_part_id)
	{
		$this->load->model("customer_manager_model");
		$customer_info=$this->customer_manager_model->get_logged_customer_info();
		if(!$customer_info)
			return FALSE;

		if( "teacher" !== $customer_info['customer_type'] ) 
			return FALSE;
		
		$res=$this->db
			->select("*")
			->from($this->class_post_table_name)
			->where("cp_id",(int)$module_part_id)
			->where("cp_teacher_id",$customer_info['customer_id'])
			->get()
			->row_array();
		
		if(!$res)
			return FALSE;

		return TRUE;
	}

	public function get_file_manager_root_url($class_post_id)
	{
		return get_class_post_content_url($class_post_id);
	}
}
