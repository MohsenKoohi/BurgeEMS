<div class="main">

	<div class="container dashboard">
		<h2>{customer_name}</h2>
		<ul class="dash-ul">
			<li><a href="<?php echo get_customer_class_curriculum_link($class_info['class_id'],$class_info['class_name']);?>">{curriculum_text}</li>
			<li><a href="<?php echo get_customer_question_collection_list_link($class_info['class_grade_id'],0);?>">{questions_collection_text}</li>
			<li><a href="<?php echo get_link('customer_reward_student');?>">{rewards_text}</li>
			<li><a href="<?php echo get_link('customer_message');?>">{messages_text}</li>
			<li><a href="<?php echo get_link('customer_logout');?>">{logout_text}</a></li>
		</ul>
	</div>
</div>
