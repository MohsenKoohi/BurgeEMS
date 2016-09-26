<div class="main class-teachers">
	<div class="container">
		<h1>{teachers_text}</h1>			
		<?php foreach($teachers as $tc) { ?>
			<div class="row even-odd-bg">		
				<div class="three columns">
					<span><?php echo $tc['customer_name'];?></span>
				</div>
				<div class="six columns">
					<span><?php echo $tc['customer_subject'];?></span>
				</div>
			</div>
		<?php } ?>
	</div>
</div>