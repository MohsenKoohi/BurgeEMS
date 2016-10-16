<div class="main">
	<div class="container reward">
		<h1>{rewards_list_text}</h1>			
		<div class="row">
			<div class="four columns">
				<select class="main-select full-width" onchange="document.location=$(this).val()" style="font-weight:bold" >
					<?php foreach($teacher_classes as $cl) { ?>
						<option 
							<?php if($cl==$class_id) echo 'selected';?>
							value="<?php echo get_customer_reward_teacher_list_class_link($cl,0);?>"
						>
							<?php echo $classes_names[$cl];?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<br><br>
		<?php foreach($rewards_list as $reward) { ?>
			<a target="_blank"
				href="<?php echo get_customer_reward_teacher_list_class_link($class_id,$reward['reward_id']);?>">
				<div class="row even-odd-bg">
					<div class="six columns">
						<b><?php echo $reward['reward_subject'];?></b>
					</div>
					<div class="six columns">
						<span class="date"><?php echo $reward['reward_date'];?></span>
					</div>

				</div>
			</a>
		<?php } ?>
		
	</div>
</div>