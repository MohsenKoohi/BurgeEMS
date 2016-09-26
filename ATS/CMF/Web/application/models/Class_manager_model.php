<?php
class Class_manager_model extends CI_Model
{
	private $class_table_name="class";
	private $class_teacher_table_name="class_teacher";
	private $class_curriculum_table_name="class_curriculum";
	private $all_classes=NULL;

	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$class_table=$this->db->dbprefix($this->class_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $class_table (
				`class_id` INT  NOT NULL AUTO_INCREMENT
				,`class_name` VARCHAR(511)
				,`class_order` INT DEFAULT 1
				,PRIMARY KEY (class_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$class_teacher_table=$this->db->dbprefix($this->class_teacher_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $class_teacher_table (
				`ct_class_id` INT  
				,`ct_teacher_id` INT
				,PRIMARY KEY (ct_class_id, ct_teacher_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$class_curriculum_table=$this->db->dbprefix($this->class_curriculum_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $class_curriculum_table (
				`cc_class_id` INT  
				,`cc_day_id` INT
				,`cc_hour_id` INT
				,`cc_course` VARCHAR(128)
				,PRIMARY KEY (cc_class_id, cc_day_id, cc_hour_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("class","class_manager");
		$this->module_manager_model->add_module_names_from_lang_file("class");

		$this->load->model("constant_manager_model");
		$this->constant_manager_model->set("allow_delete_classes",0);
				
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

		$CI->lang->load('ae_class',$lang);
			
		$data['classes']=$this->get_all_classes();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("class_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_class($class_id)
	{
		$result=$this->db->get_where($this->class_table_name,array("class_id"=>(int)$class_id));
				
		return $result->row_array();
	}

	public function get_teachers($class_id)
	{
		$ret=array();

		$result=$this->db
			->select("customer_id,customer_name,customer_subject,ct_teacher_id")
			->from("customer")
			->join($this->class_teacher_table_name,"customer_id = ct_teacher_id AND ct_class_id = ".(int)$class_id,"LEFT")			
			->where("customer_type","teacher")
			->where("customer_active",1)
			->order_by("customer_order ASC")
			->get();
		
		return $result->result_array();
	}

	public function set_class_teachers($class_id,$tids)
	{
		$this->db
			->where("ct_class_id",$class_id)
			->delete($this->class_teacher_table_name);

		if($tids)
		{
			$ins=array();
			foreach(explode(",",$tids) as $tid)
				$ins[]=array("ct_class_id"=>$class_id,"ct_teacher_id"=>$tid);

			$this->db->insert_batch($this->class_teacher_table_name,$ins);
		}

		$this->log_manager_model->info("CLASS_TEACHERS_SET",array("teachers_ids"=>$tids));	

		return;
	}

	public function get_students($class_id)
	{
		$ret=array();

		$result=$this->db
			->select("customer_id,customer_name,customer_image_hash")
			->from("customer")
			->where("customer_type","student")
			->where("customer_class_id",(int)$class_id)
			->where("customer_active",1)
			->order_by("customer_order ASC")
			->get();
		
		return $result->result_array();
	}

	public function get_all_classes()
	{
		if($this->all_classes)
			return $this->all_classes;

		$result=$this->db
			->from($this->class_table_name)
			->select("*")
			->order_by("class_order ASC")
			->get();

		$this->all_classes=$result->result_array();
		
		return $this->all_classes;
	}

	public function add($name)
	{
		$this->db->insert($this->class_table_name,array("class_name"=>$name));
		$new_class_id=$this->db->insert_id();
		
		$this->log_manager_model->info("CLASS_ADD",array("name"=>$name,"id"=>$new_class_id));	

		return;
	}

	public function resort_classes($ids)
	{
		$update_array=array();
		$i=1;
		foreach(explode(",",$ids) as $id)
			$update_array[]=array(
				"class_id"		=> $id
				,"class_order"	=> $i++
			);

		$this->db->update_batch($this->class_table_name,$update_array, "class_id");
		
		$this->log_manager_model->info("CLASS_RESORT",array("class_ids"=>$ids));	

		return;
	}

	public function resort_students($ids)
	{
		$update_array=array();
		$i=1;
		foreach(explode(",",$ids) as $id)
			$update_array[]=array(
				"customer_id"		=> (int)$id
				,"customer_order"	=> $i++
			);

		$this->db->update_batch("customer",$update_array, "customer_id");
		
		$this->log_manager_model->info("CLASS_STUDENT_RESORT",array("class_ids"=>$ids));	

		return;
	}

	public function delete_class($class_id)
	{
		$this->load->model("constant_manager_model");
		if(!$this->constant_manager_model->get("allow_delete_classes"))
			return FALSE;

		$this->db
			->where("class_id", $class_id)
			->delete($this->class_table_name);


		$this->db
			->set("customer_active",0)
			->where("customer_class_id", $class_id)
			->update("customer");

		$this->db
			->where("ct_class_id", $class_id)
			->delete($this->class_teacher_table_name);

		$this->db
			->from($this->class_curriculum_table_name)
			->where("cc_class_id",$class_id)
			->delete();

		$this->log_manager_model->info("CLASS_DELETE",array("class_id"=>$class_id));	

		$this->constant_manager_model->set("allow_delete_classes",0);

		return TRUE;
	}

	public function start_new_time($prev_time,$new_time)
	{
		$this->db
			->where("1")
			->delete($this->class_teacher_table_name);

		$log=array(
			"new_time_id"=>$new_time['time_id']
			,"new_time_name"=>$new_time['time_name']
			,"prev_time_id"=>$prev_time['time_id']
			,"prev_time_name"=>$prev_time['time_name']
		);

		$this->log_manager_model->info("CLASS_TEACHER_RESET",$log);	

		return;
	}

	public function set_class_props($new_props)
	{
		$this->db->update_batch($this->class_table_name,$new_props,"class_id");

		$log=array();
		foreach($new_props as $np)
			$log["new_name_".$np['class_id']]=$np['class_name'];

		$this->log_manager_model->info("CLASS_RENAME",$log);	

		return;
	}

	public function get_curriculum_hours()
	{
		return $this->db
			->from($this->class_curriculum_table_name)
			->where("cc_class_id",0)
			->where("cc_day_id",0)
			->order_by("cc_hour_id")
			->get()
			->result_array();
	}

	public function get_class_curriculum($class_id)
	{
		$chours=sizeof($this->get_curriculum_hours());

		$ret=array();
		for($day=0;$day<7;$day++)
		{
			$ret[$day]=array();
			for($hour=0;$hour<$chours;$hour++)
				$ret[$day][$hour]="";
		}

		$result=$this->db
			->from($this->class_curriculum_table_name)
			->where("cc_class_id",$class_id)
			->order_by("cc_day_id ASC, cc_hour_id ASC")
			->get()
			->result_array();

		foreach($result as $res)
			$ret[$res['cc_day_id']][$res['cc_hour_id']]=$res['cc_course'];

		return $ret;
	}

	public function set_class_curriculum($class_id,$courses)
	{
		$this->db
			->from($this->class_curriculum_table_name)
			->where("cc_class_id",$class_id)
			->delete();

		$insert_array=array();
		$log=array();
		foreach($courses as $day_index=>$day)
		{
			foreach($day as $hour_index=>$course)
				$insert_array[]=array(
					"cc_class_id"=>$class_id
					,"cc_day_id"=>$day_index
					,"cc_hour_id"=>$hour_index
					,"cc_course"=>$course
				);

			$log["day_".$day_index]=implode(",", $day);
		}

		$this->db->insert_batch($this->class_curriculum_table_name,$insert_array);

		$this->log_manager_model->info("CLASS_CURRICULUM_SET",$log);

		return;
	}

	public function set_curriculum_hours($hours)
	{
		$this->db
			->from($this->class_curriculum_table_name)
			->where("cc_class_id",0)
			->where("cc_day_id",0)
			->delete();

		if($hours)
		{

			$insert=array();
			$i=0;
			foreach($hours as $hour)
				$insert[]=array(
					"cc_class_id"=>0
					,"cc_day_id"=>0
					,"cc_hour_id"=>$i++
					,"cc_course"=>$hour
			);

			$this->db->insert_batch($this->class_curriculum_table_name , $insert);
		}

		$this->log_manager_model->info("CLASS_CURRICULUM_HOURS_SET",array("hours"=>implode(",", $hours)));

		return;
	}

}