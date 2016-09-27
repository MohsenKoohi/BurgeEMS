<?php
class Reward_manager_model extends CI_Model
{
	private $reward_table_name="reward";
	
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
				`reward_student_id` INT
				,`reward_teacher_id` INT
				,`reward_date` CHAR(19)
				,`reward_value` INT
				,`reward_description` VARCHAR(255) 
				,PRIMARY KEY (reward_student_id,reward_teacher_id,reward_date)	
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
		return;
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_customer',$lang);		
		
		$data['total_text']=$this->lang->line("total");
		$data['customers_count']=$this->get_total_customers();
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("customer_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function add_rewards($teacher_id,$rewards)
	{
		$date=get_current_time();

		$ins=array();
		$log=array();

		foreach($rewards as $reward)
		{
			$ins[]=array(
				"reward_student_id"=>$reward['student_id']
				,"reward_teacher_id"=>$teacher_id
				,"reward_date"=>$date
				,"reward_value"=>intval($reward['value'])
				,"reward_description"=>$reward['description']
			);

			$log['student_'.$reward['student_id'].'_reward']=$reward['value'];
			$log['student_'.$reward['student_id'].'_description']=$reward['description'];
		}

		if(!$ins)
			return;

		$this->db->insert_batch($this->reward_table_name,$ins);

		$this->log_manager_model->info("REWARD_ADD",$log);	

		$this->load->model("customer_manager_model");
		$log['teacher_id']=$teacher_id;
		$this->customer_manager_model->add_customer_log($teacher_id,'REWARD_ADD',$log);	

		return;
	}

}