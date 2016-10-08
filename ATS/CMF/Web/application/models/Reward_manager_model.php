<?php
class Reward_manager_model extends CI_Model
{
	private $reward_table_name="reward";
	private $reward_value_table_name="reward_value";
	
	public function __construct()
	{
		parent::__construct();
	
		return;
	}

	public function install()
	{
		$table=$this->db->dbprefix($this->reward_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`reward_id` INT AUTO_INCREMENT NOT NULL
				,`reward_class_id` INT NOT NULL
				,`reward_teacher_id` INT NOT NULL
				,`reward_date` CHAR(19) 
				,`reward_subject` VARCHAR(255) 
				,`reward_is_prize` BIT(1) DEFAULT 0
				,PRIMARY KEY (reward_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$table=$this->db->dbprefix($this->reward_value_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`rv_reward_id` INT NOT NULL
				,`rv_student_id` INT NOT NULL
				,`rv_value` INT
				,`rv_description` VARCHAR(255) 
				,PRIMARY KEY (rv_reward_id,rv_student_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
		
		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("reward","reward_manager");
		$this->module_manager_model->add_module_names_from_lang_file("reward");		

		return;
	}

	public function uninstall()
	{
		
		return;
	}

	public function get_student_total_rewards($student_id)
	{
		return $this->db
			->select("SUM(rv_value) as sum")
			->from($this->reward_value_table_name)
			->where("rv_student_id",$student_id)
			->group_by("rv_student_id")
			->get()
			->row_array()['sum'];
	}

	public function get_student_rewards($student_id)
	{
		return $this->db
			->select("rv_description,rv_value,reward_id,reward_subject,reward_date,reward_is_prize")
			->from($this->reward_value_table_name)
			->join($this->reward_table_name,"rv_reward_id = reward_id ","LEFT")
			->where("rv_student_id",$student_id)
			->order_by("reward_date ASC")
			->get()
			->result_array();
	}

	public function add_rewards($teacher_id,$class_id,$subject,$rewards,$is_prize)
	{
		$date=get_current_time();
		$log=array();

		$this->db->insert($this->reward_table_name,array(
			"reward_teacher_id"=>$teacher_id
			,"reward_class_id"=>$class_id
			,"reward_date"=>$date
			,"reward_subject"=>$subject
			,"reward_is_prize"=>$is_prize
			));

		$reward_id=$this->db->insert_id();

		$log['reward_subject']=$subject;
		$log['reward_id']=$reward_id;
		$log['reward_date']=$date;
		$log['reward_is_prize']=$is_prize;

		$ins=array();
		foreach($rewards as $reward)
		{
			$ins[]=array(
				"rv_reward_id"=>$reward_id
				,"rv_student_id"=>$reward['student_id']
				,"rv_value"=>intval($reward['value'])
				,"rv_description"=>$reward['description']
			);

			$log['student_'.$reward['student_id'].'_reward']=$reward['value'];
			$log['student_'.$reward['student_id'].'_description']=$reward['description'];
		}

		if(!$ins)
			return;

		$this->db->insert_batch($this->reward_value_table_name,$ins);

		$this->log_manager_model->info("REWARD_ADD",$log);	

		$this->load->model("customer_manager_model");
		$log['teacher_id']=$teacher_id;
		$this->customer_manager_model->add_customer_log($teacher_id,'REWARD_ADD',$log);	

		return $reward_id;
	}

	public function get_prize_teachers()
	{
		$this->load->model("class_manager_model");
		
		return $this->class_manager_model->get_teachers(-1);
	}

	public function set_prize_teachers($tids)
	{
		$this->load->model("class_manager_model");
		
		$this->class_manager_model->set_class_teachers(-1,$tids);

		$this->log_manager_model->info("REWARD_SET_PRIZE_ACCESS",array("class_id"=>-1,"teacher_ids"=>$tids));	

	}

	public function is_prize_teacher($tid)
	{
		$teachers=$this->get_prize_teachers();
		foreach($teachers as $teacher)
			if($tid == $teacher['ct_teacher_id'])
				return TRUE;

		return FALSE;
	}

	public function get_class_students_with_total_rewards($class_id)
	{
		$sub_query=$this->db
			->select("SUM(rv_value)")
			->from($this->reward_value_table_name)
			->where("rv_student_id = customer_id")
			->group_by("rv_student_id")
			->get_compiled_select();

		return $this->db
			->select("customer_id,customer_name,customer_image_hash,($sub_query) as total_rewards")
			->from("customer")
			->where("customer_class_id",$class_id)
			->where("customer_active",1)
			->order_by("customer_order ASC")
			->get()
			->result_array();
	}

	public function get_all_rewards()
	{
		return $this->db
			->select("r.*,class_name,customer_name as teacher_name")
			->from($this->reward_table_name." r")
			->join("class","reward_class_id = class_id","LEFT")
			->join("customer","reward_teacher_id = customer_id","LEFT")
			->order_by("reward_date ASC")
			->get()
			->result_array();
	}

	public function get_rewards_list($teacher_id,$class_id)
	{
		return $this->db
			->from($this->reward_table_name)
			->where("reward_teacher_id",$teacher_id)
			->where("reward_class_id",$class_id)
			->order_by("reward_date ASC")
			->get()
			->result_array();
	}

	public function get_reward_info($reward_id)
	{
		return $this->db
			->from($this->reward_table_name)
			->where("reward_id",$reward_id)
			->get()
			->row_array();
	}

	public function get_reward_values($reward_id)
	{
		return $this->db
			->select("v.* , customer_name")
			->from($this->reward_value_table_name." v")
			->join("customer ","rv_student_id = customer_id AND customer_type = 'student'","LEFT")
			->where("rv_reward_id",$reward_id)
			->order_by("customer_order ASC")
			->get()
			->result_array();
	}

}