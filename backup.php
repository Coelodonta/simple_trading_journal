<?php
require_once "app.php";
require_once "functions.php";
$page_id=3;
$cmd="";
$result="";
$op=get_post_data('op','');
$file_name=get_post_data('file_name','');

if($op=="backup"){
	if($file_name!=''){
		$cmd="mysqldump -u $usr -p$pwd --no-tablespaces --databases $db 2>/dev/null > $file_name";
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>stonks@home</title>
    <link rel="stylesheet" href="css/index.css?=<?=date('U')?>" type="text/css"/>

    <style>
	</style>
	<script type="text/javascript">
		
	</script>
</head>
<body>
	<div class="wrapper">
		
		<div class="header">
			<h1>stonks@home</h1>TRADING JOURNAL
		</div>

		<div class="navbar">
			<?=render_navbar($page_id)?>
		</div>
		

		<div class="filters">
			<form action="" method="POST" id="mainform">
				<input type="hidden" name="op" value="backup">
				<div class="filter"><div class="filter_label">Path to backup file:</div> 
					<div class="filter_selection"><input type="text" name="file_name"></div>
					<div class="filter_selection"><button type="submit">Generate backup command</button></div>
				</div>
			</form>
		</div>
		
		<!-- Content here -->
<?php
	if($op=="backup" && $cmd!=""){
?>
		<div style="padding: 20px;">

			Run the following command in a terminal window:<br><br>
			<span style="font-family: monospace, monospace; color: blue;"> <?=$cmd?> </span>
		</div>
<?php 		
}
?>		
		<!-- End Content -->
	</div>
</body>
</html>
