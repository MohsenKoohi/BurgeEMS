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

		$CI->lang->load('ae_class_post',$lang);
			
		$data=$this->get_statistics();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("post_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	private function get_statistics()
	{
		return array();
		$tb=$this->db->dbprefix($this->cpost_table_name);

		return $this->db->query("
			SELECT 
				(SELECT COUNT(*) FROM $tb) as total, 
				(SELECT COUNT(*) FROM $tb WHERE post_active) as active
			")->row_array();
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

		@mkdir(get_class_post_directory_path($new_class_post_id),0777);
		@mkdir(get_class_post_gallery_image_path($new_class_post_id,''),0777);

		return $new_class_post_id;
	}

	public function get_class_posts($filter)
	{
		return;
		$this->db->from($this->post_table_name);
		$this->db->join($this->post_content_table_name,"post_id = pc_post_id","left");
		$this->db->join($this->post_category_table_name,"post_id = pcat_post_id","left");
		
		$this->set_post_query_filter($filter);

		$results=$this->db->get();

		$rows=$results->result_array();
		foreach($rows as &$row)
			$row['pc_gallery']=json_decode($row['pc_gallery'],TRUE);
		
		return $rows;
	}

	public function get_class_posts_total($filter)
	{
		return 0;

		$this->db->select("COUNT( DISTINCT post_id ) as count");
		$this->db->from($this->post_table_name);
		$this->db->join($this->post_content_table_name,"post_id = pc_post_id","left");
		$this->db->join($this->post_category_table_name,"post_id = pcat_post_id","left");
		
		$this->set_post_query_filter($filter);
		
		$row=$this->db->get()->row_array();

		return $row['count'];
	}

	private function set_post_query_filter($filter)
	{
		if(isset($filter['lang']))
			$this->db->where("cpt_lang_id",$filter['lang']);

		if(isset($filter['teacher_id']))
			$this->db->where("cp_teacher_id",(int)$filter['teacher_id']);

		if(isset($filter['class_id']))
			$this->db->where("cp_class_id",(int)$filter['class_id']);

		if(isset($filter['assignment']))
			$this->db->where("cp_assignment",(int)$filter['assignment']);

		if(isset($filter['active']))
			$this->db->where("cp_active",(int)$filter['active']);

		return;

		if(isset($filter['category_id']))
			$this->db->where("pcat_category_id",$filter['category_id']);

		if(isset($filter['title']))
		{
			$title=trim($filter['title']);
			$title="%".str_replace(" ","%",$title)."%";
			$this->db->where("( `pc_title` LIKE '$title')");
		}

		if(isset($filter['active']))
			$this->db->where(array(
				"post_active"=>$filter['active']
				,"pc_active"=>$filter['active']
			));

		if(isset($filter['post_date_le']))
			$this->db->where("post_date <=",$filter['post_date_le']);

		if(isset($filter['post_date_ge']))
			$this->db->where("post_date >=",$filter['post_date_ge']);

		if(isset($filter['order_by']))
		{
			if($filter['order_by']==="random")
				$this->db->order_by("post_id","random");
			else
				$this->db->order_by($filter['order_by']);
		}
		else
			$this->db->order_by("post_id DESC");	

		if(isset($filter['start']))
			$this->db->limit($filter['count'],$filter['start']);

		if(isset($filter['group_by']))
			$this->db->group_by($filter['group_by']);
	
		return;
	}

	public function get_class_post($class_post_id,$filter=array())
	{
		$this->db
			->select($this->class_post_table_name.".* ")
			->select($this->class_post_text_table_name.".* ")
			->select("time.time_name as academic_time")
			->select("customer_name as teacher_name, customer_subject as teacher_subject")
			->from($this->class_post_table_name)
			->join($this->class_post_text_table_name,"cpt_cp_id = cp_id","left")
			->join("customer","cp_teacher_id = customer_id","left")
			->join("time","cp_academic_time_id = time_id","left")
			->where("cp_id",$class_post_id);

		$this->set_post_query_filter($filter);

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
		$this->log_manager_model->info("CLASS_POST_CHANGE",$props);	

		return;
	}

	public function delete_post($post_id)
	{
		$props=array("post_id"=>$post_id);

		$this->db
			->where("post_id",$post_id)
			->delete($this->post_table_name);

		$this->db
			->where("pc_post_id",$post_id)
			->delete($this->post_content_table_name);

		$this->db
			->where("pcat_post_id",$post_id)
			->delete($this->post_category_table_name);
		
		$this->log_manager_model->info("POST_DELETE",$props);	

		return;

	}
}
