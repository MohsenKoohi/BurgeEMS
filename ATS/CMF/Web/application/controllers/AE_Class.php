<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Class extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_class',$this->selected_lang);
		$this->load->model("class_manager_model");

	}

	public function index()
	{
		if($this->input->post("post_type")==="add_class")
			return $this->add_class();

		if($this->input->post("post_type")==="class_changes")
			return $this->class_changes();

		if($this->input->post("post_type")==="set_curriculum_hours")
			return $this->set_curriculum_hours();

		$this->data['classes']=$this->class_manager_model->get_all_classes();
		$this->data['grades']=$this->class_manager_model->get_grades_names($this->selected_lang);
		$this->data['curriculum_hours']=$this->class_manager_model->get_curriculum_hours();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_class");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_class",TRUE));
		$this->data['header_title']=$this->lang->line("classes");

		$this->send_admin_output("class");

		return;	 
	}

	private function set_curriculum_hours()
	{
		$max_index=$this->input->post("max_index");
		$hours=array();
		for($i=0;$i<$max_index;$i++)
		{
			$hour=trim($this->input->post("hour-".$i));
			if($hour)
				$hours[]=persian_normalize_word($hour);
		}

		$this->class_manager_model->set_curriculum_hours($hours);

		set_message($this->lang->line("modifications_have_been_done_successfully"));


		return redirect(get_link("admin_class")."#curriculum");
	}

	private function class_changes()
	{
		$ids=$this->input->post("class-ids");
		$ids_exp=explode(",", $ids);

		$new_props=array();
		foreach($ids_exp as $id)
			$new_props[]=array(
				"class_id"=>$id
				,"class_name"=>$this->input->post("class-".$id)
				,"class_grade_id"=>(int)$this->input->post("grade-id-".$id)
			);

		if($new_props)
			$this->class_manager_model->set_class_props($new_props);

		if($ids)
			$this->class_manager_model->resort_classes($ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_link("admin_class"));
	}

	private function add_class()
	{
		$name=$this->input->post("name");
		$grade_id=(int)$this->input->post("grade-id");

		$this->class_manager_model->add($name,$grade_id);

		set_message($this->lang->line("new_class_added_successfully"));

		return redirect(get_link("admin_class"));
	}

	public function details($class_id)
	{
		$class_id=(int)$class_id;

		$info=$this->class_manager_model->get_class($class_id);
		if(!$info)
		{
			set_message($this->lang->line("class_not_found"));
			return redirect(get_link("admin_class"));		
		}

		if($this->input->post("post_type")==="students_resort")
			return $this->students_resort($class_id);

		if($this->input->post("post_type")==="set_teachers")
			return $this->set_teachers($class_id);		

		if($this->input->post("post_type")==="delete_class")
			return $this->delete_class($class_id);

		if($this->input->post("post_type")==="set_curriculum")
			return $this->set_class_curriculum($class_id);

		$this->data['info']=$info;
		$this->data['teachers']=$this->class_manager_model->get_class_teachers($class_id);
		$this->data['students']=$this->class_manager_model->get_students($class_id);
		$this->data['curriculum_hours']=$this->class_manager_model->get_curriculum_hours();
		$this->data['curriculum']=$this->class_manager_model->get_class_curriculum($class_id);

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_admin_class_details_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_admin_class_details_link($class_id,TRUE));
		$this->data['header_title']=$info['class_name'];

		$this->send_admin_output("class_details");
	}

	private function set_class_curriculum($class_id)
	{
		$this->class_manager_model->set_class_curriculum($class_id,$this->input->post("course"));

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_admin_class_details_link($class_id)."#curriculum");
	}

	private function delete_class($class_id)
	{	
		if($this->class_manager_model->delete_class($class_id))
		{
			set_message($this->lang->line("class_deleted_successfully"));
			return redirect(get_link("admin_class"));	
		}

		set_message($this->lang->line("it_is_not_possible_to_delete"));
		return redirect(get_admin_class_details_link($class_id));
	}

	private function students_resort($class_id)
	{
		$ids=$this->input->post("students-ids");

		if($ids)
			$this->class_manager_model->resort_students($ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_admin_class_details_link($class_id));
	}

	private function set_teachers($class_id)
	{
		$ids=$this->input->post("teachers-ids");

		$this->class_manager_model->set_class_teachers($class_id,$ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_admin_class_details_link($class_id)."#teachers");
	}
}