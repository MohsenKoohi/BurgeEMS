<div class="main">
	<div class="container">
		<h1>{send_message_text}</h1>

		<div class="container">
			<?php echo form_open($post_url,array("id"=>"send-message-form","onsubmit"=>"return checkForm();")); ?>
				<div class="row even-odd-bg">
					<div class="three columns">
						<label>{receiver_text}</label>
					</div>

					<div class="nine columns">
						<div class="row">
							<div class="three columns" style="padding-top:10px;">
								<input type="radio" name="receiver_type" value="class"/> {class_text}
							</div>
							<div class="six columns">
								<select name="class" class="full-width"
									onchange='$("input[name=receiver_type][value=class]").prop("checked","checked");'
								>
									<?php 
										foreach($receivers as $r)
											echo "<option value='".$r['value']."'>".$r['name']."</option>";
									?>
								</select>
							</div>
						</div>

						<div class="row">
							<div class="three columns" style="padding-top:10px;">
								<input type="radio" name="receiver_type" value="student"/> {student_text}
							</div>

							<div class="six columns">
								<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />
								<script src="{scripts_url}/jquery-ui.min.js"></script>
								<input type="hidden" name="students" id="students-main"/>
								
									<input type="text" class="students-autocomplete full-width"/>
							</div>
							
							<div class="tweleve column aclist" id="students-list" style="margin-top:20px;">
							</div>

							<script type="text/javascript">
								
								$(document).ready(function()
							   {
							      var el=$("input.students-autocomplete");
						      	var searchUrl="{students_search_url}";
							      	
						      	el.autocomplete({
							         source: function(request, response)
							         {
							            var term=request["term"];
							            $.get(searchUrl+"/"+encodeURIComponent(term),
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
							          	$("input[name=receiver_type][value=student]").prop("checked","checked");
							            var item=ui.item;
							            var id=item.id;
							            var name=item.name;

							            if(!$("div[data-id="+id+"]",$("#students-list")).length)
							            	$("#students-list").append($("<div class='three columns' data-id='"+id+"'>"+name+"<span class='anti-float' onclick='$(this).parent().remove();'></span></div>"));
							            
							            el.val("");
							            return false;
							          }
							      });

							   });

								function setStudents()
								{
									var ids=[];
									$("#students-list div").each(function(index,el)
									{
										ids[ids.length]=$(el).data("id");
									});
									if(!ids.length)
										return false;
									
									$("#students-main").val(ids.join(","));
									return true;
								}
							</script>
						
						</div>
					</div>
				</div>		
				<div class="row even-odd-bg">
					<div class="three columns">
						<label>{subject_text}</label>
					</div>
					<div class="nine columns">
						<input name="subject" class="full-width" value="{subject}"/>
					</div>
				</div>
				<div class="row even-odd-bg">
					<div class="three columns">
						<label>{content_text}</label>
					</div>
					<div class="nine columns">
						<textarea name="content" class="full-width" rows="5">{content}</textarea>
					</div>
				</div>
				<div class="row even-odd-bg">
					<div class="three columns">
						{captcha}
					</div>
					<div class="nine columns">
						<input name="captcha" class="lang-en"/>
					</div>
				</div>
				<div class="row">
					<div class="six columns">&nbsp;</div>
					<input type="submit" class=" button-primary three columns" value="{send_text}"/>
				</div>
			</form>

			<script type="text/javascript">
				function checkForm()
				{
					if(!$("input[name=receiver_type]:checked").length)
					{
						alert("{receiver_has_not_selected_text}");
						return false;
					}

					if($("input[name=receiver_type]:checked").val() === "student")
						if(!setStudents())
						{
							alert("{receiver_has_not_selected_text}");
							return false;
						}

					var form=$("#send-message-form");
					var fields=["captcha","content","subject"];
					var result=true;
					$(fields).each(function(index,value)
					{
						var val=$("[name='"+value+"']",form).val();
						if(!val)
						{
							result=false;		
							return false;
						}							
					});

					if(!result)
					{
						alert("{fill_all_fields_text}");
						return false;
					}

					if(!confirm("{are_you_sure_to_submit_text}"))
						return false;

					return true;
				}
			</script>
		</div>
	</div>
</div>