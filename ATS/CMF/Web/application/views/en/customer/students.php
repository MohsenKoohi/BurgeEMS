<div class="main">
	<div class="container class-students">
		<h1>{students_of_text} <?php echo $class_name;?></h1>			
		<?php foreach($students as $st) { ?>
			<div class="row even-odd-bg">		
				<div class="two columns">
					&nbsp;
					<?php if($st['customer_image_hash']) { ?>
						<img src="<?php echo get_customer_image_url($st['customer_id'],$st['customer_image_hash']);?>"/>
					<?php } ?>
				</div>
				<div class="six columns">
					<span><?php echo $st['customer_name'];?></span>
				</div>
			</div>
		<?php } ?>
	</div>
</div>