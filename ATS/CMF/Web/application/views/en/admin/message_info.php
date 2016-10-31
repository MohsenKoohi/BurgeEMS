<div class="main">
	<div class="container">
		<style type="text/css">
			.even-odd-bg
			{
				margin-bottom: 20px;
			}
			div.two.columns
			{
				font-weight: bold;
			}
			.even-odd-bg.row > div
			{
				
				border:1px solid #ddd;
				border-radius: 10px;
				overflow: auto;
				min-height: 100px;
			}

			.even-odd-bg.row > div > div
			{
				padding:10px;
			}

		</style>
		<h1>
			<?php 
				echo $header;
			?>
		</h1>		
		<?php 
			if(!$messages) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{ 
		?>
			<div class="container">

				<?php foreach($messages as $info) { $mess=$info ?>
					<div class="row even-odd-bg">
						<div class='row'>
							<div class="four columns">
								<label>{sender_from_text}</label>
								<?php 
									$type=$mess['message_sender_type'];
									if($type === "group")
									{
										if($mess['message_sender_id'] > 0)
											$sender=${"group_".$mess['message_sender_id']."_name_text"};
										else
											$sender=$class_names[-$mess['message_sender_id']];
									}
									if($type === "teacher")						
										$sender=$mess['s_name']." (".$mess['s_subject'].")";
									if($type === "student" || $type === "parent")						
										$sender=$mess['s_name'];
									echo "<span>$sender</span>";
								?>
							</div>						
							<div class="four columns">
								<label>{receiver_to_text}</label>
								<?php 
									$type=$mess['message_receiver_type'];
									if($type === "group")
									{
										if($mess['message_receiver_id'] > 0)
											$receiver=${"group_".$mess['message_receiver_id']."_name_text"};
										else
											$receiver=$class_names[-$mess['message_receiver_id']];
									}
									if($type === "teacher")						
										$receiver=$mess['r_name']." (".$mess['r_subject'].")";
									if($type === "student" || $type === "parent")						
										$receiver=$mess['r_name'];
									echo "<span>$receiver</span>";
								?>
							</div>
							<div class="four columns">
								<label>{date_text}</label>
								<span class="ltr" style="display:inline-block">
									<?php echo $info['message_date'];?>
								</span>
							</div>
						</div>
						<div class="row">
							<div class="twelve columns">
								<label>{subject_text}</label>
								<span><?php echo $info['message_subject'];?></span>
							</div>
						</div>
						<div class='row'>
							<div class="twelve columns">
								<label>{content_text}</label>
							<?php
								if(preg_match("/[ابپتثجچحخدذرز]/",$info['message_content']))
									$lang="fa";
								else
									$lang="en";
							?>
							<div class="content twelve columns lang-<?php echo $lang;?>">
								<span>
									<?php echo nl2br($info['message_content']);?>
								</span>
							</div>			
							</div>
						</div>
					</div>
				<?php } ?>
			</div>

		<?php 
			}
		?>
	</div>
</div>