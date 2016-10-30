<div class="main">
	<div class="container dashboard">
		<h4>{customer_name}</h4>
		<ul class="dash-ul">
			<?php if($groups){ ?>
				<li>
					<a>{messages_text}</a>
					<ul class='dash-ul'>
						<li ><a href="<?php echo get_link('customer_message');?>">{message_inbox_text}</a></li>
						<li><a href="<?php echo get_link('customer_message_send');?>">{send_message_text}</a></li>
					</ul>
				</li>
			<?php } ?>

			<li>
				<a href="<?php echo get_link('customer_logout');?>">
					{logout_text}
				</a>
			</li>
		</ul>
	</div>
</div>
