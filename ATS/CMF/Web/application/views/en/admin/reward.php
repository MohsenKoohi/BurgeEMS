<div class="main">
	<div class="container">
		<h1>{rewards_text}</h1>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#list">{rewards_list_text}</a></li>		
				<li><a href="#prize-access">{prize_access_text}</a></li>				
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
				<h2>{rewards_list_text}</h2>
				<div class="container separated">
					<div class="row filter">
						<div class="three columns">
							<label>{academic_year_text}</label>
							<select  class="full-width " name="time_id">
								<option value=''>&nbsp;</option>
								<?php 
									foreach($academic_times as $t)
										echo "<option value='".$t['time_id']."'>".$t['time_name']."</option>";
								?>
							</select>
						</div>

						<div class="three columns half-col-margin">
							<label>{teacher_text}</label>
							<select name="teacher_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php foreach($teachers as $t){ ?>
									<option value="<?php echo $t['customer_id'];?>">
										<?php echo $t['customer_name']." (".$t['customer_subject']. ")";?>
								<?php } ?>
							</select>
						</div>
						<div class="three columns half-col-margin">
							<label>{class_name_text}</label>
							<select name="class_id" class="full-width">
								<option value="">&nbsp;</option>
								<?php
									foreach ($classes as $c)
										echo "<option value='".$c['class_id']."'>".$c['class_name']."</option>";
								?>
							</select>
						</div>
						<div class="three columns ">
							<label>{subject_text}</label>
							<input type="text" name="subject" class="full-width" />
						</div>
						<div class="three columns half-col-margin">
							<label>{start_date_text}</label>
							<input type="text" name="start_date" class="full-width ltr" />
						</div>
						<div class="three columns half-col-margin">
							<label>{end_date_text}</label>
							<input type="text" name="end_date" class="full-width ltr" />
						</div>

						<div class="three columns ">
							<label>{prize_text}</label>
							<select name="is_prize" class="full-width">
								<option>&nbsp;</option>
								<option value="1">{yes_text}</option>
								<option value="0">{no_text}</option>
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
						
						});

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
				<?php foreach($rewards as $reward) {?>
					<div class="row even-odd-bg">
						<div class="one columns">
							<?php echo $reward['reward_id'];?>
						</div>
						<div class="two columns">
							<?php echo $reward['class_name'];?>
						</div>
						<div class="two columns">
							<span class="ltr-inb">
								<?php echo $reward['reward_date'];?>
							</span>
						</div>
						<div class="three columns">
							<b>
								<?php
									if(!$reward['reward_teacher_id'])
										echo $previous_year_rewards_text;
									else
										echo $reward['reward_subject'];		
								?>
							</b>
						</div>
						<div class="two columns">
							<?php
								if($reward['reward_teacher_id']) 
									echo $reward['customer_name']." (".$reward['customer_subject'].")"
							;?> 
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
			
			<div class="tab" id="prize-access">
				<h2>{prize_access_text}</h2>	
				<?php echo form_open($raw_page_url,array("onsubmit"=>"return submitTeachers();")); ?>
					<input type="hidden" name="post_type" value="set_prize_access" />	
					<input type="hidden" name="teachers-ids" value=""/>
					<?php foreach($teachers as $tc) { ?>
						<div class="row even-odd-bg" >
							<div class="four columns">
								<?php echo $tc['customer_name'];?>
							</div>
							<div class="four columns">
								<?php echo $tc['customer_subject'];?>
							</div>
							<div class="four columns">
								<input type="checkbox" class="graphical" value="<?php echo $tc['customer_id'];?>"
								<?php if($tc['ct_teacher_id']) echo 'checked';?>
								/>
							</div>
						</div>
					<?php } ?>
					<br><br>
					<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
					</div>				
				</form>
				<script type="text/javascript">
					function submitTeachers()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;
						
						var ids=[];
						$("#prize-access input[type=checkbox]:checked").each(function(index,el)
						{
							ids.push($(el).val());
						});

						$("input[name=teachers-ids]").val(ids.join(','));

						return true;
					}
				</script>
			</div>

		</div>

	</div>
</div>