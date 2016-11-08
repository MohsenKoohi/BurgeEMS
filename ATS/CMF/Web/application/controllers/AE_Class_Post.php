<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Class_Post extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_class_post',$this->selected_lang);
		$this->load->model(array(
			"class_post_manager_model"
			,"class_manager_model"
			,"customer_manager_model"
		));
	}

	public function details($class_post_id)
	{
		$class_post_id=(int)$class_post_id;
		if( !$class_post_id )
			return redirect(get_link("admin_class_post"));

		$this->data['class_post_id']=$class_post_id;
		$filters=array(
			'lang'					=> $this->selected_lang
		);

		$cp_info=$this->class_post_manager_model->get_class_post($class_post_id,$filters);
		if(!$cp_info)
			return redirect(get_link('admin_class_post'));

		$cp_info=$cp_info[0];
		$this->data['cp_info']=$cp_info;

		$comments_filters=array();
		if($cp_info['cp_assignment'])
		{
			$comments_filters['order_by']="customer_order ASC, cpc_id ASC";
			$class_post_type_name=$this->lang->line("assignment");
		}
		else
		{
			$comments_filters['order_by']="cpc_id ASC";
			$class_post_type_name=$this->lang->line("discussion");
		}

		$this->data['comments']=$this->class_post_manager_model->get_comments($class_post_id,$comments_filters);
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_class_post_details_link($class_post_id,TRUE));
		
		$title=$cp_info['cpt_title'];
		$this->data['header_title']=$class_post_type_name." ".$title;
		$this->data['page_title']=$class_post_type_name;
		if($title)
			$this->data['page_title'].=$this->lang->line("comma").$title;	

		$this->send_admin_output("class_post_details");

		return;
	}

	
	public function index()
	{
		$this->set_class_posts_info();
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_class_post",TRUE));
		$this->data['header_title']=$this->lang->line("class_posts");
		$this->data['raw_page_url']=get_link("admin_class_post");
		$this->data['header']=$this->lang->line("class_posts");
		$this->send_admin_output("class_post_list");

		return;	 
	}

	private function set_class_posts_info()
	{
		$filters=array(
			'lang'				=>	$this->selected_lang
		);

		$this->initialize_filters($filters);

		$total=$this->class_post_manager_model->get_class_posts_total($filters);
		if($total)
		{
			$per_page=20;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$total_pages=ceil($total/$per_page);
			if($page>$total_pages)
				$page=$total_pages;

			$start=($page-1)*$per_page;

			$filters['start']=$start;
			$filters['count']=$per_page;
			
			$this->data['class_posts_info']=$this->class_post_manager_model->get_class_posts($filters);
			
			$end=$start+sizeof($this->data['class_posts_info'])-1;

			$this->data['posts_current_page']=$page;
			$this->data['posts_total_pages']=$total_pages;
			$this->data['posts_total']=$total;
			$this->data['posts_start']=$start+1;
			$this->data['posts_end']=$end+1;		
		}
		else
		{
			$this->data['posts_current_page']=0;
			$this->data['posts_total_pages']=0;
			$this->data['posts_total']=$total;
			$this->data['posts_start']=0;
			$this->data['posts_end']=0;
		}

		unset(
			$filters['start']
			,$filters['count']
		);

		$this->data['filter']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		if($this->input->get("title"))
			$filters['title']=$this->input->get("title");

		if($this->input->get("post_date_le"))
		{	
			$le=$this->input->get("post_date_le");
			if(sizeof(explode(" ",$le))==1)
				$le.=" 23:59:59";

			$filters['post_date_le']=$le;
		}

		if($this->input->get("post_date_ge"))
		{
			$ge=$this->input->get("post_date_ge");
			if(sizeof(explode(" ",$ge))==1)
				$ge.=" 00:00:00";

			$filters['post_date_ge']=$ge;
		}

		if($this->input->get("category_id")!==NULL)
			$filters['category_id']=(int)$this->input->get("category_id");

		persian_normalize($filters);

		return;
	}
}