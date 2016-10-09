<div class="main">
	<div class="container reward">
		<h1>{edit_reward_text}</h1>
		
		<br><br>
		<?php $i=0; echo form_open($page_link,array("id"=>"rewards-form")); ?>
			<input type="hidden" name="rand" value="{rand}"/>
			<input type="hidden" name="post_type"  value="edit_rewards"/>
			<div class="row even-odd-bg dont-magnify" style="margin-top:30px";>
				<div class="four columns subject">
					{reward_subject_text}
				</div>
				<div class="eight columns">
					<input 
						type="text" class="full-width" 
						id="input-<?php echo $i;?>"
						name="subject-{rand}" data-number="<?php echo $i++?>"
						value="{reward_subject}"
					/>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" style="margin-bottom:30px";>
				<div class="four columns subject">
					{reward_date_text}
				</div>
				<div class="eight columns ltr">
					{reward_date}
				</div>
			</div>

			<?php foreach($students as $st) { ?>
				<div class="row even-odd-bg dont-magnify">		
					<div class="four columns student-name">
						<?php echo $st['customer_name'];?>
					</div>
					<div class="four columns">
						<label>{reward_value_text}</label>
						<input type="text" class='ltr reward-value' 
							id="input-<?php echo $i;?>"
							data-number="<?php echo $i++?>"
							name="reward-{rand}[<?php echo $st['customer_id'] ?>]"/>
					</div>
					<div class="four columns">
						<label>{more_description_text}</label>
						<input type="text" class='full-width' 
							id="input-<?php echo $i;?>"
							data-number="<?php echo $i++?>"
							name="md-{rand}[<?php echo $st['customer_id'] ?>]"/>
					</div>
				</div>
			<?php } ?>
			<br><br>
			<div class="row">
				<div class="four columns">&nbsp;</div>
				<div class="four columns">				
					<div class="full-width button button-primary" onclick="submitRewards()">
						{submit_text}
					</div>
				</div>
			</div>
		</form>
		<script type="text/javascript">
			$(function()
			{
				<?php 
					foreach($students_rewards as $sr) 
					{ 
						$id=$sr['rv_student_id'];
						$value=$sr['rv_value'];
						$desc=$sr['rv_description'];

						echo "$('input[name=\'reward-".$rand."[$id]\']').val('$value');\n";
						echo "$('input[name=\'md-".$rand."[$id]\']').val('$desc');\n";
					}
				?>
			});
			$("#rewards-form input").keyup(function(event)
			{
				if(event.keyCode==13)
				{
					var num=$(event.target).data("number");
					var next=1+parseInt(num);
					if(next==<?php echo $i;?>)
						next=0;
					$("#input-"+next).focus();
				}
			});

			function submitRewards()
			{
				if(!$("input[name=subject-{rand}]").val().trim())
				{
					alert("{please_fill_subject_field_text}");
					return;
				}

				if(!confirm("{are_you_sure_to_submit_changes_text}"))
					return;
				
				$("#rewards-form").submit();
			}
		</script>
	</div>
</div>