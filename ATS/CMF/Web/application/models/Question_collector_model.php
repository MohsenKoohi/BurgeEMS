<?php
class Question_collector_model extends CI_Model
{
	private $question_collection_table_name="question_collection";
	private $question_collection_files_table_name="question_collection_files";
	private $question_files_dir=NULL;

	public function __construct()
	{
		parent::__construct();

		$this->question_files_dir=UPLOAD_DIR."/questions";

		return;
	}

	public function install()
	{
		if(make_dir_and_check_permission($this->question_files_dir)<0)
		{
			echo "Error: ".$this->question_files_dir." cant be used, please check permissions, and try again";
			exit;
		}

		$tbl=$this->db->dbprefix($this->question_collection_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`qc_id` INT  NOT NULL AUTO_INCREMENT
				,`qc_subject` VARCHAR(511)
				,`qc_grade_id` INT
				,`qc_course_id` INT
				,`qc_date` CHAR(20)
				,`qc_registrar_type` ENUM ('user','teacher')
				,`qc_registrar_id` INT
				,PRIMARY KEY (qc_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl=$this->db->dbprefix($this->question_collection_files_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`qcf_id` INT  NOT NULL AUTO_INCREMENT
				,`qcf_qc_id` INT NOT NULL
				,`qcf_subject` VARCHAR(511)
				,`qcf_hash` CHAR(5)
				,PRIMARY KEY (qcf_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("question_collection","question_collector");
		$this->module_manager_model->add_module_names_from_lang_file("question_collection");
				
		return;
	}

	public function uninstall()
	{
		return;
	}
	
	public function get_dashboard_info()
	{

		return ;

		$CI=& get_instance();
		$lang=$CI->language->get();

		$CI->lang->load('ae_class',$lang);
			
		$data['classes']=$this->get_all_classes();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("class_dashboard"),$data,TRUE);
		
		return $ret;		
	}


}