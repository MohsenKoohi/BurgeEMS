<div class="main">
	<div class="container">
		<h1>{header}</h1>
		<?php if($customer_type === 'teacher'){ ?>
			<div class="row general-buttons">
				<div class="three columns">
					<?php echo form_open($raw_page_url,array());?>
						<input type="hidden" name="post_type" value="add_class_post"/>
						<input type="submit" class="button button-primary full-width" value="{add_text}"/>
					</form>
				</div>
			</div>
			<br><br>
		<?php } ?>
		<div class="container separated">
			<?php if($customer_type === 'teacher') { ?>
				<div class="row filter">
					<div class="three columns">
						<label>{academic_year_text}</label>
						<select  class="full-width" name="academic_time">
							<?php 
								foreach($academic_times as $t)
									echo "<option value='".$t['time_id']."'>".$t['time_name']."</option>";
							?>
						</select>
					</div>
					
					<div class="two columns results-search-again half-col-margin">
						<label></label>
						<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
					</div>			
				</div>
			<?php } ?>
			<div class="row results-count" >
				<div class="six columns">
					<label>
						{results_text} {posts_start} {to_text} {posts_end} - {total_results_text}: {posts_total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$posts_total_pages;$i++)
							{
								$sel="";
								if($i == $posts_current_page)
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
					document.location=getCustomerSearchUrl(getSearchConditions());
				}

				function getSearchConditions()
				{
					var conds=[];

					$(".filter input, .filter select").each(
						function(index,el)
						{
							var el=$(el);

							if(el.prop("type")=="button")
								return;

							if(el.val())
								conds[el.prop("name")]=el.val();

						}
					);
					
					return conds;
				}

				function getCustomerSearchUrl(filters)
				{
					var ret=rawPageUrl+"?";
					for(i in filters)
						ret+="&"+i+"="+encodeURIComponent(filters[i].trim().replace(/\s+/g," "));
					return ret;
				}

				function pageChanged(pageNumber)
				{
					document.location=getCustomerSearchUrl(initialFilters)+"&page="+pageNumber;
				}
			</script>
		</div>
		<br>
		<div class="container">
			<?php 
				$i=$posts_start;
				if(isset($class_posts_info))
					foreach($class_posts_info as $cp)
					{ 
						if($cp['cp_assignment'])
							$link=get_customer_class_post_assignment_view_link($cp['cp_id']);
						else
							$link=get_customer_class_post_discussion_view_link($cp['cp_id']);
	
			?>		
						<div class="row even-odd-bg" >
							<div class="one column counter normal-font-size">
								<?php echo $i++;?>
							</div>

							<div class="five columns">
								<?php 
									if($cp['cpt_title']) 
										echo $cp['cpt_title'];
									else
										echo $no_title_text;
								?>
							</div>

							<?php if($customer_type == 'teacher') { ?>
								<div class="two columns">
									<?php echo $cp['class_name'];?>
									<div class='date'><?php echo $cp['academic_time'];?></div>
								</div>
							<?php } ?>

							<?php if($customer_type == 'student') { ?>
								<div class="two columns">
									<?php echo $cp['teacher_name']." (".$cp['teacher_subject'].")";?>
								</div>
							<?php } ?>

							<div class="two columns">
									<div class='date'><?php echo $cp['cp_start_date'];?></div>
							</div>

							<div class="two columns">
								<a target="_blank" class="button button-primary sub-primary full-width" href="<?php echo $link;?>">
									{view_text}
								</a>
							</div>
						</div>
			<?php
					}
			?>
		</div>

	</div>
</div>