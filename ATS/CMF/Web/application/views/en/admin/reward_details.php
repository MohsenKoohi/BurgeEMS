<div class="main">
	<div class="container">
		<h1>{rewards_text}</h1>
		<h2><?php echo $info['reward_subject'];?></h2>
		<div class="anti-float"><?php echo $info['reward_date'];?></div>
		<br><br>
		<?php foreach($students_rewards as $st) { ?>
			<div class="row even-odd-bg">
				<div class="four columns name">
					<label>{name_text}</label>
					<?php echo $st['customer_name'];?>
				</div>
				<div class="two columns">
					<label>{value_text}</label>
					<?php echo $st['rv_value'];?>
				</div>
				<div class="two columns"></div>
				<div class="four columns">
					<label>{description_text}</label>
					<?php echo $st['rv_description'];?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>