<div class="main">
	<div class="container reward">
		<h1>{submit_reward_text}</h1>			
		<div class="row">
			<div class="four columns">
				<select class="main-select full-width" onchange="document.location=$(this).val()" >
					<?php foreach($teacher_classes as $cl) { ?>
						<option 
							<?php if($cl==$class_id) echo 'selected';?>
							value="<?php echo get_customer_reward_teacher_class_link($cl);?>"
						>
							<?php echo $classes_names[$cl];?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<br><br>
		<?php $i=0; echo form_open($page_link,array("id"=>"rewards-form")); ?>
			<input type="hidden" name="post_type"  value="add_rewards"/>
			<div class="row even-odd-bg" style="margin:30px 0">
				<div class="four columns">
					{reward_subject_text}
				</div>
				<div class="eight columns">
					<input 
						type="text" class="full-width" 
						id="input-<?php echo $i;?>"
						name="subject" data-number="<?php echo $i++?>"
					/>
				</div>
			</div>

			<?php foreach($students as $st) { ?>
				<div class="row even-odd-bg dont-magnify">		
					<div class="four columns">
						<?php echo $st['customer_name'];?>
					</div>
					<div class="two columns">
						<input type="text" class='ltr full-width' 
							id="input-<?php echo $i;?>"
							data-number="<?php echo $i++?>"
							name="reward-<?php echo $st['customer_id'] ?>"/>
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
			$("#rewards-form input").keyup(function(event)
			{
				if(event.keyCode==13)
				{
					console.log(1);
					var num=$(event.target).data("number");
					var next=1+parseInt(num);
					if(next==<?php echo $i;?>)
						next=0;
					$("#input-"+next).focus();
				}
			});

			function submitRewards()
			{
				if(!$("input[name=subject]").val().trim())
				{
					alert("{please_fill_subject_field_text}");
					return;
				}

				if(!confirm("{are_you_sure_to_submit_rewards_text}"))
					return;
				
				$("#rewards-form").submit();
			}
		</script>
	</div>
</div>