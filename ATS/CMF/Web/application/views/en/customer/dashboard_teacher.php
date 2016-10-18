<div class="main">
	<div class="container dashboard">
		<h4>{hello_text} {customer_name}</h4>
		<ul class="dash-ul">
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
				<a href="<?php echo get_link('customer_message');?>">
					{messages_text}
				</a>
			</li>

			<li>
				<a href="<?php echo get_link('customer_logout');?>">
					{logout_text}
				</a>
			</li>
		</ul>
	</div>
</div>
