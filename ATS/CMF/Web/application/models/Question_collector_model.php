<?php
class Question_collector_model extends CI_Model
{
	private $question_collection_table_name="question_collection";
	private $question_collection_files_table_name="question_collection_files";

	private $question_files_dir=NULL;
	private $question_files_url=NULL;

	private $grades_count=6;
	private $courses_count=3;

	public function __construct()
	{
		parent::__construct();

		$this->question_files_dir=UPLOAD_DIR."/questions";
		$this->question_files_url=UPLOAD_URL."/questions";

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
				,`qcf_extension` CHAR(5)
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

	public function get_grades_count()
	{
		return $this->grades_count;
	}

	public function get_courses_count()
	{
		return $this->courses_count;
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

	public function get_questions()
	{
		return $this->db
			->select("*")
			->from($this->question_collection_table_name)
			->order_by("qc_id DESC")
			->get()
			->result_array();
	}

	public function add($grade_id,$course_id,$subject,$files,$registrar_type,$registrar_id)
	{
		$inp=array(
			"qc_grade_id" 			=> $grade_id
			,"qc_course_id"		=> $course_id
			,"qc_subject"			=> $subject
			,"qc_date"				=> get_current_time()
			,"qc_registrar_type"	=> $registrar_type
			,"qc_registrar_id"	=> $registrar_id
		);
		$this->db->insert($this->question_collection_table_name,$inp);

		$qc_id=$this->db->insert_id();

		$log=$inp;
		$log['qc_id']=$qc_id;

		$ins=array();
		$i=0;
		foreach($files as $f)
		{
			$hash=get_random_word(5,TRUE);
			$this->db->insert($this->question_collection_files_table_name,array(
				"qcf_qc_id"			=>	$qc_id
				,"qcf_subject"		=> $f['subject']
				,"qcf_extension"	=> $f['extension']
				,"qcf_hash"			=> $hash
			));

			$qcf_id=$this->db->insert_id();
			$path=$this->get_question_file_dir($qc_id,$qcf_id,$hash,$f['extension']);

			@move_uploaded_file($f['temp_name'],$path);

			$log['file_'.$i.'_id']=$qcf_id;
			$log['file_'.$i.'_hash']=$hash;
			$log['file_'.$i.'_extension']=$f['extension'];
			$log['file_'.$i.'_subject']=$f['subject'];

			$i++;
		}

		$this->log_manager_model->info("QUESTION_COLLECTION_ADD",$log);	

		return $qc_id;
	}

	private function get_question_file_url($qc_id,$qcf_id,$qcf_hash,$qcf_extension)
	{
		return $this->question_files_url."/".$qc_id."_".$qcf_id."_".$qcf_hash.".".$qcf_extension;
	}

	private function get_question_file_dir($qc_id,$qcf_id,$qcf_hash,$qcf_extension)
	{
		return $this->question_files_dir."/".$qc_id."_".$qcf_id."_".$qcf_hash.".".$qcf_extension;
	}


}