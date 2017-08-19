<?php
class Time_manager_model extends CI_Model
{
	private $time_table_name="time";
	private $current_academic_time=NULL;

	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$time_table=$this->db->dbprefix($this->time_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $time_table (
				`time_id` INT  NOT NULL AUTO_INCREMENT
				,`time_name` VARCHAR(255)
				,PRIMARY KEY (time_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("time","time_manager");
		$this->module_manager_model->add_module_names_from_lang_file("time");
		
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

		$CI->lang->load('ae_time',$lang);
			
		$data=$this->get_current_academic_time();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("time_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_current_academic_time()
	{
		if($this->current_academic_time)
			return $this->current_academic_time;

		$sub=$this->db
			->select("MAX(time_id)")
			->from($this->time_table_name)
			->get_compiled_select();

		$result=$this->db
			->from($this->time_table_name)
			->select("*")
			->where("time_id = ($sub)")
			->get();

		$this->current_academic_time=$result->row_array();

		return $this->current_academic_time;
	}

	public function get_current_academic_time_name()
	{
		$time=$this->get_current_academic_time();
		return $time['time_name'];
	}

	public function get_current_academic_time_id()
	{
		$time=$this->get_current_academic_time();
		return $time['time_id'];
	}

	public function get_all_times()
	{
		$result=$this->db
			->from($this->time_table_name)
			->select("*")
			->order_by("time_id DESC")
			->get();

		return $result->result_array();
	}

	public function add($name)
	{
		$prev_time=$this->get_current_academic_time();

		$this->db->insert($this->time_table_name,array("time_name"=>$name));
		
		$this->current_academic_time=NULL;
		$new_time=$this->get_current_academic_time();

		$this->complete_previous_time_actions($prev_time, $new_time);

		$this->log_manager_model->info("TIME_ADD",$new_time);	

		return;
	}

	private function complete_previous_time_actions($prev_time, $new_time)
	{
		$this->load->model("class_manager_model");
		$this->class_manager_model->start_new_time($prev_time, $new_time);

		$this->load->model("reward_manager_model");
		$this->reward_manager_model->start_new_time($prev_time, $new_time);

		return;
	}

}
