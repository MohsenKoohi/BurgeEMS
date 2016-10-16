<div class="main">
	<div class="container">
		<h1>{questions_collection_text}</h1>

		<div class="row" >
			<div class="three columns">
				<h4 style="margin-bottom:inherit;">{grade_text}</h4>
			</div>
			<div class="three columns">
				<select name="grade_id" class="full-width" onchange="document.location=$(this).val();">
					<?php 
						foreach($grades_names as $gid => $grade)
						{
							$selected='';
							if($gid === $grade_id)
								$selected='selected';

							$link=get_customer_question_collection_list_link($gid,0);
							echo "<option $selected value='$link'>$grade</option>";
						}
					?>
				</select>
			</div>
		</div>

		<div class="row" >
			<div class="three columns">
				<h4>{course_text}</h4>
			</div>
			<div class="three columns">
				<select name="course_id" class="full-width" onchange="document.location=$(this).val();">
					<option value="<?php echo get_customer_question_collection_list_link($grade_id,0);?>">&nbsp;</option>
					<?php 
						foreach($courses_names as $cid => $course)
						{
							$selected='';
							if($cid === $course_id)
								$selected='selected';
							
							$link=get_customer_question_collection_list_link($grade_id,$cid);
							echo "<option $selected value='$link'>$course</option>";
						}
					?>
				</select>
			</div>
		</div>
		<div id="class-list">
			<?php foreach($questions as $q) {?>
				<div class="row even-odd-bg">
					<a target="_blank"
						href="<?php echo get_customer_question_collection_details_link($grade_id,$q['qc_course_id'],$q['qc_id']);?>"
					>
						<div class="three columns">
							<?php echo $courses_names[$q['qc_course_id']];?>
						</div>
						<div class="seven columns">
							<b><?php echo $q['qc_subject'];?></b>
						</div>
						<div class="two columns">
							<?php echo explode(" ",$q['qc_date'])[0];?>
						</div>
					</a>
				</div>

			<?php } ?>
		</div>
		
			
	</div>
</div>