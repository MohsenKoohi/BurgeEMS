<div class="main">
	<div class="container message-details">
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
										$sender=${"group_".$mess['message_sender_id']."_name_text"};
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
										$receiver=${"group_".$mess['message_receiver_id']."_name_text"};
									if($type === "teacher")						
										$receiver=$mess['r_name']." (".$mess['r_subject'].")";
									if(($type === "student") || ($type === "parent"))
										$receiver=$mess['r_name'];
									if($type === "student_class")
										$receiver=$students_of_text." ".$class_names[$mess['message_receiver_id']];
									if($type === "parent_class")
										$receiver=$parents_of_text." ".$class_names[$mess['message_receiver_id']];
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
							<div class="twelve columns lang-<?php echo $lang;?>">
								<span>
									<?php echo nl2br($info['message_content']);?>
								</span>
							</div>			
							</div>
						</div>
					</div>
				<?php } ?>
			</div>

			<?php if(0) { ?>
				<div class="separated">
					<h2>{reply_text}</h2>
					<?php echo form_open(get_customer_message_details_link($message_id),array(
						"onsubmit"=>"return confirm('{are_you_sure_to_send_text}')")); ?>
					<input type="hidden" name="post_type" value="add_reply" />		
						<?php if(sizeof($all_langs)>1) { ?>	
						<div class="row response-type">
							<div class="three columns">
								<label>{language_text}</label>
								<select name="language" class="full-width" onchange="langChanged(this);">
									<?php
										foreach($all_langs as $key => $val)
										{
											$sel="";
											if($key===$selected_lang)
												$sel="selected";

											echo "<option $sel value='$key'>$val</option>";
										}
									?>
									<script type="text/javascript">
										var langSelectVal;

										function langChanged(el)
										{
											if(langSelectVal)
												$("#content-ta").toggleClass(langSelectVal);

											langSelectVal="lang-"+""+$(el).val();
											
											$("#content-ta").toggleClass(langSelectVal);
										}

										$(function()
										{
											$("select[name='language']").trigger("change");
										});
									</script>
								</select>
							</div>
						</div>	
						<br><br>
						<?php } ?>
						<div class="row">
							<div class="twelve columns">
								<textarea id="content-ta" name="content" class="full-width" rows="7">{content}</textarea>
							</div>
						</div>
						<div class="row">
							<div class="three columns">
								{captcha}
							</div>
							<div class="three columns">
								<input name="captcha" class="full-width lang-en"/>
							</div>
						</div>
						<br><br>
						<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{send_text}"/>
						</div>
					</form>
				</div>
				<br><br>
			<?php } ?>
		<?php 
			}
		?>
	</div>
</div>