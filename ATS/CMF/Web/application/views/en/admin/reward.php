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
				<div id="class-list">
					<?php foreach($rewards as $reward) {?>
						<div class="row even-odd-bg">
							<div class="two columns">
								<?php echo $reward['reward_id'];?>
							</div>
							<div class="two columns">
								<?php echo $reward['class_name'];?>
							</div>
							<div class="two columns">
								<?php echo $reward['reward_date'];?>
							</div>
							<div class="two columns">
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