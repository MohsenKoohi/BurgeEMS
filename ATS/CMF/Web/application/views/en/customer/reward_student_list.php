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
						<div class="desc same-float" >
							<?php 
								if(!$r['customer_name'])
									echo $previous_year_rewards_text;
								else
								{
									echo $r['reward_subject'];
									if($r['rv_description'])
										echo $comma_text." ".$r['rv_description'];
								}
							?>
						</div>
					</div>
			<?php } ?>
		</div>

		<div class="row">
			<div class='four columns reward inactive'>
			</div>
			<div class="four columns reward total">
				<div class="value-text">
					<span class="value">
						<?php echo $total_rewards;?>
						<span class="text anti-float">{reward_text}</span>
					</span>
				</div>
				<div class="desc same-float">
					{total_text}
				</div>
			</div>
		</div>

		<script type="text/javascript">
			$(".reward .desc").each(function(index,el)
				{
					var el=$(el);
					el.prop("title",el.html());
				}
			);
		</script>

		
	</div>
</div>