<ul class="dash-ul" style="padding:10px">
	<?php 
		foreach($classes as $c)
			echo "<li>".$c['class_name'].":".$c['count']."</li>";
	?>
</ul>
