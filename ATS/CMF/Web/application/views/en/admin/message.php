<div class="main">
	<div class="container">
		
		<h1>{messages_text}</h1>
		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#list">{messages_list_text}</a></li>		
				<li><a href="#group">{groups_text}</a></li>				
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
				<h2>{messages_list_text}</h2>
				<style type="text/css">
					a
					{
						color:black;
					}

					.even-odd-bg 
					{ 
						font-size: 1.2em;
					}

					.view-img
					{
						max-width:60px;
						transition: max-width .5s;
						text-align: left;
					}

					.view-img:hover
					{
						max-width:70px;
						transition: max-width .5s;
					}
				</style>

				<div class="container separated">
					<?php if(0) { ?>
					<div class="row filter half-col-margin-children">				
						<div class="three columns">
							<label>{start_date_text}</label>
							<input class="full-width ltr" name="start_date">
						</div>

						<div class="three columns ">
							<label>{end_date_text}</label>
							<input class="full-width ltr" name="end_date">
						</div>
					
						<div class="three columns ">
							<label>{status_text}</label>
							<select class="full-width" name="status">
								<option>&nbsp;</option>
								<option value="changing">{changing_text}</option>
								<option value="complete">{complete_text}</option>
							</select>
						</div>
						
						<div class="three columns">
							<label>{verification_status_of_last_message_text}</label>
							<select class="full-width" name="verified">
								<option>&nbsp;</option>
								<option value="yes">{verified_text}</option>
								<option value="no">{not_verified_text}</option>
							</select>
						</div>
						
						<div class="three columns">
							<label>{sender_text}</label>
							<select class="full-width" name="sender_type" onchange="setSender(this);">
								<option>&nbsp;</option>
								<option value="me">{me_text}</option>
								<?php 
									echo "<option value='user'>{user_text}</option>";
									echo "<option value='department'>{department_text}</option>";
									echo "<option value='customer'>{customer_text}</option>";							
								?>
							</select>

							<div class="no-display">
								
								<div class="three columns" id="sender-departments">
									<label>{sender_department_text}</label>
									<select name="sender_department" class="full-width">
										<option value="">&nbsp;</option>
										<?php
											foreach($departments as $id => $name)
												if($id)
													echo "<option value='$id'>".${"department_".$name."_text"}."</option>\n";
										?>
									</select>
								</div>
							
								<div class="three columns" id="sender-users">
									<label>{sender_user_name_or_id_text}</label>
									<input name="sender_user" type="text" class="full-width">
								</div>


								<div class="three columns" id="sender-customers">
									<label>{sender_customer_name_or_id_text}</label>
									<input name="sender_customer" type="text" class="full-width">
								</div>
							

							</div>
						</div>
						
						<div class="three columns">
							<label>{receiver_text}</label>
							<select class="full-width" name="receiver_type" onchange="setReceiver(this);">
								<option>&nbsp;</option>
								<option value="me">{me_text}</option>
								<?php 
									echo "<option value='user'>{user_text}</option>";
									echo "<option value='department'>{department_text}</option>";
									echo "<option value='customer'>{customer_text}</option>";							
								?>
							</select>

							<div class="no-display">
								<div class="three columns" id="receiver-departments">
									<label>{receiver_department_text}</label>
									<select name="receiver_department" class="full-width">
										<option value="">&nbsp;</option>
										<?php
											foreach($departments as $id => $name)
												if($id)
													echo "<option value='$id'>".${"department_".$name."_text"}."</option>\n";
										?>
									</select>
								</div>
							

								<div class="three columns " id="receiver-users">
									<label>{receiver_user_name_or_id_text}</label>
									<input name="receiver_user" type="text" class="full-width">
								</div>

								
								<div class="three columns " id="receiver-customers">
									<label>{receiver_customer_name_or_id_text}</label>
									<input name="receiver_customer" type="text" class="full-width">
								</div>
							
							</div>
						</div>

						<?php if($op_access['users']) {?>
							<div class="three columns">
								<label>{active_text}</label>
								<select class="full-width" name="active">
									<option>&nbsp;</option>
									<option value="yes">{active_text}</option>
									<option value="no">{inactive_text}</option>
								</select>
							</div>				
						<?php }?>
						<div class="two columns results-search-again ">
							<label></label>
							<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
						</div>				
						
					</div>
					<?php } ?>

					<div class="row results-count" >
						<div class="six columns">
							<label>
								{results_text} {messages_start} {to_text} {messages_end} - {total_results_text}: {messages_total}
							</label>
						</div>
						<div class="three columns results-page-select">
							<select class="full-width" onchange="pageChanged($(this).val());">
								<?php 
									for($i=1;$i<=$messages_total_pages;$i++)
									{
										$sel="";
										if($i == $messages_current_page)
											$sel="selected";

										echo "<option value='$i' $sel>$page_text $i</option>";
									}
								?>
							</select>
						</div>
					</div>

					<script type="text/javascript">
						function setSender(el)
						{
							el=$(el);
							par=el.parent();
							newVal=el.val();
							$("#sender-departments, #sender-users, #sender-customers").each(function(index,elem){
								elem=$(elem);
								$("input,select",elem).addClass("inactive");
								$(".no-display",par).append(elem);
							});

							if(!newVal || newVal=="me")
								return;

							el.parent().after($("#sender-"+newVal+"s"));
							$("input,select",$("#sender-"+newVal+"s")).removeClass("inactive");
						}

						function setReceiver(el)
						{
							el=$(el);
							par=el.parent();
							newVal=el.val();
							$("#receiver-departments, #receiver-users, #receiver-customers").each(function(index,elem){
								elem=$(elem);
								$("input,select",elem).addClass("inactive");
								$(".no-display",par).append(elem);
							});

							if(!newVal || newVal=="me")
								return;

							el.parent().after($("#receiver-"+newVal+"s"));
							$("input,select",$("#receiver-"+newVal+"s")).removeClass("inactive");
						}


						var initialFilters=[];
						<?php
							foreach($filters as $key => $val)
								echo 'initialFilters["'.$key.'"]="'.$val.'";';
						?>
						
						var rawPageUrl="{raw_page_url}";

						$(function()
						{
							$(".filter div input, .filter div select").keypress(function(ev)
							{
								if(13 != ev.keyCode)
									return;

								searchAgain();
							});

							for(i in initialFilters)
								$(".filter [name='"+i+"']").val(initialFilters[i]);

							setSender($("select[name=sender_type]")[0]);
							setReceiver($("select[name=receiver_type]")[0]);
						});

						function searchAgain()
						{
							document.location=getCustomerSearchUrl(getSearchConditions());
						}

						function getSearchConditions()
						{
							var conds=[];

							$(".filter input:not(.inactive), .filter select:not(.inactive)").each(
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
							{
								var val=filters[i].trim().replace(/\s+/g," ").replace(/[';"]/g,"");
								if(val)
									ret+="&"+i+"="+encodeURIComponent(val);
							}
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
						$i=$messages_start;
						$verification_status=array();
						if($messages_total)
						{
							foreach($messages as $mess)
							{ 
								$mess_link=$mess['link'];
					?>
						<div class="row even-odd-bg">
							<div class="three columns">
								<label>{sender_from_text}</label>
								<?php
									$type=$mess['message_sender_type'];
									if($type === "group")
										$sender=${"group_".$mess['message_sender_id']."_name_text"};										
									if($type === "teacher")						
										$sender=$mess['s_name']." (".$mess['s_subject'].")";
									if($type === "student" || $type === "parent")						
										$sender=$mess['s_name'];
									echo "<span>".$sender."</span>";
								?>
							</div>
							<div class="three columns">
								<label>{receiver_to_text}:</label>
								<?php 
									$type=$mess['message_receiver_type'];
									if($type === "group")
										$receiver=${"group_".$mess['message_receiver_id']."_name_text"};
									if($type === "teacher")						
										$receiver=$mess['r_name']." (".$mess['r_subject'].")";
									if(($type === "student") || ($type === "parent"))
										$receiver=$mess['r_name'];
									if($type === "student_class")
										$receiver=$students_of_text." ".$class_names[$mess['message_receiver_id']];
									if($type === "parent_class")
										$receiver=$parents_of_text." ".$class_names[$mess['message_receiver_id']];
									echo "<span>".$receiver."</span>";
								?>
							</div>
							
							<div class="three columns">
								<label>{last_message_text}</label>
								<span>
									<?php echo $mess['message_subject'];?><br>
									<small style="display:inline-block"  class='ltr'>
										<?php echo str_replace("-","/",$mess['message_date']); ?>
									</small>
								</span>
							</div>
							<div class="two columns">
								<label>{count_text}</label>
								<span>
									<?php echo $mess['count'];?>
								</span>
							</div>							
							<div class="one columns">
								<a target="_blank" href="<?php echo $mess_link;?>">
									<img src="{images_url}/details.png" class="view-img anti-float" title="{view_details_text}";/>
								</a>
							
							</div>
						</div>
					<?php 
							}
						}
					?>
				</div>
			</div>

			<div class="tab" id="group">
				<h2>{groups_text}</h2>

				<div class="row even-odd-bg">
					<div class="three columns">
						{group_text}
					</div>
					<div class="six columns">
						<select class="full-width" onchange="if($(this).val())location=$(this).val()">
							<option value="">&nbsp;</option>
							<?php 
								foreach($additional_groups as $gid => $gname )
								{ 
									$link=str_replace("gid",$gid,$group_url);
									$name=${"group_".$gid."_name_text"};
									$selected='';
									if($selected_group_id == $gid)
										$selected='selected';
									echo "<option value='$link' $selected >$name</option>";
								}
							?>
						</select>
					</div>
				</div>

				<?php if($selected_group_id) { ?>
					<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />
					<script src="{scripts_url}/jquery-ui.min.js"></script>

					<?php echo form_open($page_url,array("onsubmit"=>"return setMembers();")); ?>
						<input type="hidden">
						<input type="hidden" name="members" id="members-main"/>
						<input type="hidden" name="post_type" value="set_members"/>
						<div class="row even-odd-bg dont-magnify">	
							<h3>{members_text}</h3>
							<div class="three columns">
								<span>{parents_text}</span>
							</div>
							<div class="three columns">
								<input type="text" class="parents-autocomplete full-width"/>
							</div>
							<div class="tweleve column aclist" id="parents-list">
								<?php 
									foreach ($members as $mem) 
									{
										$name=$mem['customer_name'];
										$id=$mem['customer_id'];

										echo "
											<div class='three columns' data-id='$id'>
												$name
												<span class='anti-float' onclick='$(this).parent().remove();'></span>
											</div>";
									}
								?>
							</div>

							<script type="text/javascript">
								
								$(document).ready(function()
							   {
							      var el=$("input.parents-autocomplete");
						      	var searchUrl="{parents_search_url}";
							      	
						      	el.autocomplete({
							         source: function(request, response)
							         {
							            var term=request["term"];
							            $.get(searchUrl+"/"+encodeURIComponent(term)+"?type=parent&active=1",
							              function(res)
							              {
							                var rets=[];
							                for(var i=0;i<res.length;i++)
							                  rets[rets.length]=
							                    {
							                      label:res[i].name
							                      ,name:res[i].name
							                      ,id:res[i].id						                      
							                      ,value:term
							                    };

							                response(rets); 

							                return;       
							              },"json"
							            ); 
							          },
							          delay:700,
							          minLength:1,
							          select: function(event,ui)
							          {
							            var item=ui.item;
							            var id=item.id;
							            var name=item.name;

							            if(!$("div[data-id="+id+"]",$("#parents-list")).length)
							            	$("#parents-list").append($("<div class='three columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
							            
							            el.val("");
							            return false;
							          }
							      });

							   });

								function setMembers()
								{
									if(!confirm("{are_you_sure_to_submit_text}"))
										return false;

									var memberIds=[];
									$("#parents-list div").each(function(index,el)
									{
										memberIds[memberIds.length]=$(el).data("id");
									});
									
									$("#members-main").val(memberIds.join(","));
								}

							</script>
						</div>

						<br><br>
						<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
						</div>
					</form>
				<?php } ?>

			</div>
	</div>
	
</div>