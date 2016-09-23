<div class="main">
	<div class="container">
		<h1>{classes_text}</h1>

		<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />  
		<script src="{scripts_url}/jquery-ui.min.js"></script>
		<div class="container  separated">		
			<h2>{classes_list_text}</h2>
			<?php echo form_open($raw_page_url,array("onsubmit"=>"return submitChanges();")); ?>
				<input type="hidden" name="post_type" value="class_changes"/>
				<input type="hidden" name="class-ids" value=""/>
				<div id="class-list">
					<?php foreach($classes as $class) {?>
						<div class="row even-odd-bg"  data-id="<?php echo $class['class_id'];?>" style="cursor:grab;">
							<div class="four columns">
								<label>{name_text}</label>
								<input 
									type='text' value='<?php echo $class['class_name'];?>' 
									name="class-<?php echo $class['class_id'];?>"
								/>
							</div>
							<div class="four columns">
								<label>{delete_text} </label>
								<input name="delete-class-<?php echo $class['class_id']?>" type="checkbox" class="graphical" />
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
					$( "#class-list" ).sortable();
				})

				function submitChanges()
				{
					if(!confirm("{are_you_sure_to_submit_text}"))
						return false;
					
					var ids=[];
					$("#class-list .row").each(function(index,el)
					{
						ids.push($(el).data("id"));
					});

					$("input[name=class-ids]").val(ids.join(','));

					return true;
				}

			</script>
		</div>
		
		<br><br>
		<div class="container separated">
			<h2>{add_class_text}</h2>	
			<?php echo form_open($raw_page_url,array()); ?>
				<input type="hidden" name="post_type" value="add_class" />	
				<div class="row even-odd-bg" >
					<div class="three columns">
						<label>{name_text}</label>
						<input type="text" name="name" class="full-width" />
					</div>
				</div>
				<div class="row">
						<div class="four columns">&nbsp;</div>
						<input type="submit" class=" button-primary four columns" value="{add_text}"/>
				</div>				
			</form>
		</div>

	</div>
</div>