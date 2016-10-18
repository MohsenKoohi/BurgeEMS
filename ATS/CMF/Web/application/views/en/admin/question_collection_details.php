<div class="main">
	<div class="container">
		<h1>{questions_collection_text}<h1>
		<h2>
			{grade_text} <?php echo $grades_names[$info[0]['qc_grade_id']];?>{comma_text}
			{course_text} <?php echo $courses_names[$info[0]['qc_course_id']];?>{comma_text}
			<?php echo $info[0]['qc_subject'];?>
		</h2>

		<div class="row general-buttons">
			<div class="anti-float two columns button button-type2" onclick="deleteQC()">
				{delete_text}
			</div>

			<?php echo form_open($raw_page_url,array("id"=>"delete_form")); ?>
				<input type="hidden" name="post_type" value="delete_qc"/>
			</form>

			<script type="text/javascript">
				function deleteQC()
				{
					if(confirm('{are_you_sure_to_delete_this_collection_text}'))
						$("#delete_form").submit();
				}
			</script>
		</div>
		<br><br>
		
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
			<div class="nine columns ltr">	
				<?php echo $info[0]['qc_date'];?>
			</div>
		</div>
		
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