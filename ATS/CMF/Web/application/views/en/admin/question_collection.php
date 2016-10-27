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

				<div class="container separated">
					<div class="row filter">
						<div class="three columns">
							<label>{grade_text}</label>
							<select name="grade_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach ($grades as $gid=>$gname)
										echo "<option value='".$gid."'>".$gname."</option>";
								?>
							</select>
						</div>

						<div class="three columns half-col-margin">
							<label>{course_text}</label>
							<select name="course_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach ($courses as $cid=>$cname)
										echo "<option value='".$cid."'>".$cname."</option>";
								?>
							</select>
						</div>

						<div class="three columns half-col-margin">
							<label>{subject_text}</label>
							<input type="text" name="subject" class="full-width" />
						</div>

						<div class="three columns">
							<label>{start_date_text}</label>
							<input type="text" name="start_date" class="full-width ltr" />
						</div>
						<div class="three columns half-col-margin">
							<label>{end_date_text}</label>
							<input type="text" name="end_date" class="full-width ltr" />
						</div>

						<div class="three columns half-col-margin">
							<label>{registrar_text}</label>
							<select name="registrar_type" class="full-width" onchange="registrarTypeChanged()">
								<option value="">&nbsp;</option>
								<option value="teacher">{teacher_text}</option>
								<option value="user">{user_text}</option>
							</select>
						</div>

						<div class="three columns" id="teacher-div">
							<label>{teacher_text}</label>
							<select name="teacher_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php foreach($teachers as $t){ ?>
									<option value="<?php echo $t['customer_id'];?>">
										<?php echo $t['customer_name']." (".$t['customer_subject']. ")";?>
								<?php } ?>
							</select>
						</div>

						<div class="three columns" id="user-div">
							<label>{user_text}</label>
							<select name="user_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php foreach($users as $u){ ?>
									<option value="<?php echo $u['user_id'];?>">
										<?php echo $u['user_name']." (".$u['user_code']. ")";?>
								<?php } ?>
							</select>
						</div>

					</div>
					<div clas="row">
						<div class="two columns results-search-again">
							<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
						</div>
					</div>
					<div class="row results-count" >
						<div class="three columns">
							<label>
								{results_text} {results_start} {to_text} {results_end} - {total_results_text}: {total_count}
							</label>
						</div>
						<div class="three columns results-page-select">
							<select class="full-width" onchange="pageChanged($(this).val());">
								<?php 
									for($i=1;$i<=$total_pages;$i++)
									{
										$sel="";
										if($i == $current_page)
											$sel="selected";

										echo "<option value='$i' $sel>$page_text $i</option>";
									}
								?>
							</select>
						</div>
					</div>

					<script type="text/javascript">							
						var initialFilters=[];
						<?php
							foreach($filter as $key => $val)
								echo 'initialFilters["'.$key.'"]="'.$val.'";';
						?>
						var rawPageUrl="{raw_page_url}";

						$(function()
						{
							$(".filter input, .filter select").keypress(function(ev)
							{
								if(13 != ev.keyCode)
									return;

								searchAgain();
							});

							for(i in initialFilters)
								$(".filter [name='"+i+"']").val(initialFilters[i]);
							
							registrarTypeChanged();
						});

						function registrarTypeChanged()
						{
							$("#teacher-div,#user-div").css("display","none");
							var rtype=$("select[name=registrar_type]").val();

							if(rtype=='teacher')
								$("#teacher-div").css("display","block");
							
							if(rtype=='user')
								$("#user-div").css("display","block");
						}

						function searchAgain()
						{
							document.location=getSearchUrl(getSearchConditions());
						}

						function getSearchConditions()
						{
							var conds=[];

							$(".filter input, .filter select").each(
								function(index,el)
								{
									var el=$(el);
									if(el.val())
										conds[el.prop("name")]=el.val();

								}
							);
							
							return conds;
						}

						function getSearchUrl(filters)
						{
							var ret=rawPageUrl+"?";
							for(i in filters)
								ret+="&"+i+"="+encodeURIComponent(filters[i].trim().replace(/\s+/g," "));
							return ret;
						}

						function pageChanged(pageNumber)
						{
							document.location=getSearchUrl(initialFilters)+"&page="+pageNumber;
						}
					</script>
				</div>
				<br><br>

				<?php foreach($questions as $q) {?>
					<div class="row even-odd-bg">
						<div class="two columns">
							<b><?php echo $grades_names[$q['qc_grade_id']];?></b>
						</div>
						<div class="two columns">
							<b><?php echo $courses_names[$q['qc_course_id']];?></b>
						</div>
						<div class="four columns">
							<b><?php echo $q['qc_subject'];?></b>
						</div>
						<div class="two columns" title="<?php echo $q['qc_date'];?>">
							<?php echo $q['qc_registrar_name'];?><br>
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
			
			<div class="tab" id="add">
				<h2>{add_question_text}</h2>	
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

		</div>

	</div>
</div>