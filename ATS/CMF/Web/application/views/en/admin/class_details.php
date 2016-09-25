<div class="main">
	<div class="container">
		<h1><?php echo $info['class_name'];?></h1>

		<div class="row general-buttons">
			<div class="anti-float two columns button button-primary "
			 onclick="if(confirm('{are_you_sure_to_create_new_password_text}')) window.open('{new_pass_link}','_blank');"
			 >
				{print_password_text}
			</div>
		</div>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#students">{students_text}</a></li>
				<li><a href="#teachers">{teachers_text}</a></li>
				<li><a href="#cirriculum">{cirriculum_text}</a></li>
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
								<div class="nine columns">
									<?php echo $st['customer_name'];?>
								</div>

								<div class="two columns">
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

				</script>
			</div>

			<div class="tab" id="teachers" style="">
				<div class="container">
					<h2>{tasks_text}</h2>
					<div class="row">
					</div>
				</div>
			</div>

			<div class="tab" id="cirriculum" style="">
				<div class="container">
					<h2>{events_text}</h2>
					
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
			function customerLogin()
			{
				if(!$("#props input[name='customer_code']").val())
				{
					alert("{customer_code_has_not_been_specified_text}");
					return;
				}

				$("#hidden_form input[name='post_type']").val("customer_login");
				$("#hidden_form").submit();
			}
			
		</script>
	</div>
</div>