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
					<?php foreach($rewards as $reward) {?>
						<div class="row even-odd-bg">
							<div class="one columns">
								<?php echo $reward['reward_id'];?>
							</div>
							<div class="two columns">
								<?php echo $reward['class_name'];?>
							</div>
							<div class="two columns">
								<?php echo $reward['reward_date'];?>
							</div>
							<div class="three columns">
								<?php echo $reward['reward_subject'];?>
							</div>
							<div class="two columns">
								<?php echo $reward['teacher_name'];?>
							</div>
							<div class="two columns">
								<a href="<?php echo get_admin_reward_details_link($reward['reward_id']); ?>"
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
				<?php echo form_open_multipart($raw_page_url,array()); ?>
					<input type="hidden" name="post_type" value="add_question" />	
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{grade_text}</span>
						</div>
						<div class="four columns">
							<select name="grade" class="full-width">
								<?php 
									for($i=1;$i<=$grades_count;$i++)
									{
										$text=${"grade_".$i."_text"};
										echo "<option value='$i'>$text</option>";
									}
								?>
							</select>
						</div>
					</div>

					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{course_text}</span>
						</div>
						<div class="four columns">
							<select name="course" class="full-width">
								<?php 
									for($i=1;$i<=$courses_count;$i++)
									{
										$text=${"course_".$i."_text"};
										echo "<option value='$i'>$text</option>";
									}
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
							+		"<input type='text' class='full-width' name='subject["+index+"]'/>"
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