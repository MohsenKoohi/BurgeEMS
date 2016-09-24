<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<style type="text/css">
		td
		{
			border:1px solid #eee;
			padding:10px;
		}

		td:nth-child(2)
		{
			font-weight: bold;
			font-size: 1.2em;
		}

		td.fa
		{
			
		}

		td.en
		{
			direction:ltr;
			text-align: left;
		}

		img
		{
			max-width: 200px;
			max-height: 50px;
		}
	</style>
<body style="direction:ltr;">
</head>
<?php $i=0; foreach($customers as $cs) {  ?>
	<img src="{images_url}/logo-text.png" style="float:left"/>
	<img src="{images_url}/logo-notext.png" style="float:right"/>
	<table style="width:100%;border-collapse: collapse;">
		<tr>
			<td class="fa">{name_text}</td>
			<td class="fa"><?php echo $cs['customer_name'];?></td>
		</tr>
		<tr>
			<td class="fa">{url_text}</td>
			<td class="en">{url}</td>
		</tr>
		<tr>
			<td class="fa">{username_text}</td>
			<td class="en"><?php echo $cs['customer_code'];?></td>
		</tr>
		<tr>
			<td class="fa">{password_text}</td>
			<td class="en"><?php echo $cs['customer_pass'];?></td>
		</tr>
	</table>
	<br>
	<hr <?php if(++$i % 3 == 0) echo "style='page-break-after: always'";?> >
	<br>

<?php } ?>
</body>
</html>
  
  