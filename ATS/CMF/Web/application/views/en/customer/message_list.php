<div class="main">
	<div class="container">
		<style type="text/css">
			a
			{
				color:black;
			}

			.even-odd-bg div.message-content
			{
				text-overflow: ellipsis;
				overflow:hidden;
				max-height: 110px;
			}

			.view-img
			{
				max-width:60px;
				transition: max-width .5s;
				text-align: left;
			}

			.view-img:hover
			{
				max-width:70px;
				transition: max-width .5s;
			}
		</style>
		<h1>{messages_text}</h1>

		<div class="container">	
			<div class="row results-count" >
				<div class="six columns">
					<label>
						{results_text} {messages_start} {to_text} {messages_end} - {total_results_text}: {messages_total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$messages_total_pages;$i++)
							{
								$sel="";
								if($i == $messages_current_page)
									$sel="selected";

								echo "<option value='$i' $sel>$page_text $i</option>";
							}
						?>
					</select>
				</div>
				<script type="text/javascript">
					function pageChanged(pageNumber)
					{
						document.location="{page_link}?page="+pageNumber;
					}
				</script>
			</div>	

			<?php 
				$i=$messages_start;
				if($messages_total)
					foreach($messages as $mess)
					{ 
						$mess_link=get_customer_message_details_link($mess['message_id']);
			?>
						<div class="row even-odd-bg">
							<div class="one column counter">
								#<?php echo $i++;?>
							</div>
							<div class="four columns">
								<label>{subject_text}</label>
								<span>
									<?php echo $mess['message_subject'];?>
								</span>
								<div class='ltr'>
									<?php echo str_replace("-","/",$mess['message_date']); ?>
								</div>
							</div>

							<div class="three columns">
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
									echo "<span>".$sender."</span>";
								?>
							</div>
							<div class="three columns">
								<label>{receiver_to_text}:</label>
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
									echo "<span>".$receiver."</span>";
								?>
							</div>
							
							
							<div class="one column">
								<a target="_blank" href="<?php echo $mess_link;?>">
									<img src="{images_url}/details.png" class="view-img anti-float" title="{view_details_text}";/>
								</a>
							
							</div>
						</div>
			<?php 
					}
			?>
		</div>
	</div>
</div>