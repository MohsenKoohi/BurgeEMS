<?php
class Message_manager_model extends CI_Model
{	
	private $message_table_name 		 = "message";
	private $message_group_member_table_name = "message_group_member";
	private $additional_groups=array(
		1=>"parents_community"
	);

	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->message_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mi_message_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL
				,`mi_sender_type` ENUM ('student','parent','teacher','group')
				,`mi_sender_id` BIGINT UNSIGNED
				,`mi_receiver_type` ENUM ('student','parent','teacher','group')
				,`mi_receiver_id` BIGINT UNSIGNED
				,`mi_last_activity` CHAR(20)
				,`mi_subject` VARCHAR(255)
				,`mi_content` TEXT
				,PRIMARY KEY (`mi_message_id`)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_group_member_table_name);
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mgm_group_id` BIGINT 
				,`mgm_customer_id` BIGINT 
				,PRIMARY KEY (`mgm_group_id`,`mgm_customer_id`)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->module_manager_model->add_module("message","message_manager");
		$this->module_manager_model->add_module_names_from_lang_file("message");

		return;
	}

	public function uninstall()
	{

		return;
	}

	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_message',$lang);		
		
		$data=array();
		$res=$this->get_dashboard_totals();
		$data['total']=$res['total'];
		$data['total_text']=$CI->lang->line("total");
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("message_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	private function get_dashboard_totals()
	{
		$ret=array();

		$ret['total']=$this->db
			->select("COUNT(*) as count ")
			->from($this->message_table_name)
			->get()
			->row_array()['count'];

		return $ret;
	}
	
	public function get_sidebar_text()
	{
		//return " (12) ";
	}

	public function get_additional_groups()
	{
		return $this->additional_groups;
	}

	public function get_group_members($group_id)
	{
		return $this->db
			->select("customer_id,customer_name")
			->from($this->message_group_member_table_name)
			->join("customer","customer_id= mgm_customer_id","LEFT")
			->where("mgm_group_id",$group_id)
			->order_by("customer_order ASC")
			->get()
			->result_array();
	}

	public function set_group_members($group_id,$members)
	{
		$member_ids=explode(",", $members);
		$this->db
			->from($this->message_group_member_table_name)
			->where("mgm_group_id",$group_id)
			->delete();

		$ins=array();
		foreach($member_ids as $cid)
			$ins[]=array(
				"mgm_group_id"=>$group_id
				,"mgm_customer_id"=>$cid
			);

		$this->db->insert_batch($this->message_group_member_table_name,$ins);

		$log=array("members"=>$members,"group_id"=>$group_id);
		$this->log_manager_model->info("MESSAGE_GROUP_SET",$log);

		return TRUE;
	}


	private function add_message($props)
	{
		$props['mi_last_activity']=get_current_time();

		$this->db->insert($this->message_info_table_name,$props);
		$id=$this->db->insert_id();
		
		$props['mi_message_id']=$id;
		$this->log_manager_model->info("MESSAGE_ADD",$props);

		return $id;
	}

}