<ul class="dash-ul" style="padding:10px">
	<?php foreach($grades_counts as $gc)
		echo "<li>".$gc['grade_name'].": ".$gc['grade_count']."</li>";
	?>
</ul>
