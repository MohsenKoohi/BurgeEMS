<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Question_Collection extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_question_collection',$this->selected_lang);
		
		$this->load->model(array(
			"question_collector_model"
			,"class_manager_model"
			)
		);

	}

	public function index()
	{
		if($this->input->post("post_type")==="add_question")
			return $this->add_question();

		$this->set_questions();

		$this->load->model("user_manager_model");
		$this->data['users']=$this->user_manager_model->get_users();
		$this->data['teachers']=$this->class_manager_model->get_teachers();
		$this->data['grades']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['courses']=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['message']=get_message();

		$this->data['grades_names']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['courses_names']=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['raw_page_url']=get_link("admin_question_collection");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_question_collection",TRUE));
		$this->data['header_title']=$this->lang->line("questions_collection");

		$this->send_admin_output("question_collection");

		return;	 
	}

	private function set_questions()
	{
		$items_per_page=20;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$filter=array();

		$pfnames=array("grade_id","course_id","subject","start_date","end_date","registrar_type","teacher_id","user_id");
		foreach($pfnames as $pfname)
			if($this->input->get($pfname)!==NULL)
				$filter[$pfname]=$this->input->get($pfname);	

		if(isset($filter['start_date']))
		{
			$filter['start_date']=persian_normalize($filter['start_date']);
			validate_persian_date($filter['start_date']);
		}

		if(isset($filter['end_date']))
		{
			$filter['end_date']=persian_normalize($filter['end_date']);
			validate_persian_date($filter['end_date']);
		}

		if(isset($filter['subject']))
		{
			$filter['subject']=persian_normalize($filter['subject']);
		}

		$total=$this->question_collector_model->get_total_questions($filter);
		$this->data['total_count']=$total;
		$this->data['total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['total_pages'])
				$page=$this->data['total_pages'];
			if($page<1)
				$page=1;
			$this->data['current_page']=$page;
			
			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['results_start']=$start+1;
			$this->data['results_end']=$end+1;		
	
			$filter['order_by']="qc_id DESC";

			$this->data['questions']=$this->question_collector_model->get_questions($filter);

			unset($filter['start'],$filter['length'],$filter['order_by']);
		}
		else
		{
			$this->data['results_start']=0;
			$this->data['results_end']=0;
			$this->data['questions']=array();
		}

		$this->data['filter']=$filter;

		return;
	}

	private function add_question()
	{
		$grade_id=$this->input->post("grade_id");
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
			return redirect(get_link("admin_question_collection")."#add");
		}

		$this->load->model("user_manager_model");
		$user=$this->user_manager_model->get_user_info();
		$user_id=$user->get_id();

		$this->question_collector_model->add($grade_id,$course_id,$subject,$files,"user",$user_id);

		set_message($this->lang->line("the_new_question_collection_added_successfully"));
		return redirect(get_link("admin_question_collection"));
	}

	public function details($qid)
	{
		$qid=(int)$qid;
		$info=$this->question_collector_model->get_question_info($qid);

		if(!$info)
			return redirect(get_link("admin_question_collection"));

		if($this->input->post("post_type")==="delete_qc")
			return $this->delete($qid);

		$this->data['info']=$info;

		$this->data['message']=get_message();

		$this->data['grades_names']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['courses_names']=$this->class_manager_model->get_courses_names($this->selected_lang);

		$this->data['raw_page_url']=get_admin_question_collection_details_link($qid);
		$this->data['lang_pages']=get_lang_pages(get_admin_question_collection_details_link($qid,TRUE));
		$this->data['header_title']=
			$this->lang->line("questions_collection")
			.$this->lang->line("header_separator")
			.$info[0]['qc_subject'];

		$this->send_admin_output("question_collection_details");

		return;	 
	}

	private function delete($qid)
	{
		$this->question_collector_model->delete($qid);

		set_message($this->lang->line("question_set_deleted_successfully"));

		return redirect(get_link("admin_question_collection"));
	}
}