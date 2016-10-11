<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Question_Collection extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_question_collection',$this->selected_lang);
		
		$this->load->model(array(
			"question_collector_model"
			)
		);

	}

	public function index()
	{
		if($this->input->post("post_type")==="add_question")
			return $this->add_question();

		//$this->data['rewards']=$this->reward_manager_model->get_all_rewards();
		
		$this->data['message']=get_message();

		$this->data['grades_count']=$this->question_collector_model->get_grades_count();
		$this->data['courses_count']=$this->question_collector_model->get_courses_count();

		$this->data['raw_page_url']=get_link("admin_question_collection");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_question_collection",TRUE));
		$this->data['header_title']=$this->lang->line("questions_collection");

		$this->send_admin_output("question_collection");

		return;	 
	}

	private function add_question()
	{
		$grade_id=$this->input->post("grade");
		$course_id=$this->input->post("course");
		$subject=$this->input->post("subject");
		$files=array();

		$file_count=$this->input->post("file_count");
		for($i=0;$i<$file_count;$i++)
		{
			$file_name=$_FILES['files']['name'][$i];
			$file_tmp_name=$_FILES['files']['tmp_name'][$i];
			$extension=pathinfo($file_name, PATHINFO_EXTENSION);
			$subject=$this->input->post('subjects')[$i];
			$file_error=$_FILES['files']['error'][$i];
			$file_size=$_FILES['files']['size'][$i];

			if($file_error)
				continue;
			
			$files[]=array(
				"temp_name"=>$file_tmp_name
				,"extension"=>$extension
				,"subject"=>$subject
				,"size"=>$file_size
			);
			//echo $file_name."#<br>".$file_tmp_name."#<br>".$subject."#<br>".$file_error."#<br>".$file_size."#<br>*</br>";
		}

		if(!$subject || !$files)
		{
			set_message($this->lang->line("please_fill_all_fields"));
			return redirect(get_link("admin_question_collection")."#add");
		}

		$this->load->model("user_manager_model");
		$user=$this->user_manager_model->get_user_info();
		$user_id=$user->get_id();

		$this->question_collector_model->add($grade_id,$course_id,$subject,$files,"user",$user_id);

		set_message($this->lang->line("question_added_successfully"));
		return redirect(get_link("admin_question_collection"));

	}
}