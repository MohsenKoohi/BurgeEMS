<div class="main">
	<div class="container">
		<h1>{questions_collection_text}</h1>
		<h2>{collections_submitted_by_you_text}</h2>

		<div id="class-list">
			<?php foreach($questions as $q) {?>
				<div class="row even-odd-bg">					
					<div class="two columns">
						<label>{grade_text}</label>
						<span><?php echo $grades_names[$q['qc_grade_id']];?></span>
					</div>
					<div class="two columns">
						<label>{course_text}</label>
						<span><?php echo $courses_names[$q['qc_course_id']];?></span>
					</div>
					<div class="four columns mobile-center">
						<label>{subject_text}</label>
						<span><b><?php echo $q['qc_subject'];?></b></span>
					</div>
					<div class="two columns">
						<label>{submit_date_text}</label>
						<span><?php echo explode(" ",$q['qc_date'])[0];?></span>
					</div>
					<div class="two columns">
						<label>&nbsp;</label>
						<a href="<?php echo get_customer_question_collection_details_link($q['qc_grade_id'],$q['qc_course_id'],$q['qc_id']);?>"
							class="button button-primary sub-primary full-width" target="_blank"
						>
							{view_text}
						</a>
					</div>
				</div>

			<?php } ?>
		</div>
		
			
	</div>
</div>