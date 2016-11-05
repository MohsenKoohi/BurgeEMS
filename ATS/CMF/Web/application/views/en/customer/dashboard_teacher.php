<div class="main">
	<div class="container dashboard">
		<h4>{customer_name}</h4>
		<ul class="dash-ul">
			<?php if($classes){ ?>
				<li><a >{class_posts_text}</a>
					<ul class="dash-ul">
						<li>
							<a href="<?php echo get_link('customer_class_post_assingment');?>">
								{lesson_assignments_text}
							</a>
						</li>

						<li>
							<a href="<?php echo get_link('customer_class_post_conversation');?>">
								{lesson_conversations_text}
							</a>
						</li>
					</ul>
				</li>
			<?php } ?>
			

			<?php if($classes){ ?>
				<li><a >{rewards_text}</a>
					<ul class="dash-ul">
						<li>
							<a href="<?php echo get_customer_reward_teacher_submit_class_link(0);?>">
								{submit_rewards_text}
							</a>
						</li>
						<li>
							<a href="<?php echo get_customer_reward_teacher_list_class_link(0);?>">
								{rewards_list_text}
							</a>
						</li>
						<?php if($prize_teacher) { ?>
							<li>
								<a href="<?php echo get_customer_reward_teacher_prize_class_link(0);?>">
									{submit_prize_text}
								</a>
							</li>
						<?php } ?>
					</ul>
				</li>
			<?php } ?>
			
			<li>
				<a>{questions_collection_text}</a>
				<ul class="dash-ul">
					<li>
						<a href="<?php echo get_link('customer_question_collection_teacher_submit');?>">
							{submit_questions_collection_text}
						</a>
					</li>
					<li>
						<a href="<?php echo get_link('customer_question_collection_teacher_list');?>">
							{questions_collection_list_text}
						</a>
					</li>
				</ul>
			</li>

			<li>
				<a>{messages_text}</a>
				<ul class='dash-ul'>
					<li ><a href="<?php echo get_link('customer_message');?>">{message_inbox_text}</a></li>
					<li><a href="<?php echo get_link('customer_message_send');?>">{send_message_text}</a></li>
				</ul>
			</li>

			<li>
				<a href="<?php echo get_link('customer_logout');?>">
					{logout_text}
				</a>
			</li>
		</ul>
	</div>
</div>
