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

		$this->customer_info=$this->customer_manager_model->get_logged_customer_info();
		if(!$this->customer_info)
			return redirect(get_link("customer_login"));

		$customer_type=$this->customer_info['customer_type'];
		if( ( "student" !== $customer_type ) && ( "teacher" !== $customer_type ) )
			return redirect(get_link("customer_dashboard"));
	}

	public function assignment_edit($class_post_id)
	{
		$customer_type=$this->customer_info['customer_type'];
		$class_post_id=(int)$class_post_id;
		if( ( "teacher" !== $customer_type ) || !$class_post_id )
			return redirect(get_link("customer_class_post_assignment"));

		if($this->input->post("post_type")==="edit_class_post")
			return $this->edit_class_post($class_post_id);

		if($this->input->post("post_type")==="delete_class_post")
			return $this->delete_class_post($class_post_id);

		$this->data['class_post_id']=$class_post_id;
		$filters=array(
			'class_post_type'=>"assignment"
		);
		$this->initialize_filters($filters);

		$cp_info=$this->class_post_manager_model->get_class_post($class_post_id,$filters);
		if(!$cp_info)
			return redirect(get_link('customer_class_post_assignment'));
		//bprint_r($cp_info);exit();

		$this->data['langs']=$this->language->get_languages();

		$this->data['cp_texts']=array();
		foreach($this->data['langs'] as $lang => $val)
			foreach($cp_info as $pi)
				if($pi['cpt_lang_id'] === $lang)
				{
					$this->data['cp_texts'][$lang]=$pi;
					break;
				}
		//bprint_r($this->data['cp_texts']);exit();
		$this->data['cp_info']=array(
			"start_date"			=> str_replace("-","/",$cp_info[0]['cp_start_date'])
			,"end_date"				=> str_replace("-","/",$cp_info[0]['cp_end_date'])
			,"academic_year"		=> $cp_info[0]['academic_time']
			,"class_id"				=> $cp_info[0]['cp_class_id']
			,"teacher_name"		=> $cp_info[0]['teacher_name']
			,"teacher_subject"	=> $cp_info[0]['teacher_subject']
			,"active"				=> $cp_info[0]['cp_active']
			,"assignment"			=> $cp_info[0]['cp_assignment']
			,"allow_comment"		=> $cp_info[0]['cp_allow_comment']
			,"allow_file"			=> $cp_info[0]['cp_allow_file']			
		);

		$this->data['current_time']=get_current_time();
		$this->data['teacher_classes']=$this->class_manager_model->get_teacher_classes_with_names($this->customer_info['customer_id']);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_class_post_assignment_view_link($class_post_id,TRUE));
		$title=$this->data['cp_texts'][$this->selected_lang]['cpt_title'];
		$this->data['header_title']=$this->lang->line("assignment")." ".$title;
		$this->data['page_title']=$this->lang->line("assignment");
		$this->data['raw_page_url']=get_customer_class_post_assignment_edit_link($class_post_id);
		if($title)
			$this->data['page_title'].=$this->lang->line("comma").$title;	

		$this->send_customer_output("class_post_edit");

		return;
	}


	private function delete_post($post_id)
	{
		$this->post_manager_model->delete_post($post_id);

		set_message($this->lang->line('post_deleted_successfully'));

		return redirect(get_link("admin_post"));
	}

	private function edit_class_post($cp_id)
	{
		$props=array();
		
		$props['cp_start_date']=trim($this->input->post('start_date'));
		persian_normalize($props['cp_start_date']);
		
		$props['cp_end_date']=trim($this->input->post('end_date'));
		persian_normalize($props['cp_end_date']);

		if( DATE_FUNCTION === 'jdate')
		{
			validate_persian_date_time($props['cp_start_date']);
			
			if($props['cp_end_date'])
				validate_persian_date_time($props['cp_end_date']);
		}
			
		$props['cp_class_id']=(int)($this->input->post('class_id'));
		$props['cp_active']=(int)($this->input->post('active') === "on");
		$props['cp_allow_comment']=(int)($this->input->post('allow_comment') === "on");
		$props['cp_allow_file']=(int)($this->input->post('allow_file') === "on");
		
		$text_props=array();
		foreach($this->language->get_languages() as $lang=>$name)
		{
			$post_text=$this->input->post($lang);
			$text['cpt_title']=$post_text['title'];
			$text['cpt_content']=$_POST[$lang]['content'];
			$text['cpt_lang_id']=$lang;
			$text['cpt_gallery']=$this->get_class_post_gallery($cp_id,$lang);

			$text_props[$lang]=$text;
		}

		$teacher_id=$this->customer_info['customer_id'];
		$this->class_post_manager_model->set_class_post_props($cp_id,$props,$text_props,$teacher_id);
		
		set_message($this->lang->line("changes_saved_successfully"));

		//return redirect(get_customer_class_post_assignment_view_link($class_post_id));
	}

	private function get_class_post_gallery($cp_id, $lang)
	{
		$pp=$this->input->post($lang);
		$pp=$pp['gallery'];
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
					@unlink(get_class_post_gallery_image_path($cp_id,$img));
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
				$file_names=$_FILES[$lang]['name']['gallery']['new_image'][$index];
				$file_tmp_names=$_FILES[$lang]['tmp_name']['gallery']['new_image'][$index];
				$file_errors=$_FILES[$lang]['error']['gallery']['new_image'][$index];
				$file_sizes=$_FILES[$lang]['size']['gallery']['new_image'][$index];
				$text=$pp['new_text'][$index];

				foreach($file_names as $findex => $file_name)
				{
					if($file_errors[$findex])
						continue;

					$extension=pathinfo($file_names[$findex], PATHINFO_EXTENSION);
					if(!in_array($extension,array("jpg","jpeg","JPG","JPEG")))
						continue;

					$img_name=$lang."_".$last_index."_".get_random_word(5).".".$extension;
					$file_dest=get_class_post_gallery_image_path($cp_id,$img_name);
					$this->check_resize_image($file_tmp_names[$findex]);

					move_uploaded_file($file_tmp_names[$findex], $file_dest);

					$gallery['images'][$last_index++]=array(
						"image"	=> $img_name
						,"text"	=> $text
						);
					//echo "***<br>".$file_name."<br>".$file_sizes[$findex]."<br>".$text."<br>###<br>";
				}			
			}
		
		//bprint_r($gallery);

		//we need in some positions to check if pc_gallery is null
		if(!sizeof($gallery['images']))
			return NULL;

		return $gallery;
	}

	private function check_resize_image($img)
	{
		$this->load->library("image_lib");

		list($w,$h)=getimagesize($img);
		
		if(($w>1600) || ($h>1600))
		{
			$config=array();
			$config['source_image'] = $img;
			$config['maintain_ratio'] = TRUE;
		
			$config['height'] = 1600;	
			$config['width'] = 1600;

			$this->image_lib->clear();			
			$this->image_lib->initialize($config);
			$this->image_lib->resize();

			echo $img;
		}

		return;
	}

	public function assignment()
	{
		$customer_type=$this->customer_info['customer_type'];
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

		$total=$this->class_post_manager_model->get_class_posts_total($filters);
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
			
			$this->data['posts_info']=$this->post_manager_model->get_class_posts($filters);
			
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

		unset($filters['assignment'],$filters['class_post_type']);
		$customer_type=$this->customer_info['customer_type'];
		if('student' === $customer_type)
			unset($filters['class_id'],$filters['teacher_id_in'],$filters['lang'],$filters['active']);
		if('teacher' === $customer_type)
			unset($filters['teacher_id']);

		$this->data['filter']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		$customer_type=$this->customer_info['customer_type'];
		if('student' === $customer_type)
		{
			$filters['class_id']=$this->customer_info['customer_class_id'];
			////////////
			$filters['acadmic_time']=1;
			$filters['teacher_id_in']=array();
			////////////
			$filters['lang']=$this->language->get();
			$filters['active']=1;
		}

		if('teacher' === $customer_type)
			$filters['teacher_id']=$this->customer_info['customer_id'];
			
		if('assignment' === $filters['class_post_type'])
			$filters['assignment']=1;
		else
			$filters['assignment']=0;

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
}