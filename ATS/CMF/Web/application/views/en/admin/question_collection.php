<div class="main">
	<div class="container">
		<h1>{questions_collection_text}</h1>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#list">{questions_list_text}</a></li>		
				<li><a href="#add">{add_question_text}</a></li>				
			</ul>
			<script type="text/javascript">
				$(function(){
				   $('ul.tabs').each(function(){
						var $active, $content, $links = $(this).find('a');
						$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
						$active.addClass('active');

						$content = $($active[0].hash);

						$links.not($active).each(function () {
						   $(this.hash).hide();
						});

						$(this).on('click', 'a', function(e){
						   $active.removeClass('active');
						   $content.hide();

						   $active = $(this);
						   $content = $(this.hash);

						   $active.addClass('active');

						   $content.show();						   	

						   e.preventDefault();
						});
					});
				});
			</script>

			<div class="tab" id="list">
				<h2>{questions_list_text}</h2>
				<div id="class-list">
					<?php foreach($questions as $q) {?>
						<div class="row even-odd-bg">
							<div class="two columns">
								<?php echo $grades_names[$q['qc_grade_id']];?>
							</div>
							<div class="two columns">
								<?php echo $courses_names[$q['qc_course_id']];?>
							</div>
							<div class="four columns">
								<?php echo $q['qc_subject'];?>
							</div>
							<div class="two columns" title="<?php echo $q['qc_date'];?>">
								<?php echo explode(" ",$q['qc_date'])[0];?>
							</div>
							<div class="two columns">
								<a href="<?php echo get_admin_question_collection_details_link($q['qc_id']); ?>"
									class="button button-primary sub-primary full-width" target="_blank"
								>
									{view_text}
								</a>
							</div>
						</div>

					<?php } ?>
				</div>
			</div>
			
			<div class="tab" id="add">
				<h2>{add_question_text}</h2>	
				<?php echo form_open_multipart($raw_page_url,array("onsubmit"=>"return alert('{are_you_sure_to_submit_text}')")); ?>
					<input type="hidden" name="post_type" value="add_question" />	
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{grade_text}</span>
						</div>
						<div class="four columns">
							<select name="grade_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php 
									foreach($grades_names as $gid => $grade)
										echo "<option value='$gid'>$grade</option>";
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
							<input  name="subjec" class="full-width"/>
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

		</div>

	</div>
</div>