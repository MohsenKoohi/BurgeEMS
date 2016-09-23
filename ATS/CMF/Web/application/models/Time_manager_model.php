<?php
class Time_manager_model extends CI_Model
{
	private $time_table_name="time";
	private $current_academic_year=NULL;

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
			
		$data=$this->get_current_academic_year();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("time_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_current_academic_year()
	{
		if($this->current_academic_year)
			return $this->current_academic_year;

		$sub=$this->db
			->select("MAX(time_id)")
			->from($this->time_table_name)
			->get_compiled_select();

		$result=$this->db
			->from($this->time_table_name)
			->select("*")
			->where("time_id = ($sub)")
			->get();

		$this->current_academic_year=$result->row_array();

		return $this->current_academic_year;
	}

	public function get_all_times()
	{
		$result=$this->db
			->from($this->time_table_name)
			->select("*")
			->order_by("time_id ASC")
			->get();

		return $result->result_array();
	}

	public function add($name)
	{
		$prev_time=$this->get_current_academic_year();

		$this->db->insert($this->time_table_name,array("time_name"=>$name));
		
		$this->current_academic_year=NULL;
		$new_time=$this->get_current_academic_year();

		$this->complete_previous_time_actions($prev_time, $new_time)

		return;
	}

	private function complete_previous_time_actions($prev_time, $new_time)
	{

		return;
	}

}
