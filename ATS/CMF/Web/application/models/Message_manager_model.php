<?php

/*

We have two types of groups:
1)Class groups: students of a class are memebers of their class group, with negative id of class
2)Additional groups: which their members can be set throught the admin page

We have different types of message senders:
1)Student: can send message to its teachers and additional groups
2)Teachers: can send message to its students, and classes
3)Additional groups: can send message to students, and classes 
4)Students Class: to receive a message by students of a class
5)Parents Class: to receive a message by students' parents of a class

*/

class Message_manager_model extends CI_Model
{	
	private $message_table_name = "message";
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
				`message_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL
				,`message_sender_type` ENUM ('student','parent','teacher','group')
				,`message_sender_id` BIGINT UNSIGNED
				,`message_receiver_type` ENUM ('student','student_class','parent','parent_class','teacher','group')
				,`message_receiver_id` BIGINT 
				,`message_date` CHAR(20)
				,`message_subject` VARCHAR(255)
				,`message_content` TEXT
				,PRIMARY KEY (`message_id`)	
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

	public function get_customer_groups($customer_id)
	{
		$result=$this->db
			->select("*")
			->from($this->message_group_member_table_name)
			->where("mgm_customer_id",$customer_id)
			->get()
			->result_array();

		$ret=array();
		foreach($result as $row)
			$ret[]=$row['mgm_group_id'];

		return $ret;
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


	public function add_message($in_props)
	{
		$props=array();

		$props['message_date']=get_current_time();

		$props['message_sender_type']=$in_props['sender_type'];
		$props['message_sender_id']=$in_props['sender_id'];
		$props['message_receiver_type']=$in_props['receiver_type'];
		$props['message_receiver_id']=$in_props['receiver_id'];
		$props['message_content']=$in_props['content'];
		$props['message_subject']=$in_props['subject'];

		$this->db->insert($this->message_table_name,$props);
		$id=$this->db->insert_id();
		
		$props['message_id']=$id;
		$this->log_manager_model->info("MESSAGE_ADD",$props);

		return $id;
	}

	public function get_customer_total_messages($filter)
	{
		$subquery=$this->db
			->select("max(message_id) as max_message_id")
			->from($this->message_table_name)
			->group_by("message_sender_type, message_sender_id, message_receiver_type, message_receiver_id")
			->get_compiled_select();

		$this->db
			->select("*")
			->from($this->message_table_name)
			->join("($subquery) sub","message_id = max_message_id","INNER");

		$this->set_customer_search_where_clause($filter);

		$subquery=$this->db->get_compiled_select();

		$row=$this->db
			->query("SELECT COUNT(*) as count from ($subquery) sub")
			->row_array();

		return $row['count'];
	}

	public function get_customer_message($filter)
	{
		$ft=$filter['part1_type'];
		$fi=$filter['part1_id'];
		$st=$filter['part2_type'];
		$si=$filter['part2_id'];
		$where="
			(
				message_sender_type = '$ft' && message_sender_id = $fi 
				&& message_receiver_type = '$st' && message_receiver_id = '$si' 
			)
			|| ( 
				message_sender_type = '$st' && message_sender_id = $si 
				&& message_receiver_type = '$ft' && message_receiver_id = '$fi' 
			)
		";

		$this->db
			->select("m.*")
			->select("s.customer_name as s_name , s.customer_subject as s_subject")
			->select("r.customer_name as r_name , r.customer_subject as r_subject")
			->from($this->message_table_name." m")
			->join("customer s","message_sender_id = s.customer_id","LEFT")
			->join("customer r","message_receiver_id = r.customer_id","LEFT")
			->order_by("message_date ASC")
			->where("( $where )");
		
		return $this->db
			->get()
			->result_array();
	}

	public function get_customer_messages($filter)
	{
		$subquery=$this->db
			->select("max(message_id) as max_message_id,count(*) as count")
			->from($this->message_table_name)
			->group_by("
				(if(
				CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				<
				CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
			))

				")
			->get_compiled_select();

		$this->db
			->select("m.*, sub.count")
			->select("s.customer_name as s_name , s.customer_subject as s_subject")
			->select("r.customer_name as r_name , r.customer_subject as r_subject")
			->from($this->message_table_name." m")
			->join("($subquery) sub","message_id = max_message_id","INNER")
			->join("customer s","message_sender_id = s.customer_id","LEFT")
			->join("customer r","message_receiver_id = r.customer_id","LEFT");

		$this->set_customer_search_where_clause($filter);

		return $this->db
			->order_by("message_date DESC")
			->get()
			->result_array();
	}

	private function set_customer_search_where_clause($filter)
	{
		$customer_type=$filter['customer_type'];
		$customer_id=$filter['customer_id'];
		$class_id=$filter['class_id'];

		if('student'===$customer_type)
			$where="
				   ( message_sender_type = 'student' && message_sender_id = $customer_id )
				|| ( message_receiver_type = 'student' && message_receiver_id = $customer_id )
				|| ( message_receiver_type = 'group' && message_receiver_id = -$class_id )
			";

		if('teacher'===$customer_type)
			$where="
				   ( message_sender_type = 'teacher' && message_sender_id = $customer_id )
				|| ( message_receiver_type = 'teacher' && message_receiver_id = $customer_id )
			";

		if('parent'===$customer_type)
		{
			$groups="(".implode(",",$filter['customer_groups']).")";

			$where="
				   ( message_sender_type = 'group' && message_sender_id IN $groups )
				|| ( message_receiver_type = 'group' && message_receiver_id IN $groups )
			";
		}

		$this->db->where(" ( $where ) ");

		$this->db->group_by("
			(if(
				CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				<
				CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				,CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
			))
		 ");

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);
	}

	public function get_admin_total_messages($filter)
	{
		$subquery=$this->db
			->select("max(message_id) as max_message_id")
			->from($this->message_table_name)
			->group_by("message_sender_type, message_sender_id, message_receiver_type, message_receiver_id")
			->get_compiled_select();

		$this->db
			->select("*")
			->from($this->message_table_name)
			->join("($subquery) sub","message_id = max_message_id","INNER");

		$this->set_admin_search_where_clause($filter);

		$subquery=$this->db->get_compiled_select();

		$row=$this->db
			->query("SELECT COUNT(*) as count from ($subquery) sub")
			->row_array();

		return $row['count'];
	}

	public function get_admin_message($filter)
	{
		$ft=$filter['part1_type'];
		$fi=$filter['part1_id'];
		$st=$filter['part2_type'];
		$si=$filter['part2_id'];
		$where="
			(
				message_sender_type = '$ft' && message_sender_id = $fi 
				&& message_receiver_type = '$st' && message_receiver_id = '$si' 
			)
			|| ( 
				message_sender_type = '$st' && message_sender_id = $si 
				&& message_receiver_type = '$ft' && message_receiver_id = '$fi' 
			)
		";

		$this->db
			->select("m.*")
			->select("s.customer_name as s_name , s.customer_subject as s_subject")
			->select("r.customer_name as r_name , r.customer_subject as r_subject")
			->from($this->message_table_name." m")
			->join("customer s","message_sender_id = s.customer_id","LEFT")
			->join("customer r","message_receiver_id = r.customer_id","LEFT")
			->order_by("message_date ASC")
			->where("( $where )");
		
		return $this->db
			->get()
			->result_array();
	}

	public function get_admin_messages($filter)
	{
		$subquery=$this->db
			->select("max(message_id) as max_message_id,count(*) as count")
			->from($this->message_table_name)
			->group_by("
				(if(
				CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				<
				CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
			))

				")
			->get_compiled_select();

		$this->db
			->select("m.*, sub.count")
			->select("s.customer_name as s_name , s.customer_subject as s_subject")
			->select("r.customer_name as r_name , r.customer_subject as r_subject")
			->from($this->message_table_name." m")
			->join("($subquery) sub","message_id = max_message_id","INNER")
			->join("customer s","message_sender_id = s.customer_id","LEFT")
			->join("customer r","message_receiver_id = r.customer_id","LEFT");

		$this->set_admin_search_where_clause($filter);

		return $this->db
			->order_by("message_date DESC")
			->get()
			->result_array();
	}

	private function set_admin_search_where_clause($filter)
	{
		$this->db->group_by("
			(if(
				CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				<
				CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
				,CONCAT(message_receiver_type, message_receiver_id,message_sender_type, message_sender_id)
				,CONCAT(message_sender_type, message_sender_id,message_receiver_type, message_receiver_id)
			))
		 ");

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);
	}

}