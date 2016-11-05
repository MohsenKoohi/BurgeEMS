<?php
class Class_post_manager_model extends CI_Model
{
	private $class_post_table_name="class_post";
	private $class_post_text_table_name="class_post_text";
	private $class_post_comment_table_name="class_post_comment";
	
	private $class_post_writable_props=array(
		"cp_date_start","cp_date_end","cp_class_id","cp_active","cp_allow_comment"
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
				,`cp_date_start` CHAR(20)
				,`cp_date_end` CHAR(20)
				,`cp_teacher_id` INT NOT NULL DEFAULT 0
				,`cp_class_id` INT NOT NULL
				,`cp_active` BIT(1) NOT NULL DEFAULT 0
				,`cp_assignment` BIT(1) NOT NULL DEFAULT 0
				,`cp_allow_comment` BIT(1) NOT NULL DEFAULT 0
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
		$props=array(
			"cp_date_start"		=> get_current_time()
			,'cp_assignment'		=> $ins['assignment']
			,'cp_teacher_id'		=> $ins['teacher_id']
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

		return $new_class_post_id;
	}

	public function get_customer_class_posts($filter)
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

	public function get_customer_class_total($filter)
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
			$this->db->where("pc_lang_id",$filter['lang']);

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

	public function get_post($post_id,$filter=array())
	{
		$cat_query=$this->db
			->select("GROUP_CONCAT(pcat_category_id)")
			->from($this->post_category_table_name)
			->where("pcat_post_id",$post_id)
			->get_compiled_select();

		$this->db
			->select("post.* , post_content.* , user_id, user_name")
			->select("(".$cat_query.") as categories")
			->from("post")
			->join("user","post_creator_uid = user_id","left")
			->join("post_content","post_id = pc_post_id","left")
			->where("post_id",$post_id);

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

			if($post['pc_gallery'])
				$gallery=json_decode($post['pc_gallery'],TRUE);

			$post['pc_gallery']=$gallery;
		}

		return;
	}

	public function set_post_props($post_id, $props, $post_contents)
	{	
		$this->db
			->where("pcat_post_id",$post_id)
			->delete($this->post_category_table_name);
		
		$props_categories=$props['categories'];
		
		if($props_categories!=NULL)
		{
			$categories=explode(",",$props_categories);
			$ins=array();
			foreach($categories as $category_id)
				$ins[]=array("pcat_post_id"=>$post_id,"pcat_category_id"=>(int)$category_id);

			if($ins)
				$this->db->insert_batch($this->post_category_table_name,$ins);
		}

		unset($props['categories']);

		$props=select_allowed_elements($props,$this->post_writable_props);

		if($props)
		{
			foreach ($props as $prop => $value)
				$this->db->set($prop,$value);

			$this->db
				->where("post_id",$post_id)
				->update($this->post_table_name);
		}

		$props['categories']=$props_categories;

		foreach($post_contents as $content)
		{
			$lang=$content['pc_lang_id'];

			$content['pc_gallery']=json_encode($content['pc_gallery']);
			$content=select_allowed_elements($content,$this->post_content_writable_props);
			if(!$content)
				continue;

			foreach($content as $prop => $value)
			{
				$this->db->set($prop,$value);
				$props[$lang."_".$prop]=$value;
			}

			$this->db
				->where("pc_post_id",$post_id)
				->where("pc_lang_id",$lang)
				->update($this->post_content_table_name);
		}
		
		$this->log_manager_model->info("POST_CHANGE",$props);	

		return;
	}

	public function change_category($old_category_id,$new_category_id)
	{
		$rows=$this->db
			->where("pcat_category_id",$old_category_id)
			->or_where("pcat_category_id",$new_category_id)
			->group_by("pcat_post_id")
			->get($this->post_category_table_name)
			->result_array();

		$post_ids=array();
		foreach($rows as $row)
			$post_ids[]=$row['pcat_post_id'];

		if(!$post_ids)
			return;

		$this->db
			->where("pcat_category_id",$old_category_id)
			->or_where("pcat_category_id",$new_category_id)
			->delete($this->post_category_table_name);

		$ins=array();
		foreach($post_ids as $post_id)
			$ins[]=array("pcat_category_id"=>$new_category_id,"pcat_post_id"=>$post_id);

		$this->db->insert_batch($this->post_category_table_name,$ins);

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
