<ul class="dash-ul" style="padding:10px">
	<?php foreach($rewards_counts as $r)
		echo "<li>".$r['class_name'].": ".$r['reward_count']."</li>";
	?>
</ul>
