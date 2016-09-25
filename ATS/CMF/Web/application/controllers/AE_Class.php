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

		$this->data['classes']=$this->class_manager_model->get_all_classes();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_class");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_class",TRUE));
		$this->data['header_title']=$this->lang->line("classes");

		$this->send_admin_output("class");

		return;	 
	}

	private function class_changes()
	{
		$ids=$this->input->post("class-ids");
		$ids_exp=explode(",", $ids);

		$new_props=array();
		foreach($ids_exp as $id)
			$new_props[]=array("class_id"=>$id,"class_name"=>$this->input->post("class-".$id));
		if($new_props)
			$this->class_manager_model->set_props($new_props);

		if($ids)
			$this->class_manager_model->resort_classes($ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_link("admin_class"));
	}

	private function add_class()
	{
		$name=$this->input->post("name");

		$this->class_manager_model->add($name);

		set_message($this->lang->line("new_class_added_successfully"));

		return redirect(get_link("admin_class"));
	}

	public function details($class_id)
	{
		$class_id=(int)$class_id;

		if($this->input->post("post_type")==="students_resort")
			return $this->students_resort($class_id);

		if($this->input->post("post_type")==="set_teachers")
			return $this->set_teachers($class_id);		

		$info=$this->class_manager_model->get_class($class_id);
		if(!$info)
		{
			set_message($this->lang->line("class_not_found"));
			return redirect(get_link("admin_class"));		
		}

		$this->data['info']=$info;
		$this->data['teachers']=$this->class_manager_model->get_teachers($class_id);
		$this->data['students']=$this->class_manager_model->get_students($class_id);

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_admin_class_details_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_admin_class_details_link($class_id,TRUE));
		$this->data['header_title']=$info['class_name'];

		$this->send_admin_output("class_details");
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

		return redirect(get_admin_class_details_link($class_id));
	}
}