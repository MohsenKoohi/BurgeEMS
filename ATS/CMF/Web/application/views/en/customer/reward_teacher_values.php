<div class="main">
	<div class="container reward-students-list">
		<h1>{reward_subject}</h1>			
		<div class="anti-float">
			<span class="date">{reward_date}</span>
		</div>
		<br><br>
		<?php foreach($students_rewards as $st) { ?>
			<div class="row even-odd-bg">
				<div class="four columns name">
					<?php echo $st['customer_name'];?>
				</div>
				<div class="two columns">
					<span class="norm-hid">{value_text}:</span>
					<span class="value"><?php echo $st['rv_value'];?></span>
				</div>
				<div class="two columns"></div>
				<div class="four columns">
					<span class="norm-hid">{description_text}:</span>
					<?php echo $st['rv_description'];?>
				</div>
			</div>
		<?php } ?>
		
	</div>
</div>