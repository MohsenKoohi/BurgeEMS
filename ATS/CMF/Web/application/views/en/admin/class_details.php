<div class="main">
	<div class="container">
		<h1><?php echo $info['class_name'];?></h1>

		<div class="row general-buttons">
			<div class="anti-float two columns button button-type2 " onclick="deleteClass();">
				{delete_class_text}
			</div>
		</div>
		<br><br>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#students">{students_text}</a></li>
				<li><a href="#teachers">{teachers_text}</a></li>
				<li><a href="#curriculum">{curriculum_text}</a></li>
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

			<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />  
			<script src="{scripts_url}/jquery-ui.min.js"></script>

			<div class="tab" id="students" style="">
				<h2>{students_text}</h2>
				<?php echo form_open($raw_page_url,array("onsubmit"=>"return submitStudentsSorting();")); ?>
					<input type="hidden" name="post_type" value="students_resort"/>
					<input type="hidden" name="students-ids" value=""/>
					<div id="students-list">
						<?php foreach($students as $st) {?>
							<div class="row even-odd-bg"  data-id="<?php echo $st['customer_id'];?>" style="cursor:grab;">
								<div class="two columns">
									&nbsp;
									<?php if($st['customer_image_hash']){ ?>
										<img 
											class="customer-img"
											src="<?php echo get_customer_image_url($st['customer_id'],$st['customer_image_hash']);?>"
										/>
									<?php } ?>
								</div>

								<div class="six columns">
									<label>{name_text}</label>
									<span><?php echo $st['customer_name'];?></span>
								</div>

								<div class="two columns">
									<label>{details_text}</label>
									<a href="<?php echo get_admin_customer_details_link($st['customer_id']); ?>"
										class="button button-primary sub-primary full-width" target="_blank"
									>
										{view_text}
									</a>
								</div>
							</div>

						<?php } ?>
					</div>
					<br>
					<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class="button  button-primary  four columns" value="{submit_new_sorting_text}"/>
					</div>
				</form>
			
				<script type="text/javascript">
					$(window).load(function()
					{
						$( "#students-list" ).sortable();
					})

					function submitStudentsSorting()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;
						
						var ids=[];
						$("#students-list .row").each(function(index,el)
						{
							ids.push($(el).data("id"));
						});

						$("input[name=students-ids]").val(ids.join(','));

						return true;
					}
				</script>
			</div>

			<div class="tab" id="teachers" style="">
				<h2>{teachers_text}</h2>
				<?php echo form_open($raw_page_url,array("onsubmit"=>"return submitTeachers();")); ?>
					<input type="hidden" name="post_type" value="set_teachers"/>
					<input type="hidden" name="teachers-ids" value=""/>
					<div id="teachers-list">
						<?php foreach($teachers as $tc) {?>
							<div class="row even-odd-bg"  data-id="<?php echo $tc['customer_id'];?>" style="cursor:grab;">
								<div class="four columns">
									<a href="<?php echo get_admin_customer_details_link($tc['customer_id']); ?>"
										target="_blank"
									>
										<?php echo $tc['customer_name'];?>
									</a>
								</div>

								<div class="two columns">
									&nbsp;
								</div>

								<div class="four columns">
									<input type="checkbox" class="graphical" 
										value="<?php echo $tc['customer_id']; ?>"
										<?php if($tc['ct_teacher_id']) echo 'checked';?>
									/>
								</div>
							</div>
						<?php } ?>
					</div>
					<br>
					<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class="button  button-primary  four columns" value="{submit_text}"/>
					</div>
				</form>
			
				<script type="text/javascript">
					$(window).load(function()
					{
						$( "#students-list" ).sortable();
					})

					function submitStudentsSorting()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;
						
						var ids=[];
						$("#students-list .row").each(function(index,el)
						{
							ids.push($(el).data("id"));
						});

						$("input[name=students-ids]").val(ids.join(','));

						return true;
					}

					function submitTeachers()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;
						
						var ids=[];
						$("#teachers-list input[type=checkbox]:checked").each(function(index,el)
						{
							ids.push($(el).val());
						});

						$("input[name=teachers-ids]").val(ids.join(','));

						return true;
					}
				</script>
			</div>

			<div class="tab" id="curriculum" style="">
				<div class="container">
					<h2>{curriculum_text}</h2>
					<div class="row">
						<style type="text/css">
							#curriculum table td
							{
								width:<?php echo 100.0/(1+sizeof($curriculum_hours));?>%;
							}
						</style>
						<?php echo form_open($raw_page_url,array("onsubmit"=>"return confirm('{are_you_sure_to_submit_text}');")); ?>
							<input type="hidden" name="post_type" value="set_curriculum"/>
							<table>
								<tr>
									<td></td>
									<?php foreach($curriculum_hours as $hour) { ?>
										<td><?php echo $hour['cc_course'];?></td>
									<?php } ?>
								</tr>
								<?php foreach($curriculum as $day_index => $day) { ?>
									<tr>
										<td><?php echo ${"day_".($day_index+1)."_name_text"};?></td>
										<?php foreach($day as $hour_index => $course) { ?>
											<td>
												<input type='text' class='' value='<?php echo $course;?>'
													name="course[<?php echo $day_index;?>][<?php echo $hour_index;?>]" 
												/>
											</td>
										<?php } ?>
									</tr>
								<?php } ?>
							</table>

							<br>
							<div class="row">
								<div class="four columns">&nbsp;</div>
								<input type="submit" class="button  button-primary  four columns" value="{submit_text}"/>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<br>	
		<br>
		<?php 
			echo form_open($raw_page_url,
				array(
					"style"=>'display:none'
					,"id"=>"hidden_form"
					,"target"=>"_blank"
				)
			);
		?>
			<input type="hidden" name="post_type"> 
		</form>
		<script type="text/javascript">
			function deleteClass()
			{
				if(!confirm('{are_you_sure_to_delete_text}'))
					return;

				$("#hidden_form input[name='post_type']").val("delete_class");
				$("#hidden_form").submit();
			}
			
		</script>
	</div>
</div>