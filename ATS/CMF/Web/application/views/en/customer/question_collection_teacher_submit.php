<div class="main">
	<div class="container">
		<h1>{submit_questions_collection_text}</h1>
		<?php echo form_open_multipart($raw_page_url,array("onsubmit"=>"return confirm('{are_you_sure_to_submit_the_new_questions_set_text}')")); ?>
			<input type="hidden" name="post_type" value="add_question" />	
			<div class="row even-odd-bg" >
				<div class="three columns">
					<span>{grade_text}</span>
				</div>
				<div class="four columns">
					<select name="grade_id" class="full-width">
						<option value="">&nbsp;</option>
						<?php 
							foreach($grade_ids as $gid)
								echo "<option value='$gid'>".$grades_names[$gid]."</option>";
						?>
					</select>
				</div>
			</div>

			<div class="row even-odd-bg" >
				<div class="three columns">
					<span>{course_text}</span>
				</div>
				<div class="four columns">
					<select name="course_id" class="full-width">
						<option value="">&nbsp;</option>
						<?php 
							foreach($courses_names as $cid => $course)
								echo "<option value='$cid'>$course</option>";
						?>
					</select>
				</div>
			</div>

			<div class="row even-odd-bg" >
				<div class="three columns">
					<span>{subject_text}</span>
				</div>
				<div class="eight columns">
					<input  name="subject" class="full-width"/>
				</div>
			</div>
			<div class="row separated">
				<label>&nbsp;</label>
				<div id="addRow" class="five columns button button-type1" style="font-size:1.3em"
					onclick="addFileRow(this);" >
					{add_file_text}
				</div>
			</div>
			<input type="hidden" name="file_count"  value="0"/>
			<br><br>
			<div class="row">
					<div class="four columns">&nbsp;</div>
					<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
			</div>				
		</form>
		<script type="text/javascript">
			function addFileRow(el)
			{
				var counter=$("input[name='file_count']");
				var index=parseInt(counter.val());
				counter.val(index+1);
				
				var html=
					"<div class='row even-odd-bg'>"
					+	"<div class='four columns'>"
					+		"<label>{select_file_text}</label>"
					+		"<input type='file' name='files["+index+"]'/>"
					+	"</div>"
					+	"<div class='eight columns'>"
					+		"<label>{file_subject_text}</label>"
					+		"<input type='text' class='full-width' name='subjects["+index+"]'/>"
					+	"</div>"
					+"</div>";

				html=$(html);
				setCheckBoxGraphical($("input[type=checkbox]",html)[0]);

				$(el).parent().before(html);
			}

			$(function()
			{
				$("#addRow").trigger("click");
			})
		</script>
</div>