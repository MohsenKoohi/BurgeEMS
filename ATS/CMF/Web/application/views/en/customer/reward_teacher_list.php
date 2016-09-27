<div class="main">
	<div class="container reward">
		<h1>{rewards_list_text}</h1>			
		<div class="row">
			<div class="four columns">
				<select class="main-select full-width" onchange="document.location=$(this).val()" >
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
		
	</div>
</div>