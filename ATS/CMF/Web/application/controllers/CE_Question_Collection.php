<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Question_Collection extends Burge_CMF_Controller {
	protected $hit_level=2;
	private $customer_info=NULL;

	function __construct()
	{
		parent::__construct();
		
		$this->load->model(array(
			"question_collector_model"
			,"class_manager_model"
			)
		);

		$this->lang->load('ce_question_collection',$this->selected_lang);

	}

	public function grade_list($grade_id,$course_id)
	{
		$grade_id=(int)$grade_id;
		$course_id=(int)$course_id;

		$grade_names=$this->class_manager_model->get_grades_names($this->selected_lang);
		if(!$grade_id)
		{
			foreach($grade_names as $gid => $gname)
				break;
			
			return redirect(get_customer_question_collection_list_link($gid,0));
		}

		$this->data['grades_names']=$grade_names;

		$courses_names=$this->class_manager_model->get_courses_names($this->selected_lang);
		$this->data['courses_names']=$courses_names;

		$this->data['grade_id']=$grade_id;
		$this->data['course_id']=$course_id;
		$filters=array(
			'grade_id'	=> $grade_id
			,'order_by'	=> "qc_course_id ASC, qc_subject ASC"
		);

		if($course_id)
			$filters['course_id']=$course_id;

		$this->data['questions']=$this->question_collector_model->get_questions($filters);
		//bprint_r($this->data['questions']);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_question_collection_list_link($grade_id,$course_id,TRUE));

		$this->data['header_title']=
			$this->lang->line("grade")." "
			.$grade_names[$grade_id]
			.$this->lang->line("header_separator")
			.$this->lang->line("questions_collection")
			.$this->lang->line("header_separator")
			.$this->data['header_title'];
		$this->data['header_meta_description']=
			$this->lang->line("questions_collection")." "
			.$this->lang->line("grade")." "
			.$grade_names[$grade_id];
		$this->data['header_meta_keywords'].=","
			.$this->lang->line("questions_collection")
			.",".$this->lang->line("grade")
			." ".$grade_names[$grade_id];

		if($course_id)
		{
			$this->data['header_title']=
				$this->lang->line("course")." "
				.$courses_names[$course_id]
				.$this->lang->line("header_separator")
				.$this->data['header_title'];

			$this->data['header_meta_description'].=" ".$this->lang->line("course")." ".$courses_names[$course_id];
			$this->data['header_meta_keywords'].=",".$this->lang->line("course")." ".$courses_names[$course_id];
		}

		$this->data['header_canonical_url']=get_customer_question_collection_list_link($grade_id,$course_id);

		$this->send_customer_output("question_collection_grade_list");
	}

	public function details($garde_id,$course_id,$qid)
	{
		$grade_id=(int)$garde_id;
		$course_id=(int)$course_id;
		$qid=(int)$qid;
		if(!$grade_id || !$course_id || !$qid)
			redirect(get_link("home_url"));

		$info=$this->question_collector_model->get_question_info($qid);

		if(!$info || ($grade_id != $info[0]['qc_grade_id']) || ($course_id != $info[0]['qc_course_id']))
			return redirect(get_link("home_url"));

		$this->data['info']=$info;

		$this->data['message']=get_message();

		$grades_names=$this->class_manager_model->get_grades_names($this->selected_lang);
		$courses_names=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['grade_name']=$grades_names[$grade_id];
		$this->data['course_name']=$courses_names[$course_id];

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_question_collection_details_link($grade_id,$course_id,$qid,TRUE));

		$this->data['header_title']=
			$info[0]['qc_subject']
			.$this->lang->line("header_separator")
			.$this->lang->line("course")." "
			.$this->data['course_name']
			.$this->lang->line("header_separator")
			.$this->lang->line("grade")." "
			.$this->data['grade_name']
			.$this->lang->line("header_separator")
			.$this->lang->line("questions_collection")
			.$this->lang->line("header_separator")
			.$this->data['header_title'];

		$this->data['header_meta_description']=
			$this->lang->line("questions_collection")." "
			.$this->lang->line("grade")." "
			.$this->data['grade_name']." "
			.$this->lang->line("course")." "
			.$this->data['course_name']." "
			.$info[0]['qc_subject'];

		$this->data['header_meta_keywords'].=","
			.$this->lang->line("questions_collection")
			.",".$this->lang->line("grade")
			." ".$this->data['grade_name']
			.",".$this->lang->line("course")
			." ".$this->data['course_name']
			.",".$info[0]['qc_subject'];

		$this->data['header_canonical_url']=get_customer_question_collection_details_link($grade_id,$course_id,$qid);

		$this->send_customer_output("question_collection_details");

		return;	 
	}

	public function teacher_submit()
	{
		$this->load->model("customer_manager_model");
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			
		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];
		$grade_ids=$this->class_manager_model->get_teacher_grades($teacher_id);
		if(!$grade_ids)
		{
			redirect(get_link("customer_dashboard"));
			return;
		}

		$this->data['grade_ids']=$grade_ids;

		if($this->input->post("post_type")==="add_question")
			return $this->add_question($teacher_id);

		$this->data['grades_names']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['courses_names']=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("customer_question_collection_teacher_submit");
		$this->data['lang_pages']=get_lang_pages(get_link("customer_question_collection_teacher_submit",TRUE));
		$this->data['header_title']=
			$this->lang->line("questions_collection")
			.$this->lang->line("header_separator")
			.$this->lang->line("submit");

		$this->send_customer_output("question_collection_teacher_submit");

		return;
	}

	private function add_question($teacher_id)
	{
		$grade_id=$this->input->post("grade_id");
		if($grade_id && !in_array($grade_id, $this->data['grade_ids']))
			return redirect(get_link("customer_question_collection_teacher_submit"));

		$course_id=$this->input->post("course_id");
		$subject=$this->input->post("subject");
		$files=array();

		$file_count=$this->input->post("file_count");
		for($i=0;$i<$file_count;$i++)
		{
			$file_name=$_FILES['files']['name'][$i];
			$file_tmp_name=$_FILES['files']['tmp_name'][$i];
			$extension=pathinfo($file_name, PATHINFO_EXTENSION);
			$file_subject=$this->input->post('subjects')[$i];
			$file_error=$_FILES['files']['error'][$i];
			$file_size=$_FILES['files']['size'][$i];

			if($file_error)
				continue;
			
			$files[]=array(
				"temp_name"=>$file_tmp_name
				,"extension"=>$extension
				,"subject"=>$file_subject
				,"size"=>$file_size
			);
			//echo $file_name."#<br>".$file_tmp_name."#<br>".$subject."#<br>".$file_error."#<br>".$file_size."#<br>*</br>";
		}

		if(!$grade_id || !$course_id || !$subject || !$files)
		{
			set_message($this->lang->line("please_fill_all_fields"));
			return redirect(get_link("customer_question_collection_teacher_submit"));
		}

		$this->question_collector_model->add($grade_id,$course_id,$subject,$files,"teacher",$teacher_id);

		set_message($this->lang->line("the_new_question_collection_added_successfully"));

		return redirect(get_link("customer_question_collection_teacher_list"));
	}

	public function teacher_list()
	{
		$this->load->model("customer_manager_model");
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			
		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];
		$grade_ids=$this->class_manager_model->get_teacher_grades($teacher_id);
		if(!$grade_ids)
		{
			redirect(get_link("customer_dashboard"));
			return;
		}

		$this->data['questions']=$this->question_collector_model->get_questions(
			array(
				"registrar_type"=>"teacher"
				,"registrar_id"=>$teacher_id
				,"order_by"=>"qc_grade_id ASC, qc_course_id ASC, qc_id ASC"
			)
		);

		$this->data['grades_names']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['courses_names']=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("customer_question_collection_teacher_list");
		$this->data['lang_pages']=get_lang_pages(get_link("customer_question_collection_teacher_list",TRUE));
		$this->data['header_title']=$this->lang->line("questions_collection");

		$this->send_customer_output("question_collection_teacher_list");

		return;
	}

}