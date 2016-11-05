<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Class_Post extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ce_class_post',$this->selected_lang);
		$this->load->model(array(
			"class_post_manager_model"
			,"class_manager_model"
			,"customer_manager_model"
		));

	}

	public function assignment()
	{
		$this->customer_info=$this->customer_manager_model->get_logged_customer_info();
		if(!$this->customer_info)
			return redirect(get_link("customer_login"));

		$customer_type=$this->customer_info['customer_type'];
		if( ( "student" !== $customer_type ) && ( "teacher" !== $customer_type ) )
			return redirect(get_link("customer_dashboard"));
		
		if( "teacher" === $customer_type )
			if($this->input->post("post_type")==="add_class_post")
				return $this->add_class_post("assignment");
		
		$this->set_class_posts_info("assignment");
		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("customer_class_post_assignment");
		$this->data['lang_pages']=get_lang_pages(get_link("customer_class_post_assignment",TRUE));
		$this->data['header_title']=$this->lang->line("assignments");
		$this->data['header']=$this->lang->line("assignments");

		$this->send_customer_output("class_post_list");

		return;	 
	}	

	private function set_class_posts_info($class_post_type)
	{
		$filters=array(
			'class_post_type'=>$class_post_type
		);

		$this->initialize_filters($filters);

		$total=$this->class_post_manager_model->get_customer_class_total($filters);
		if($total)
		{
			$per_page=20;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$start=($page-1)*$per_page;

			$filters['group_by']="post_id";
			$filters['start']=$start;
			$filters['count']=$per_page;
			
			$this->data['posts_info']=$this->post_manager_model->get_customer_class_posts($filters);
			
			$end=$start+sizeof($this->data['posts_info'])-1;

			unset($filters['start']);
			unset($filters['count']);
			unset($filters['group_by']);

			$this->data['posts_current_page']=$page;
			$this->data['posts_total_pages']=ceil($total/$per_page);
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

		unset($filters['lang'],$filters['assignment'],$filters['class_post_type']);
		$customer_type=$this->customer_info['customer_type'];
		if('student' === $customer_type)
			unset($filters['class_id']);
		if('teacher' === $customer_type)
			unset($filters['teacher_id'],$filters['class_id_in']);

		$this->data['filter']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		$customer_type=$this->customer_info['customer_type'];
		if('student' === $customer_type)
			$filters['class_id']=$this->customer_info['customer_class_id'];
		if('teacher' === $customer_type)
		{
			$filters['teacher_id']=$this->customer_info['customer_id'];
			$filters['class_id_in']=$this->class_manager_model->get_teacher_classes($this->customer_info['customer_id']);
		}

		if($filters['class_post_type'] === 'assignment')
			$filters['assignment']=1;
		else
			$filters['assignment']=0;

		$filters['lang']=$this->language->get();
	
		return;


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

	private function add_class_post($type)
	{
		$ins=array(
			"teacher_id"=>$this->customer_info['customer_id']
		);

		if( 'assignment' === $type )
			$ins['assignment']=1;
		else
			$ins['assignment']=0;

		$class_post_id=$this->class_post_manager_model->add_class_post($ins);

		if( 'assignment' === $type )
			return redirect(get_customer_class_post_assignment_edit_link($class_post_id));
		else
			return redirect(get_customer_class_post_discussion_edit_link($class_post_id));

	}

	public function details($post_id)
	{
		if($this->input->post("post_type")==="edit_post")
			return $this->edit_post($post_id);

		if($this->input->post("post_type")==="delete_post")
			return $this->delete_post($post_id);

		$this->data['post_id']=$post_id;
		$post_info=$this->post_manager_model->get_post($post_id);

		$this->data['langs']=$this->language->get_languages();

		$this->data['post_contents']=array();
		foreach($this->data['langs'] as $lang => $val)
			foreach($post_info as $pi)
				if($pi['pc_lang_id'] === $lang)
				{
					$this->data['post_contents'][$lang]=$pi;
					break;
				}

		if($post_info)
		{
			$this->data['post_info']=array(
				"post_date"=>str_replace("-","/",$post_info[0]['post_date'])
				,"post_allow_comment"=>$post_info[0]['post_allow_comment']
				,"post_active"=>$post_info[0]['post_active']
				,"user_name"=>$post_info[0]['user_name']
				,"user_id"=>$post_info[0]['user_id']
				,"categories"=>$post_info[0]['categories']
				,"post_title"=>$this->data['post_contents'][$this->selected_lang]['pc_title']
			);
			$this->data['customer_link']=get_customer_post_details_link($post_id,"",$post_info[0]['post_date']);
		}
		else
		{
			$this->data['post_info']=array();
			$this->data['customer_link']="";
		}
		
		$this->data['current_time']=get_current_time();
		$this->load->model("category_manager_model");
		$this->data['categories']=$this->category_manager_model->get_hierarchy("checkbox",$this->selected_lang);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_post_details_link($post_id,TRUE));
		$this->data['header_title']=$this->lang->line("post_details")." ".$post_id;

		$this->send_admin_output("post_details");

		return;
	}

	private function delete_post($post_id)
	{
		$this->post_manager_model->delete_post($post_id);

		set_message($this->lang->line('post_deleted_successfully'));

		return redirect(get_link("admin_post"));
	}

	private function edit_post($post_id)
	{
		$post_props=array();
		$post_props['categories']=$this->input->post("categories");

		$post_props['post_date']=persian_normalize($this->input->post('post_date'));
		if( DATE_FUNCTION === 'jdate')
			validate_persian_date_time($post_props['post_date']);
		
		$post_props['post_active']=(int)($this->input->post('post_active') === "on");
		$post_props['post_allow_comment']=(int)($this->input->post('post_allow_comment') === "on");
		
		$post_content_props=array();
		foreach($this->language->get_languages() as $lang=>$name)
		{
			$post_content=$this->input->post($lang);
			$post_content['pc_content']=$_POST[$lang]['pc_content'];
			$post_content['pc_lang_id']=$lang;

			if(isset($post_content['pc_active']))
				$post_content['pc_active']=(int)($post_content['pc_active']=== "on");
			else
				$post_content['pc_active']=0;

			$post_content['pc_gallery']=$this->get_post_gallery($post_id,$lang);

			$post_content_props[$lang]=$post_content;
		}

		foreach($this->language->get_languages() as $lang=>$name)
		{
			$copy_from=$this->input->post($lang."[copy]");
			if(!$copy_from)
				continue;

			$post_content_props[$lang]=$post_content_props[$copy_from];
			$post_content_props[$lang]['pc_lang_id']=$lang;
		}


		$this->post_manager_model->set_post_props($post_id,$post_props,$post_content_props);
		
		set_message($this->lang->line("changes_saved_successfully"));

		redirect(get_admin_post_details_link($post_id));

		return;
	}

	private function get_post_gallery($post_id, $lang)
	{
		$pp=$this->input->post($lang);
		$pp=$pp['pc_gallery'];
		//bprint_r($pp);

		$gallery=array();
		$gallery['last_index']=0;
		$gallery['images']=array();

		$last_index=&$gallery['last_index'];

		if(isset($pp['old_images']))
			foreach($pp['old_images'] as $index)
			{
				$img=$pp['old_image_image'][$index];
				$delete=isset($pp['old_image_delete'][$index]);
				if($delete)
				{
					unlink(get_post_gallery_image_path($img));
					continue;
				}

				$text=$pp['old_image_text'][$index];
				$gallery['images'][$index]=array(
					"image"	=> $img
					,"text"	=> $text
				);

				$last_index=max(1+$index,$last_index);
			}
		
		if(isset($pp['new_images']))
			foreach($pp['new_images'] as $index)
			{
				$file_names=$_FILES[$lang]['name']['pc_gallery']['new_image'][$index];
				$file_tmp_names=$_FILES[$lang]['tmp_name']['pc_gallery']['new_image'][$index];
				$file_errors=$_FILES[$lang]['error']['pc_gallery']['new_image'][$index];
				$file_sizes=$_FILES[$lang]['size']['pc_gallery']['new_image'][$index];
				$text=$pp['new_text'][$index];
				$watermark=isset($pp['new_image_watermark'][$index]);

				foreach($file_names as $findex => $file_name)
				{
					if($file_errors[$findex])
						continue;

					$extension=pathinfo($file_names[$findex], PATHINFO_EXTENSION);

					if($watermark)
						burge_cmf_watermark($file_tmp_names[$findex]);

					$img_name=$post_id."_".$lang."_".$last_index."_".get_random_word(5).".".$extension;
					$file_dest=get_post_gallery_image_path($img_name);
					move_uploaded_file($file_tmp_names[$findex], $file_dest);

					$gallery['images'][$last_index++]=array(
						"image"	=> $img_name
						,"text"	=> $text
						);
					//echo "***<br>".$file_name."<br>".$file_sizes[$findex]."<br>".$text."<br>watermark:".$watermark."<br>###<br>";
				}			
			}
		
		//bprint_r($gallery);

		//we need in some positions to check if pc_gallery is null
		if(!sizeof($gallery['images']))
			return NULL;

		return $gallery;
	}
}