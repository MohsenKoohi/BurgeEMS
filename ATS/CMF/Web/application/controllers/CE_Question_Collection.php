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
			,'order_by'	=> "qc_id ASC"
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
			." ".$this->lang->line("grade")
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

}