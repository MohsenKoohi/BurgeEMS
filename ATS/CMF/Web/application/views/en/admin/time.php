<div class="main">
	<div class="container">
		<h1>{times_text}</h1>

		<div class="container separated">
			<h2>{times_list_text}</h2>	
			<?php foreach($times as $time) {?>
				<div class="row even-odd-bg" >
					<div class="three columns">
						<label>{id_text}</label>
						<span><?php echo $time['time_id'];?></span>
					</div>					
					<div class="three columns">
						<label>{name_text} </label>
						<span><?php echo $time['time_name'];?></span>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="container separated">
			<h2>{start_new_time_text}</h2>	
			<?php echo form_open(get_link("admin_time"),array()); ?>
				<input type="hidden" name="post_type" value="add_time" />	
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