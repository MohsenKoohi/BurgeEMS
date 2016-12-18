<div class="main">
	<div class="container">
		<h1>{page_title}</h1>		
		
		<script src="{scripts_url}/tinymce/tinymce.min.js"></script>
		<div class="container">
			<div class="row general-buttons">
				<a  class="two columns"  onclick="deletePost()">
					<div class="full-width button sub-primary button-type2">
						{delete_text}
					</div>
				</a>
			</div>
			<br>
			<?php echo form_open_multipart($raw_page_url,array("onsubmit"=>"return formSubmit();")); ?>
				<input type="hidden" name="post_type" value="edit_class_post" />
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{academic_year_text}</span>
					</div>
					<div class="six columns">
						<?php echo $cp_info['academic_year'];?>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{teacher_text}</span>
					</div>
					<div class="six columns">
						<?php echo $cp_info['teacher_name']." (".$cp_info['teacher_subject'].")";?>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{class_text}</span>
					</div>
					<div class="six columns">
						<select class="full-width" name='class_id'>
							<?php 
								foreach($teacher_classes as $c)
									echo "<option value='".$c['class_id']."'>".$c['class_name']."</option>";
							?>
						</select>

						<script type="text/javascript">
							$("select[name=class_id]").val("<?php echo $cp_info['class_id'];?>");
						</script>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{start_date_text}</span>
					</div>
					<div class="six columns">
						<input type="text" class="full-width ltr" name="start_date" value="<?php echo $cp_info['start_date'];?>" />
					</div>
					<div class="three columns">
						<img 
							class="icon update" src="{images_url}/update.png" 
							onclick="$('[name=start_date]').val('{current_time}');"
						/>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{end_date_text}</span>
					</div>
					<div class="six columns">
						<input type="text" class="full-width ltr" name="end_date" value="<?php echo $cp_info['end_date'];?>" />
					</div>
					<div class="three columns">
						<img 
							class="icon update" src="{images_url}/update.png" 
							onclick="$('[name=end_date]').val('{current_time}');"
						/>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{active_text}</span>
					</div>
					<div class="six columns">
						<input type="checkbox" class="graphical" name="active"
							<?php if($cp_info['active']) echo "checked"; ?>
						/>
					</div>
				</div>
				
				<div class="row even-odd-bg" >
					<div class="three columns">
						<?php if($cp_info['assignment']){ ?>
							<span>{allow_submit_response_text}</span>
						<?php } else { ?>
							<span>{allow_comment_text}</span>
						<?php } ?>
					</div>
					<div class="six columns">
						<input type="checkbox" class="graphical" name="allow_comment"
							<?php if($cp_info['allow_comment']) echo "checked"; ?>
						/>
					</div>
				</div>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<span>{allow_submit_file_text}</span>
					</div>
					<div class="six columns">
						<input type="checkbox" class="graphical" name="allow_file"
							<?php if($cp_info['allow_file']) echo "checked"; ?>
						/>
					</div>
				</div>			
			
				<div class="tab-container">
					
					<ul class="tabs">
						<?php foreach($cp_texts as $cpt) { ?>
							<li>
								<a href="#pc_<?php echo $cpt['cpt_lang_id'];?>">
									<?php echo $langs[$cpt['cpt_lang_id']];?>
								</a>
							</li>
						<?php } ?>
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

					<?php foreach($cp_texts as $lang=>$cpt) {?>
						<div class="tab" id="pc_<?php echo $cpt['cpt_lang_id'];?>">
							<div class="container">
								<div class="row even-odd-bg" >
									<div class="three columns">
										<span>{title_text}</span>
									</div>
									<div class="nine columns">
										<input type="text" class="full-width" 
											name="<?php echo $lang;?>[title]" 
											value="<?php echo $cpt['cpt_title']; ?>"
										/>
									</div>
								</div>
								<div class="row even-odd-bg dont-magnify" >
									<div class="three columns">
										<span>{content_text}</span>
									</div>
									<div class="twelve columns ">
										<textarea class="full-width" rows="15"
											name="<?php echo $lang;?>[content]"
										><?php echo $cpt['cpt_content']; ?></textarea>
									</div>
								</div>
								<div class="row even-odd-bg" >
									<div class="three columns">
										<span>{gallery_text}</span>
									</div>
									<div class="nine columns">
										<style type="text/css">
											.gallery-row img
											{
												max-width: 100%;
												max-height: 300px;
											}
										</style>
										<?php 
											$gallery=$cpt['cpt_gallery']; 
											if($gallery)
												foreach($gallery['images'] as $index=>$gim)
												{
													$img_link=get_class_post_gallery_image_url($class_post_id,$gim['image']);
										?>
												<div class="row gallery-row separated">
													<input type='hidden' name='<?php echo $lang;?>[gallery][old_images][]' value='<?php echo $index;?>'/>
													<div class="five columns">
														<a href="<?php echo $img_link; ?>" target="_blank">
															<img class="lazy-load" data-ll-type="src"
																data-ll-url="<?php echo $img_link; ?>"/>
														</a>
													</div>
													<div class="six columns half-col-margin">
														<input type="text"  class="full-width" 
															name='<?php echo $lang;?>[gallery][old_image_text][<?php echo $index?>]'
															value="<?php echo $gim['text']; ?>"
														/>
														<input type="hidden"  
															name='<?php echo $lang;?>[gallery][old_image_image][<?php echo $index?>]'
															value="<?php echo $gim['image']; ?>"
														/>
														<br><br>
														{delete_text}
														<input type="checkbox" class="graphical"
															name='<?php echo $lang;?>[gallery][old_image_delete][<?php echo $index?>]'
														/>
													</div>
												</div>
										<?php
												}
										?>
										<input type="hidden"  
											name="<?php echo $lang;?>[gallery][count]"  
											value="0"
										/>
										<div class="row separated">
											<label>&nbsp;</label>
											<div class="five columns button button-type1" style="font-size:1.3em"
												onclick="addGalleryRow('<?php echo $lang;?>',this);" >
												{add_image_text}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
				<br><br>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class="button-primary four columns" value="{submit_text}"/>
				</div>				
			</form>

			<div style="display:none">
				<?php echo form_open($raw_page_url,array("id"=>"delete")); ?>
					<input type="hidden" name="post_type" value="delete_class_post"/>
				</form>

				<script type="text/javascript">

				function trimTitle(el)
				{
					el=$(el);
					var newTitle=el.val().trim().replace(/\s+/g," ");
					el.val(newTitle);
				}

				//gallery operations
				function addGalleryRow(lang,el)
				{
					var counter=$("input[name='"+lang+"[gallery][count]']");
					var index=parseInt(counter.val());
					counter.val(index+1);
					
					var html=
						"<div class='row separated'>"
						+	"<input type='hidden' name='"+lang+"[gallery][new_images][]' value='"+index+"'/>"
						+	"<div class='six columns'>"
						+		"<label>{image_text}</label>"
						+		"<input type='file' multiple name='"+lang+"[gallery][new_image]["+index+"][]'/>"
						+	"</div>"
						+	"<div class='six columns'>"
						+		"<label>{description_text}</label>"
						+		"<input type='text' class='full-width' name='"+lang+"[gallery][new_text]["+index+"]'/>"
						+	"</div>"
						+"</div>";

					html=$(html);
					$(el).parent().before(html);
				}
				//end of gallery operations

				$(window).load(initializeTextAreas);
				var tmTextAreas=[];
				<?php
					foreach($langs as $lang => $value)
						echo "\n".'tmTextAreas.push("textarea[name=\''.$lang.'[content]\']");';
				?>
				var tineMCEFontFamilies=
					"Mitra= b mitra, mitra;Yagut= b yagut, yagut; Titr= b titr, titr; Zar= b zar, zar; Koodak= b koodak, koodak;"+
					+"Andale Mono=andale mono,times;"
					+"Arial=arial,helvetica,sans-serif;"
					+"Arial Black=arial black,avant garde;"
					+"Book Antiqua=book antiqua,palatino;"
					+"Comic Sans MS=comic sans ms,sans-serif;"
					+"Courier New=courier new,courier;"
					+"Georgia=georgia,palatino;"
					+"Helvetica=helvetica;"
					+"Impact=impact,chicago;"
					+"Symbol=symbol;"
					+"Tahoma=tahoma,arial,helvetica,sans-serif;"
					+"Terminal=terminal,monaco;"
					+"Times New Roman=times new roman,times;"
					+"Trebuchet MS=trebuchet ms,geneva;"
					+"Verdana=verdana,geneva;"
					+"Webdings=webdings;"
					+"Wingdings=wingdings,zapf dingbats";
				var tinyMCEPlugins="directionality textcolor link image hr emoticons2 lineheight colorpicker media code table";
				var tinyMCEToolbar=[
				   "link image media hr bold italic underline strikethrough alignleft aligncenter alignright alignjustify styleselect formatselect fontselect fontsizeselect  emoticons2",
				   "cut copy paste bullist numlist outdent indent forecolor backcolor removeformat  ltr rtl lineheightselect code table"
				];

				function formSubmit()
				{
					if(!confirm("{are_you_sure_to_submit_text}"))
						return false;

					return true;
				}

				function RoxyFileBrowser(field_name, url, type, win)
				{
					var roxyFileman ="{file_manager_link}";

					if (roxyFileman.indexOf("?") < 0) {     
					 roxyFileman += "?type=" + type;   
					}
					else {
					 roxyFileman += "&type=" + type;
					}
					roxyFileman += '&input=' + field_name + '&value=' + win.document.getElementById(field_name).value;
					if(tinyMCE.activeEditor.settings.language){
					 roxyFileman += '&langCode=' + tinyMCE.activeEditor.settings.language;
					}

					tinyMCE.activeEditor.windowManager.open({
					  file: roxyFileman,
					  title: 'Roxy Fileman',
					  width: 850, 
					  height: 650,
					  resizable: "yes",
					  plugins: "media",
					  inline: "yes",
					  close_previous: "no"  
					}, {     window: win,     input: field_name    });
				
					return false; 
				}

				function initializeTextAreas()
				{
					for(i in tmTextAreas)
	               tinymce.init({
							selector: tmTextAreas[i]
							,plugins: tinyMCEPlugins
							,file_browser_callback: RoxyFileBrowser
							//,width:"600"
							,height:"600"
							,convert_urls:false
							,toolbar: tinyMCEToolbar
							,font_formats:tineMCEFontFamilies
							,media_live_embeds: true
               	});
           	}

           	function deletePost()
				{
					if(!confirm("{are_you_sure_to_delete_this_class_post_text}"))
						return;

					$("form#delete").submit();
				}
				</script>
			</div>
		</div>
	</div>
</div>