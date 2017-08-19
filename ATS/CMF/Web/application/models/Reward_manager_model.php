<?php
class Reward_manager_model extends CI_Model
{
	private $reward_table_name="reward";
	private $reward_value_table_name="reward_value";
	private $reward_edit_time=84400; //24*60*60 one day

	private $current_academic_time_id;

	public function __construct()
	{
		parent::__construct();

		$this->load->model("time_manager_model");
		$atime=$this->time_manager_model->get_current_academic_time();
		$this->current_academic_time_id=$atime['time_id'];

		return;
	}

	public function install()
	{
		$table=$this->db->dbprefix($this->reward_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`reward_id` INT AUTO_INCREMENT NOT NULL
				,`reward_time_id` INT NOT NULL
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

	public function get_dashboard_info()
	{
		$CI=& get_instance();

		$rewards_counts=$this->db
			->select("class_name, COUNT(*) as reward_count")
			->from($this->reward_table_name)
			->join("class","reward_class_id = class_id","LEFT")
			->where("reward_is_prize",0)
			->where("reward_time_id", $this->current_academic_time_id)
			->group_by("reward_class_id")
			->order_by("class_order")
			->get()
			->result_array();

		$data['rewards_counts']=$rewards_counts;

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("reward_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_student_total_rewards($student_id)
	{
		return $this->db
			->select("SUM(rv_value) as sum")
			->from($this->reward_value_table_name)
			->join($this->reward_table_name,"rv_reward_id = reward_id ","LEFT")
			->where("rv_student_id",$student_id)
			->where("reward_time_id", $this->current_academic_time_id)
			->group_by("rv_student_id")
			->get()
			->row_array()['sum'];
	}

	public function start_new_time($old_time,$new_time)
	{
		$new_tid=$new_time['time_id'];
		$old_tid=$old_time['time_id'];
		$this->load->model("class_manager_model");
		$classes=$this->class_manager_model->get_all_classes();

		$subject="<<";
		foreach($classes as $c)
		{
			$class_id=$c['class_id'];
			$rewards=array();
			foreach($this->get_class_students_with_total_rewards($class_id, $old_tid) as $r)
				if($r['total_rewards'])
					$rewards[]=array(
						'student_id'	=> $r['customer_id']
						,"value"			=> $r['total_rewards']
						,"description"	=> ''
					);

			$this->add_rewards_with_time_id(0,$class_id,$subject,$rewards,0,$new_tid);
		}

		$log=array(
			"new_time_id"		=> $new_tid
			,"prev_time_id"	=> $old_tid
		);

		$this->log_manager_model->info("REWARD_MOVING_PREVIOUS_YEAR",$log);	

		
		return;
	}

	public function get_student_rewards($student_id)
	{
		return $this->db
			->select("rv_description,rv_value,reward_id,reward_subject,reward_date,reward_is_prize, customer_name")
			->from($this->reward_value_table_name)
			->join($this->reward_table_name,"rv_reward_id = reward_id ","LEFT")
			->join("customer","reward_teacher_id = customer_id ","LEFT")
			->where("rv_student_id",$student_id)
			->where("reward_time_id", $this->current_academic_time_id)
			->order_by("reward_date ASC")
			->get()
			->result_array();
	}

	public function add_rewards($teacher_id,$class_id,$subject,$rewards,$is_prize)
	{
		$time_id=$this->current_academic_time_id;
		return $this->add_rewards_with_time_id($teacher_id,$class_id,$subject,$rewards,$is_prize,$time_id);
	}

	private function add_rewards_with_time_id($teacher_id,$class_id,$subject,$rewards,$is_prize,$time_id)
	{
		$date=get_current_time();
		$log=array();

		$this->db->insert($this->reward_table_name,array(
			"reward_teacher_id"	=> $teacher_id
			,"reward_time_id"		=> $time_id
			,"reward_class_id"	=> $class_id
			,"reward_date"			=> $date
			,"reward_subject"		=> $subject
			,"reward_is_prize"	=> $is_prize
		));

		$reward_id=$this->db->insert_id();

		$log['reward_subject']=$subject;
		$log['reward_id']=$reward_id;
		$log['reward_date']=$date;
		$log['reward_is_prize']=$is_prize;
		$log['reward_time_id']=$time_id;

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
		
		return $this->class_manager_model->get_class_teachers(-1);
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

	public function get_class_students_with_total_rewards($class_id,$time_id=0)
	{
		if(!$time_id)
			$time_id= $this->current_academic_time_id;

		$sub_query=$this->db
			->select("SUM(rv_value)")
			->from($this->reward_value_table_name)
			->join($this->reward_table_name,"reward_id = rv_reward_id","LEFT")
			->where("reward_time_id",$time_id)
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

	public function get_total_rewards($filter)
	{
		$this->db
			->select("COUNT(*) as count")
			->from($this->reward_table_name);
		
		$this->set_search_where_clause($filter);

		$row=$this->db->get()->row_array();

		return $row['count'];
	}

	public function get_rewards(&$filter)
	{
		$this->db
			->select("r.*,class_name,customer_name,customer_subject")
			->from($this->reward_table_name." r")
			->join("class","reward_class_id = class_id","LEFT")
			->join("customer","reward_teacher_id = customer_id","LEFT");

		$this->set_search_where_clause($filter);
		
		return $this->db
			->order_by("reward_date ASC")
			->get()
			->result_array();
	}

	private function set_search_where_clause(&$filter)
	{
		if(isset($filter['subject']))
		{
			$filter['subject']=persian_normalize($filter['subject']);
			$this->db->where("reward_subject LIKE '%".str_replace(' ', '%', $filter['subject'])."%'");
		}

		if(isset($filter['time_id']))
		{
			$this->db->where("reward_time_id",$filter['time_id']);
		}

		if(isset($filter['this_year']))
			$this->db->where("reward_time_id",$this->current_academic_time_id);

		if(isset($filter['start_date']))
		{
			$filter['start_date']=persian_normalize($filter['start_date']);
			validate_persian_date($filter['start_date']);
			$this->db->where("reward_date >=",$filter['start_date']." 00:00:00");
		}

		if(isset($filter['end_date']))
		{
			$filter['end_date']=persian_normalize($filter['end_date']);
			validate_persian_date($filter['end_date']);
			$this->db->where("reward_date <=",$filter['end_date']." 23:59:59");
		}

		if(isset($filter['teacher_id']))
		{
			$this->db->where("reward_teacher_id",(int)$filter['teacher_id']);
		}

		if(isset($filter['class_id']))
		{
			$this->db->where("reward_class_id",(int)$filter['class_id']);
			$this->db->where("reward_time_id",$this->current_academic_time_id);
		}

		if(isset($filter['is_prize']))
		{
			$this->db->where("reward_is_prize",(int)$filter['is_prize']);
		}

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);


		return;
	}

	public function get_reward_info($reward_id)
	{
		$date=DATE_FUNCTION;
		$date=$date("Y/m/d H:i:s",time()-$this->reward_edit_time);

		return $this->db
			->select("r.* , (reward_date > '$date') as reward_editable, class_name,customer_name as teacher_name, time_name")
			->from($this->reward_table_name." r")
			->join("class","reward_class_id = class_id","LEFT")
			->join("customer","reward_teacher_id = customer_id","LEFT")
			->join("time","reward_time_id = time_id","LEFT")
			->where("reward_id",$reward_id)
			->get()
			->row_array();
	}

	public function get_teacher_reward_info($reward_id)
	{
		$date=DATE_FUNCTION;
		$date=$date("Y/m/d H:i:s",time()-$this->reward_edit_time);

		return $this->db
			->select("r.* , (reward_date > '$date') as reward_editable, class_name,customer_name as teacher_name")
			->from($this->reward_table_name." r")
			->join("class","reward_class_id = class_id","LEFT")
			->join("customer","reward_teacher_id = customer_id","LEFT")
			->where("reward_time_id", $this->current_academic_time_id)
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

	public function edit_rewards($reward_id,$subject,$rewards,$teacher_id)
	{
		$log=array();

		$this->db
			->set("reward_subject",$subject)
			->where("reward_id",$reward_id)
			->update($this->reward_table_name);

		$log['new_subject']=$subject;

		$this->db
			->where("rv_reward_id",$reward_id)
			->delete($this->reward_value_table_name);

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

		$this->log_manager_model->info("REWARD_EDIT",$log);	

		$this->load->model("customer_manager_model");
		$log['teacher_id']=$teacher_id;
		$this->customer_manager_model->add_customer_log($teacher_id,'REWARD_EDIT',$log);	

		return $reward_id;
	}

}