<div class="main">
	<div class="container">
		<h1>{questions_collection_text}</h1>
		<div class="row even-odd-bg">
			<div class="three columns">
				{grade_text}:
			</div>
			<div class="nine columns">	
				<?php echo $grades_names[$info[0]['qc_grade_id']];?>
			</div>
		</div>

		<div class="row even-odd-bg">
			<div class="three columns">
				{course_text}:
			</div>
			<div class="nine columns">	
				<?php echo $courses_names[$info[0]['qc_course_id']];?>
			</div>
		</div>

		<div class="row even-odd-bg">
			<div class="three columns">
				{subject_text}:
			</div>
			<div class="nine columns">	
				<?php echo $info[0]['qc_subject'];?>
			</div>
		</div>

		<div class="row even-odd-bg">
			<div class="three columns">
				{registrar_text}:
			</div>
			<div class="nine columns">	
				<?php echo $info[0]['qc_registrar_name'];?>
			</div>
		</div>

		<div class="row even-odd-bg">
			<div class="three columns">
				{date_text}:
			</div>
			<div class="nine columns">	
				<?php echo $info[0]['qc_date'];?>
			</div>
		</div>

		
		<div id="class-list">
			<?php $i=1;foreach($info as $q) {?>
				<div class="row even-odd-bg">
					<div class="three columns">
						{file_text} <?php echo $i++;?>:
					</div>
					<div class="seven columns">
						<?php echo $q['qcf_subject'];?>
					</div>
					<div class="two columns">
						<a href="<?php echo $q['qcf_url'];?>"
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