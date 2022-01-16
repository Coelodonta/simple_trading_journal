<?php
require_once "app.php";
require_once "functions.php";
require_once "dropdown_data.php";
$page_id=2;
$conn = connect($host,$usr,$pwd,$db);

$ticklist=array();
$account=get_post_data('account','0');
$dt=get_post_data('start_dt',date('Y-m-d', strtotime("-1 week")));
$edt=get_post_data('end_dt',date('Y-m-d'));
$tickers=get_post_data('symbols','');

$status_filter=get_post_data('status_filter','-1');
$side_filter=get_post_data('side_filter','-1');
$type_filter=get_post_data('type_filter','-1');
$sort_filter=get_post_data('sort_filter','desc');


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
			<div class="tickers">
				<span class="dats">Account: <?=make_listbox_from_sql_table('account',"select AcctId as k, CONCAT(AcctName,' ',AcctNo) as v from Account order by AcctId asc",$conn,$account)?></span>
				<button onclick="submit_form();"> Change Account </button>
				<span class="dats">From Date: <input style="width: 90px;" type="text" name="dt" id="dt" value="<?=$dt?>"/></span>
				<span class="dats">To Date: <input style="width: 90px;" type="text" name="edt" id="edt" value="<?=$edt?>"/></span>				
				Tickers: <input type="text" name="tickers" id="tickers" <input style="width: 180px;" value="<?=strtoupper($tickers)?>"/> <button onclick="submit_form();"> Refresh </button>
			</div>
		</div>
		
		<div class="filters">
			Filter trades by:<br/>
			<form action="" method="POST" id="mainform">
				<input type="hidden" name="op" value="statistics">
				<input type="hidden" name="symbols" id="symbols" value=""/>
				<input type="hidden" name="start_dt" id="start_dt" value="<?=$dt?>"/>
				<input type="hidden" name="end_dt" id="end_dt" value="<?=$edt?>"/>
				<input type="hidden" name="account" id="acct" value="<?=$account?>"/>
				<div class="filter"><div class="filter_label">Side</div> 
					<div class="filter_selection"><?=make_listbox_from_array("side_filter",$side_values2,$side_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">Type</div> 
					<div class="filter_selection"><?=make_listbox_from_array("type_filter",$type_values2,$type_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">
					<button type="submit"> Generate statistics</button>
				</div>
			</form>
		</div>
	</div>

		<!-- Content here -->
		<div class="picks" style="width: 50%; background-color: #ffffff;">
			<table class="picks">
<?php
$op=get_post_data('op','');
if($op=='statistics'){
	$acct_name='all accounts';	
	$acct_str='';
	$side_str='';
	$type_str='';

	$sql = "select CONCAT(AcctName,' ',AcctNo) as AcctName from Account where AcctId=$account";
	$rows=$conn->query($sql);
	if(1==$rows->num_rows){
		foreach ($rows as $row) {
			$acct_name=$row['AcctName'];	
			$acct_str=" and AcctId=$account";
		}
	}

	if($side_filter!="-1"){
		$side_str=" and Side=$side_filter";
	}


	if($type_filter!="-1"){
		$type_str=" and Type=$type_filter";
	}
?>
	<tr>
		<td colspan='4' style="text-align: center;"><h1>Statistics for <?=$acct_name?> for period <?=$dt?> - <?=$edt?></h1></td>
	</tr>
	<tr><td colspan='4'><h2>Filter Criteria</h2></td></tr>
	<tr><th>Type: </th><td><?=$type_values2[$type_filter]?></td></tr>
	<tr><th>Side: </th><td><?=$side_values2[$side_filter]?></td></tr>
	<tr><td colspan='4'><h2>Trade Status</h2></td></tr>
<?php

	// All orders open before DT.
	$sql = "select count(*) as Cnt from Trade where Status=1 and DT<='$edt' $acct_str $side_str $type_str";
	echo $sql;
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Open Orders at end of period</th><td>".$row[0]."</td><td/><td/></tr>";

	// All orders still in trade status at DT, regardless of when they were placed. Must have an entry date.
	$sql = "select count(*) as Cnt from Trade where Status=2 and DT<='$edt' and EntryDT is not NULL $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>In Trade at end of period</th><td>".$row[0]."</td><td/><td/></tr>";


	// All orders partially DT, must have an entry date
	$sql = "select count(*) as Cnt from Trade where Status=3 and DT<='$edt' and EntryDT is not NULL $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Partially Filled Trades at end of period</th><td>".$row[0]."</td><td/><td/></tr>";

	// All orders completed during the period, must have entry and exit date inside the period
	$sql = "select count(*) as Cnt from Trade where Status=4 and EntryDT>='$dt' and ExitDT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Completed Trades within the period</th><td>".$row[0]."</td><td/><td/></tr>";

	// All orders stopped out during the period, must have entry and exit date inside the period
	$sql = "select count(*) as Cnt from Trade where Status=5 and EntryDT>='$dt' and ExitDT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Stopped Out Trades within the period</th><td>".$row[0]."</td><td/><td/></tr>";

	// All orders Canceled out during the period
	$sql = "select count(*) as Cnt from Trade where Status=6 and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Canceled Trades within the period</th><td>".$row[0]."</td><td/><td/></tr>";


	// All orders Expired out during the period
	$sql = "select count(*) as Cnt from Trade where Status=7 and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Canceled Trades within the period</th><td>".$row[0]."</td><td/><td/></tr>";
	
	echo "<tr><td colspan='4'><h2>Trade Performance</h2></td></tr>";

	$sql = "select min(PL) as MinPL, max(PL) as MaxPL, min(PLDollar) as MinDL, max(PLDollar) as MaxDL from Trade where Status in (4,5) and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Worst trade profit % in the period</th><td>".round($row[0],2)."</td><td/><td/></tr>";
	echo "<tr><th>Best trade profit % in the period</th><td>".round($row[1],2)."</td><td/><td/></tr>";
	echo "<tr><th>Worst trade profit $ in the period</th><td>".round($row[2],2)."</td><td/><td/></tr>";
	echo "<tr><th>Best trade profit $ in the period</th><td>".round($row[3],2)."</td><td/><td/></tr>";

	$sql = "select avg(PL) as AvgPL, avg(PLDollar) as avgDL from Trade where Status in (4,5) and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Average trade profit % in the period</th><td>".round($row[0],2)."</td><td/><td/></tr>";
	echo "<tr><th>Average trade profit $ in the period</th><td>".round($row[1],2)."</td><td/><td/></tr>";


	$sql = "select min(Qty*ActualEntryPrice) as MinSz, max(Qty*ActualEntryPrice) as MaxSz, avg(Qty*ActualEntryPrice) as AvgSz from Trade where Status in (4,5) and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Smallest trade size $ in the period</th><td>".round($row[0],2)."</td><td/><td/></tr>";
	echo "<tr><th>Largest trade size $ in the period</th><td>".round($row[1],2)."</td><td/><td/></tr>";
	echo "<tr><th>Average trade size $ in the period</th><td>".round($row[2],2)."</td><td/><td/></tr>";


	$sql = "select min(Qty) as MinSz, max(Qty) as MaxSz, avg(Qty) as AvgSz from Trade where Status in (4,5) and DT>='$dt' and DT<='$edt' $acct_str $side_str $type_str";
	$rows=$conn->query($sql);
	$row=$rows->fetch_row();
	echo "<tr><th>Smallest trade number of shares in the period</th><td>".round($row[0],2)."</td><td/><td/></tr>";
	echo "<tr><th>Largest trade size number of shares in the period</th><td>".round($row[1],2)."</td><td/><td/></tr>";
	echo "<tr><th>Average trade size numbers of shares in the period</th><td>".round($row[2],2)."</td><td/><td/></tr>";


}
?>
			</table>
		</div>
		<!-- End Content -->
	<script type="text/javascript">
		function submit_form(){
			
			var dt=document.getElementById('dt');
			if(dt.value.length>0){
				document.getElementById('start_dt').value=dt.value;
			}

			var edt=document.getElementById('edt');
			if(edt.value.length>0){
				document.getElementById('end_dt').value=edt.value;
			}

			var tick=document.getElementById('tickers');
			if(tick.value.length>0){
				document.getElementById('symbols').value=tick.value;
			}

			var acct=document.getElementById('account');
			document.getElementById('acct').value=acct.value;
			
			document.getElementById('mainform').submit();
		}
		function copyText() {
		  	var el=document.querySelector("#ticklist");
		  	navigator.clipboard.writeText(el.value);
		}

		document.querySelector("#copybutton").addEventListener("click", copyText);

	</script>
</body>
</html>
