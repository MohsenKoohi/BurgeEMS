<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Class extends Burge_CMF_Controller {
	protected $hit_level=3;

	function __construct()
	{
		parent::__construct();
		$this->load->model("class_manager_model");
		$this->lang->load('ce_class',$this->selected_lang);		

	}

	public function students($class_id,$class_name="")
	{
		$class_id=(int)$class_id;
		if(!$class_id)
		{

			foreach($this->class_manager_model->get_all_classes() as $class)
				break;

			return redirect(get_customer_class_students_link($class['class_id']));
		}

		$info=$this->class_manager_model->get_class($class_id);
		if(!$info)
			return redirect(get_link("home_url"));		

		$page_link=get_customer_class_students_link($class_id,$info['class_name']);
		if($info['class_name'] && $class_name)
			if(get_customer_class_students_link($class_id,urldecode($class_name)) !== $page_link)
				redirect($page_link,"location",301);

		$this->data['class_name']=$info['class_name'];
		$this->data['students']=$this->class_manager_model->get_students($class_id);
		$this->data['classes']=$this->class_manager_model->get_all_classes();
		$this->data['class_id']=$class_id;
		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_customer_class_students_link($class_id,"",TRUE));

		$this->data['header_title']=$this->lang->line("students_of").$info['class_name'].$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->lang->line("students_of").$info['class_name'];
		$this->data['header_meta_keywords'].=",".$this->lang->line("students_of").$info['class_name'];

		$this->data['header_canonical_url']=$page_link;

		$this->send_customer_output("class_students");
	}

	public function curriculum($class_id,$class_name="")
	{
		$class_id=(int)$class_id;
		if(!$class_id)
		{

			foreach($this->class_manager_model->get_all_classes() as $class)
				break;

			return redirect(get_customer_class_curriculum_link($class['class_id']));
		}

		$info=$this->class_manager_model->get_class($class_id);
		if(!$info)
			return redirect(get_link("home_url"));		

		$page_link=get_customer_class_curriculum_link($class_id,$info['class_name']);
		if($info['class_name'] && $class_name)
			if(get_customer_class_curriculum_link($class_id,urldecode($class_name)) !== $page_link)
				redirect($page_link,"location",301);

		$this->data['class_name']=$info['class_name'];
		$this->data['curriculum_hours']=$this->class_manager_model->get_curriculum_hours();
		$this->data['curriculum']=$this->class_manager_model->get_class_curriculum($class_id);
		$this->data['classes']=$this->class_manager_model->get_all_classes();
		$this->data['class_id']=$class_id;
		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_customer_class_curriculum_link($class_id,"",TRUE));

		$this->data['header_title']=$this->lang->line("curriculum_of").$info['class_name'].$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->lang->line("curriculum_of").$info['class_name'];
		$this->data['header_meta_keywords'].=",".$this->lang->line("curriculum_of").$info['class_name'];

		$this->data['header_canonical_url']=$page_link;

		$this->send_customer_output("class_curriculum");
	}

	public function teachers()
	{
		$this->data['teachers']=$this->class_manager_model->get_teachers(0);
		
		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_class_teachers",TRUE));

		$this->data['header_title']=$this->lang->line("teachers").$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->lang->line("teachers");
		$this->data['header_meta_keywords'].=",".$this->lang->line("teachers");

		$this->data['header_canonical_url']=get_link("customer_class_teachers");

		$this->send_customer_output("class_teachers");
	}

	
}