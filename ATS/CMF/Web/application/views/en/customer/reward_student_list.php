<div class="main">
	<div class="container student-rewards-list">
		<h1>{rewards_text}</h1>
		<div class="row">
		<?php foreach($rewards as $r) { ?>
				<div class="four columns reward">
					<div class="anti-float date">
						<?php echo explode(" ",$r['reward_date'])[0];?>
					</div>
					<div class="value-text">
						<span class="value">
							<?php echo $r['rv_value'];?>
							<span class="text anti-float">{reward_text}</span>
						</span>
					</div>
					<div class="desc same-float">
						<?php echo $r['reward_subject']." ".$r['rv_description'];?>
					</div>
				</div>
		<?php } ?>
		</div>

		
	</div>
</div>