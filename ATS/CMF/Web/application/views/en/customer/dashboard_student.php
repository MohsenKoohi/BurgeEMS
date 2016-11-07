<div class="main">
	<div class="container dashboard">
		<h4>{customer_name}</h4>
		<ul class="dash-ul">
			<li><a >{class_posts_text}</a>
				<ul class="dash-ul">
					<li>
						<a href="<?php echo get_link('customer_class_post_assignment');?>">
							{lesson_assignments_text}
						</a>
					</li>

					<li>
						<a href="<?php echo get_link('customer_class_post_discussion');?>">
							{lesson_discussions_text}
						</a>
					</li>
				</ul>
			</li>

			<li><a href="<?php echo get_link('customer_reward_student');?>">{rewards_text}</li>
			
			<li>
				<a>{messages_text}</a>
				<ul class='dash-ul'>
					<li ><a href="<?php echo get_link('customer_message');?>">{message_inbox_text}</a></li>
					<li><a href="<?php echo get_link('customer_message_send');?>">{send_message_text}</a></li>
				</ul>
			</li>

			<li><a href="<?php echo get_customer_class_curriculum_link($class_info['class_id'],$class_info['class_name']);?>">{curriculum_text}</li>
			<li><a href="<?php echo get_customer_question_collection_list_link($class_info['class_grade_id'],0);?>">{questions_collection_text}</li>
			
			<li><a href="<?php echo get_link('customer_logout');?>">{logout_text}</a></li>
		</ul>
	</div>
</div>
