<div class="main">
	<div class="container student-rewards-list">
		<h1>{rewards_text}</h1>
		<h2>{reward_class_name}{comma_text} {reward_subject}</h2>			
		<?php if($reward_editable) { ?>
			<div class="row general-buttons" style="margin-bottom:50px;">
				<div class="anti-float two columns button button-type2" onclick="document.location='{edit_link}';">
					{edit_text}
				</div>
			</div>
		<?php } ?>

		<div class="anti-float">
			<span class="date">{reward_date}</span>
		</div>
		<br><br>
		<div class="row">
			<?php 
			 	$reward_date=explode(" ",$reward_date)[0];
				foreach($students_rewards as $st) { 
			?>
				<div class="four columns reward">
					<div class="anti-float date">
						<?php echo $st['customer_name'];?>
					</div>
					<div class="value-text">
						<span class="value">
							<?php echo $st['rv_value'];?>
							<span class="text anti-float">{reward_text}</span>
						</span>
					</div>
					<div class="desc same-float" >
						<?php echo $reward_subject." ".$st['rv_description'];?>
					</div>
				</div>
			<?php } ?>
		</div>
		
	</div>
</div>