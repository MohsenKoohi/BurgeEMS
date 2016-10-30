<div class="main">
	<div class="container">
		<style type="text/css">
			.even-odd-bg .even-odd-bg
			{
				margin-bottom:-8px;
			}

			.even-odd-bg.row div.content
			{
				padding:10px;
				border:1px solid #ddd;
				border-radius: 10px;
				overflow: auto;
				min-height: 50px;
			}
		</style>
		<h1>
			<?php 
				if($info) 
					echo $info['message_subject'];
			?>
		</h1>		
		<?php 
			if(!$info) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{  $mess=$info;
		?>
			<div class="container">
				<div>
					<div class="row even-odd-bg ">
						<div class="two columns">
							{sender_from_text}:
						</div>
						<div class="ten columns">
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
								echo $sender;
							?>
						</div>
					</div>

					<div class="row even-odd-bg ">
						<div class="two columns">
							{receiver_to_text}:
						</div>
						<div class="ten columns">
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
								echo $receiver;
							?>
						</div>
					</div>

					<div class="row even-odd-bg ">
						<div class="two columns">
							{subject_text}:
						</div>
						<div class="ten columns">
							<?php echo $info['message_subject'];?>
						</div>
					</div>

					<div class="row even-odd-bg ">
						<div class="two columns">
							{date_text}:
						</div>
						<div class="three columns">
							<span class="ltr" style="display:inline-block">
								<?php echo $info['message_date'];?>
							</span>
						</div>
					</div>
				</div>
				<div></div>
				<div class="row even-odd-bg ">
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