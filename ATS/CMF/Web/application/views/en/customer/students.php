<div class="main">
	<div class="container class-students">
		<h1>{students_text}</h1>			
		<div class="row">
			<div class="four columns">
				<select class="main-select full-width" onchange="document.location=$(this).val()" >
					<?php foreach($classes as $cl) { ?>
						<option 
							<?php if($cl['class_id']==$class_id) echo 'selected';?>
							value="<?php echo get_customer_class_students_link($cl['class_id'],$cl['class_name']);?>"
						>
							<?php echo $cl['class_name'];?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<?php foreach($students as $st) { ?>
			<div class="row even-odd-bg">		
				<div class="tweleve columns">
					<?php if($st['customer_image_hash']) { ?>
						<img src="<?php echo get_customer_image_url($st['customer_id'],$st['customer_image_hash']);?>"/>
					<?php } ?>
					<span><?php echo $st['customer_name'];?></span>
				</div>
			</div>
		<?php } ?>
	</div>
</div>