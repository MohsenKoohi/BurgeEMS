<div class="main">
	<div class="container class-curriculum">
		<h1>{curriculum_text}</h1>			
		<div class="row">
			<div class="four columns">
				<select class="main-select full-width" onchange="document.location=$(this).val()" >
					<?php foreach($classes as $cl) { ?>
						<option 
							<?php if($cl['class_id']==$class_id) echo 'selected';?>
							value="<?php echo get_customer_class_curriculum_link($cl['class_id'],$cl['class_name']);?>"
						>
							<?php echo $cl['class_name'];?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<br><br>
		<style type="text/css">
			.class-curriculum table td
			{
				width:<?php echo 100.0/(1+sizeof($curriculum_hours));?>%;
			}

			@media (max-width: 600px)
			{
				.class-curriculum table td
				{
					display: block;
					width:100%;
				}
			}
		</style>
		<table>
			<tr>
				<td></td>
				<?php foreach($curriculum_hours as $hour) { ?>
					<td><?php echo $hour['cc_course'];?></td>
				<?php } ?>
			</tr>
			<?php 
				foreach($curriculum as $day_index => $day)
				{ 
					$day_name=${"day_".($day_index+1)."_name_text"};
			?>
				<tr>
					<td><?php echo $day_name;?></td>
					<?php foreach($day as $hour_index => $course) { ?>
						<td>
							<span class="day-hour">
								<?php echo $curriculum_hours[$hour_index]['cc_course'];?>: 
							</span>
							<?php echo $course;?>
						</td>
					<?php } ?>
				</tr>
			<?php 
				} 
			?>
		</table>

	</div>
</div>