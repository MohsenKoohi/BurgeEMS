<div class="main class-teachers">
	<div class="container">
		<h1>{teachers_text}</h1>			
		<?php foreach($teachers as $tc) { ?>
			<div class="row even-odd-bg">		
				<div class="four columns">
					<span><?php echo $tc['customer_name'];?></span>
				</div>
				<div class="four columns">
					<span><?php echo $tc['customer_subject'];?></span>
				</div>
				<div class="four columns">
					<?php if($tc['customer_address']) { ?>
						<label>{contact_time_text}</label>
						<span><?php echo $tc['customer_address'];?></span>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>