<link rel="stylesheet" type="text/css" href="{styles_url}/colorbox.css" />
<script src="{scripts_url}/colorbox.js"></script>
  
<div class="main">
	<div class="container class-post">
		<h1><?php echo $page_title;?></h1>

		<div class="row">
			<div class="three columns">
				{academic_year_text}:
			</div>
			<div class="six columns post-date">
				<?php 
					echo $cp_info['academic_time'];
				?>
			</div>
		</div>		

		<div class="row">
			<div class="three columns">
				{class_text}:
			</div>
			<div class="six columns ">
				<?php 
					echo $cp_info['class_name'];
				?>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{teacher_text}:
			</div>
			<div class="six columns">
				<?php 
					echo $cp_info['teacher_name']." (".$cp_info['teacher_subject'].")";
				?>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{type_text}:
			</div>
			<div class="six columns">
				<?php 
					if($cp_info['cp_assignment']) 
						echo $assignment_text; 
					else 
						echo $discussion_text;
				?>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{start_date_text}:
			</div>
			<div class="six columns">
				<div class="date">
					<?php 
						echo str_replace("-","/",$cp_info['cp_start_date']);
					?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{end_date_text}:
			</div>
			<div class="six columns">
				<div class="date">
					<?php 
						echo str_replace("-","/",$cp_info['cp_end_date']);
					?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{active_text}:
			</div>
			<div class="six columns">
				<?php 
					if($cp_info['cp_active']) 
						echo $yes_text; 
					else 
						echo $no_text;
				?>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{allow_submit_response_text}:
			</div>
			<div class="six columns">
				<?php 
					if($cp_info['cp_allow_comment']) 
						echo $yes_text; 
					else 
						echo $no_text;
				?>
			</div>
		</div>

		<div class="row">
			<div class="three columns">
				{allow_submit_file_text}:
			</div>
			<div class="six columns">
				<?php 
					if($cp_info['cp_allow_file']) 
						echo $yes_text; 
					else 
						echo $no_text;
				?>
			</div>
		</div>

		<div class="row separated class-post-content">
			<div class="full-width">
				<?php echo $cp_info['cpt_content'] ?>
			</div>
		
			<div class="row post-gallery">
				<?php 
					if($cp_info['cpt_gallery'])
						foreach($cp_info['cpt_gallery']['images'] as $img)
						{ 
							$link=get_class_post_gallery_image_url($class_post_id,$img['image']);
				?>
					<div class="four columns img-div" title="<?php echo $img['text'];?>"  href="<?php echo $link;?>" >
						<div class="img lazy-load"  data-ll-url="<?php echo $link;?>"
						 data-ll-type="background-image" >
						</div>
						<div class="text">
							<?php echo $img['text'];?>
						</div>
					</div>
				<?php } ?>

				<script type="text/javascript">

					$(window).load(function()
					{
						$("body").addClass("post-page");
						$(window).on("resize",setColorBox);
						setColorBox();
					});

					function setColorBox()
					{
						$.colorbox.remove();
						$(".img-div").unbind("click");

						if($(window).width() < 600)
							$(".img-div").click(function(event)
							{
								window.open($(event.target).parent().attr("href"));
							});
						else
							$(".img-div").colorbox({
								rel:"group"
								,iframe:false
								,width:"80%"
								,height:"80%"
								,opacity:.4
								,fixed:true
								,current:"{image_text} {current} {from_text} {total}" 

							});
					}
				</script>
			</div>
		</div>

		<?php if($comments) { ?>
			<div class='row separated comments'>
				<?php 
					$i=0;
					$count=sizeof($comments);

					for($i=0;$i<$count;$i++)
					{ 
						$c=$comments[$i];

						$div_open=FALSE;
						if( ($i==0) || ($comments[$i-1]['cpc_customer_id'] != $c['cpc_customer_id']))
							$div_open=TRUE;

						$div_close=FALSE;
						if ( ($i==$count-1) || ($comments[$i+1]['cpc_customer_id'] != $c['cpc_customer_id']) )
							$div_close=TRUE;
				?>

					<?php if($div_open) { ?>
						<div class='row even-odd-bg dont-magnify'>
							<div class="twelve columns">
								<label>
									<?php echo $c['customer_name']?>
								</label>
					<?php } ?>

								<div class='row comment'>
									<div class='date-row'>
										<div class='date anti-float'>
											<?php echo $c['cpc_date'];?>
										</div>
									</div>

									<p class='align-justify'><?php echo nl2br($c['cpc_comment'])?></p>
									
									<div class='last-row'>
										<?php if($c['cpc_file']) { ?>
											<div class="attachment same-float">
												<a target='_blank' 
													href='<?php echo get_class_post_comment_file_url($class_post_id,$c['cpc_id'],$c['cpc_file']); ?>'
												>
													{attachment_text}
												</a>
											</div>
										<?php } ?>

										<?php if(!$cp_info['cp_assignment']) { ?>
											<div class="verify-comment anti-float">
												<div class='active'>
													<?php 
														if($c['cpc_active']) 
															echo $active_text; 
														else 
															echo $inactive_text;
													?>
												</div>
											</div>
										<?php } ?>
										
									</div>
								</div>

					<?php if($div_close){ ?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>

	</div>
</div>