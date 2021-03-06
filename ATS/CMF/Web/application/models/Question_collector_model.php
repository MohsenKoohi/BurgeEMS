<?php
class Question_collector_model extends CI_Model
{
	private $question_collection_table_name="question_collection";
	private $question_collection_files_table_name="question_collection_files";

	private $question_files_dir=NULL;
	private $question_files_url=NULL;

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
				,`qc_grade_id` INT
				,`qc_course_id` INT
				,`qc_subject` VARCHAR(511)
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

	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$CI->load->model("class_manager_model");

		$lang=$CI->language->get();
		$grades_names=$this->class_manager_model->get_grades_names($lang);

		$counts=$this->db
			->select("qc_grade_id, COUNT(*) as grade_count")
			->from($this->question_collection_table_name)
			->group_by("qc_grade_id")
			->get()
			->result_array();

		$grades_counts=array();
		foreach($counts as $count)
			$grades_counts[$count['qc_grade_id']]=array(
				"grade_name"=>$grades_names[$count['qc_grade_id']]
				,"grade_count"=>$count['grade_count']
			);
		$data['grades_counts']=$grades_counts;

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("question_collection_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_total_questions($filter)
	{
		$this->db
			->select("COUNT(*) as count")
			->from($this->question_collection_table_name);
		
		$this->set_search_where_clause($filter);

		$row=$this->db->get()->row_array();

		return $row['count'];
	
	}

	public function get_questions($filter)
	{
		$this->db
			->select("*")
			->from($this->question_collection_table_name)
			->join("user","user_id = qc_registrar_id","LEFT")
			->join("customer","customer_id = qc_registrar_id","LEFT");
			
		$this->set_search_where_clause($filter);

		$ret=$this->db
			->get()
			->result_array();

		foreach($ret as &$q)
		{
			$rtype=$q['qc_registrar_type'];
			if($rtype==="user")
				$rname=$q['user_name']." (".$q['user_code'].")";
			else
				$rname=$q['customer_name'];

			$q['qc_registrar_name']=$rname;

			unset($q['customer_name'],$q['user_code'],$q['user_name']);
		}

		return $ret;
	}

	private function set_search_where_clause($filter)
	{
		if(isset($filter['registrar_type']))
			$this->db->where("qc_registrar_type",$filter['registrar_type']);

		if(isset($filter['registrar_id']))
			$this->db->where("qc_registrar_id",(int)$filter['registrar_id']);

		if(isset($filter['user_id']) )
			$this->db->where("qc_registrar_id",(int)$filter['user_id']);

		if(isset($filter['teacher_id']))
			$this->db->where("qc_registrar_id",(int)$filter['teacher_id']);

		if(isset($filter['grade_id']))
			$this->db->where("qc_grade_id",(int)$filter['grade_id']);

		if(isset($filter['course_id']))
			$this->db->where("qc_course_id",(int)$filter['course_id']);

		if(isset($filter['subject']))
		{
			$this->db->where("qc_subject LIKE '%".str_replace(' ', '%', $filter['subject'])."%'");
		}

		if(isset($filter['start_date']))
		{
			$this->db->where("qc_date >=",$filter['start_date']." 00:00:00");
		}

		if(isset($filter['end_date']))
		{
			$this->db->where("qc_date <=",$filter['end_date']." 23:59:59");
		}

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);

		return;		
	}

	public function get_question_info($qc_id)
	{
		$ret=$this->db
			->select("qc.* , qcf.* , user_code, user_name, customer_name")
			->from($this->question_collection_table_name." qc")
			->join($this->question_collection_files_table_name." qcf","qc_id = qcf_qc_id","LEFT")
			->join("user","user_id = qc_registrar_id","LEFT")
			->join("customer","customer_id = qc_registrar_id","LEFT")
			->where("qc_id",$qc_id)
			->order_by("qc_id DESC")
			->get()
			->result_array();

		if($ret)
		{
			$rtype=$ret[0]['qc_registrar_type'];
			if($rtype==="user")
				$rname=$ret[0]['user_name']." (".$ret[0]['user_code'].")";
			else
				$rname=$ret[0]['customer_name'];

			foreach($ret as &$q)
			{
				$q['qcf_url']=$this->get_question_file_url($qc_id,$q['qcf_id'],$q['qcf_hash'],$q['qcf_extension']);
				$q['qc_registrar_name']=$rname;

				unset($q['customer_name'],$q['user_code'],$q['user_name']);
			}
		}

		return $ret;
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
		if('teacher' === $registrar_type)
			$this->customer_manager_model->add_customer_log($registrar_id,'QUESTION_COLLECTION_ADD',$log);

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

	public function delete($qc_id)
	{
		$log=array("qc_id"=>$qc_id);

		$files=$this->db
			->select("*")
			->from($this->question_collection_files_table_name)
			->where("qcf_qc_id",$qc_id)
			->get()
			->result_array();

		$i=0;
		foreach($files as $f)
		{
			$filename=$this->get_question_file_dir($qc_id,$f['qcf_id'],$f['qcf_hash'],$f['qcf_extension']);
			unlink($filename);
			$log['file_'.$i++]=$filename;
		}

		$this->db
			->where("qc_id",$qc_id)
			->delete($this->question_collection_table_name);

		$this->db
			->where("qcf_qc_id",$qc_id)
			->delete($this->question_collection_files_table_name);

		$this->log_manager_model->info("QUESTION_COLLECTION_DELETE",$log);

		return;
	}	


}