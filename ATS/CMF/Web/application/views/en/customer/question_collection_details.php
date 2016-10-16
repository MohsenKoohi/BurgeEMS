<div class="main">
	<div class="container">
		<h1>{questions_collection_text}</h1>
		<h2>
			{grade_text} {grade_name} {comma_text}
			{course_text} {course_name} {comma_text}
			<?php echo $info[0]['qc_subject'];?>
		</h2>


		<div class="row">
			<div class="three anti-float date">
				<b><?php echo explode(" ",$info[0]['qc_date'])[0];?></b>
			</div>
		</div>

		<br><br><br>
		<?php $i=1;foreach($info as $q) {?>
			<div class="row even-odd-bg">
				<div class="three columns">
					#<?php echo $i++;?>
				</div>
				<div class="seven columns">
					<a href="<?php echo $q['qcf_url'];?>" target="_blank">
						<b><?php echo $q['qcf_subject'];?></b>
					</a>
				</div>
				<div class="two columns">
					<a href="<?php echo $q['qcf_url'];?>" target="_blank"
						class="button button-primary sub-primary full-width" target="_blank"
					>
						{download_text}
					</a>
				</div>
			</div>

		<?php } ?>
		

	</div>
</div>