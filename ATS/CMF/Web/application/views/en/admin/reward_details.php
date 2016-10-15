<div class="main">
	<div class="container">
		<h1>{rewards_text}</h1>
		<h2><?php echo $info['reward_subject'];?></h2>
		<div class="row even-odd-bg">
			<div class="four columns name">
				{date_text}
			</div>
			<div class="eight columns">
				<span class="ltr-inb">
					<?php echo $info['reward_date'];?>
				</span>
			</div>
		</div>
		<div class="row even-odd-bg">
			<div class="four columns name">
				{class_name_text}
			</div>
			<div class="eight columns">
				<?php echo $info['class_name'];?>
			</div>
		</div>
		<div class="row even-odd-bg">
			<div class="four columns name">
				{teacher_text}
			</div>
			<div class="eight columns">
				<?php echo $info['teacher_name'];?>
			</div>
		</div>
		<br><br>
		<h2>{reward_values_text}</h2>
		<?php foreach($students_rewards as $st) { ?>
			<div class="row even-odd-bg">
				<div class="four columns name">
					<label>{name_text}</label>
					<?php echo $st['customer_name'];?>
				</div>
				<div class="two columns">
					<label>{value_text}</label>
					<span class="ltr-inb">
						<?php echo $st['rv_value'];?>
					</span>
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