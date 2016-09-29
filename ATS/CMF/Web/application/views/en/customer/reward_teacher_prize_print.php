<!DOCTYPE html>
<html lang="fa">
<head>
	<meta charset="UTF-8" />
	<style type="text/css">
	tr:first-child
	{
		background-color: #eee;
		font-weight: bold;
		text-align: center;
	}
	 table
	 {
	 	border-collapse: collapse;
	 }
	 td
	 {
	 	padding:10px;
	 	font-size:1.2em;
	 	border:1px solid #ccc;
	 }

	 td:nth-child(2)
	 {
	 	text-align: center;
	 }
	</style>
</head>
<body style="direction:rtl;">

<div class="main" style="font-family:b mitra,mitra;">
	<div class="container reward">
		<h1>{rewards_list_text} | <?php echo $classes_names[$class_id];?></h1>
		<div style="float:left;direction:ltr"><b><?php echo get_current_time(); ?></b></div>
		<br><br>
		<table style="width:100%">
			<tr>
				<td>{name_text}</td>
				<td>{reward_text}</td>
				<td>{used_text}</td>
			</tr>
			<?php foreach($students as $st) { ?>
				<tr>		
					<td>
						<?php echo $st['customer_name'];?>
					</td>
					<td>
						<span style="direction:ltr;display:inline-block;" ><?php echo $st['total_rewards'] ?></span>
					</td>
					<td>

					</td>
				</tr>
			<?php } ?>	
		</table>
	</div>
</div>
</body>
</html>