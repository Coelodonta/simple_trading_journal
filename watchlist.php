<?php
require_once "app.php";
require_once "functions.php";
require_once "dropdown_data.php";

$page_id=1;
$conn = connect($host,$usr,$pwd,$db);

$ticklist=array();
$account=get_post_data('account','0');
//$dt=get_post_data('start_dt',date('Y-m-d', strtotime("-1 week")));
$dt=get_post_data('start_dt',date('Y-m-d'));
$edt=get_post_data('end_dt',date('Y-m-d'));
$tickers=get_post_data('symbols','');
$status_filter=get_post_data('status_filter','1');
$sort_filter=get_post_data('sort_filter','desc');

// Add, save etc.
$op=get_post_data('op','');
if($op=="add"){
	add_watch($conn);
}
elseif($op=="save") {
	save_watch($conn);
}
elseif($op=="dismiss") {
	one_click_dismiss($conn);
}
elseif($op=="dismiss_all") {
	one_click_dismiss_all($conn);
}

// Build sql for the list
$sql="select WatchId,AcctId,Ticker,CAST(DT as CHAR) as DT,CAST(DDT as CHAR) as DDT,Status,ImageLink,Publish,Idea, Counter 
from Watch where DT>='$dt' and DT<='$edt' ";
if($account!=""){
	$sql.=" and AcctId=$account";
}
if($status_filter!="-1"){
	$sql.=" and Status=$status_filter";
}
if($tickers!=''){
	$quoted_ticklist=parse_symbols($tickers);
	$sql.=" and Ticker in ($quoted_ticklist)";
}
$sql.=" order by DT $sort_filter, Ticker asc";
//echo $sql;

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
			Filter watchlist by:<br/>
			<form action="" method="POST" id="mainform">
				<input type="hidden" name="symbols" id="symbols" value=""/>
				<input type="hidden" name="start_dt" id="start_dt" value="<?=$dt?>"/>
				<input type="hidden" name="end_dt" id="end_dt" value="<?=$edt?>"/>
				<input type="hidden" name="account" id="acct" value="<?=$account?>"/>
				<div class="filter"><div class="filter_label">Status</div> 
					<div class="filter_selection"><?=make_listbox_from_array("status_filter",$watch_status_values,$status_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">Sort dates</div> 
					<div class="filter_selection"><?=make_listbox_from_array("sort_filter",$sort_values,$sort_filter)?></div>
				</div>
			</form>
		</div>
		
		<!-- Content here -->
		<div class="picks">
			<table>
<?php

$trades=list_watchlist($conn,$sql);

if($trades->num_rows>0){
	echo "<tr><th>Watch No</th><th>Ticker</th><th>Date <span style='font-size: xx-small;'>(yyyy-mm-dd)</span></th><th>Dismissed Date</th><th>Status</th><th>Idea</th><th>Count</th><th>Image URL</th><th>Finviz</th><th>Yahoo</th><th colspan='2'>Action</th></tr>";
}

$dark="";
foreach ($trades as $row) {
	$dark=$dark==""?" style='background-color: #aaaaaa'":"";
	$id=$row['WatchId'];
	$account_id=$row['AcctId'];
	$ticker=$row['Ticker'];
	$active_dt=$row['DT'];
	$dismiss_dt=$row['DDT'];
	$status=$row['Status'];
	$img=$row['ImageLink'];
	$idea=trim($row['Idea']);
	$ddt=$row['DDT'];
	$cnt=$row['Counter'];
?>
				<tr>
					<form action="" method="POST">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="save">
						<input type="hidden" name="account" value="<?=$account?>">
						<input type="hidden" name="status_filter"  value="<?=$status_filter?>">
						<input type="hidden" name="id" value="<?=$id?>">
						<td <?=$dark?>> <?=$id?></td>
						<td <?=$dark?>><input type="text" name="symbol" value="<?=$ticker?>"></td>
						<td <?=$dark?>><input type="text" name="active_dt" value="<?=$active_dt?>"></td>
						<td <?=$dark?>><input type="text" name="dismiss_dt" value="<?=$ddt?>"></td>
						<td <?=$dark?>><?=make_listbox_from_array('status',$watch_status_values,$status)?></td>
						<td <?=$dark?>><textarea name="idea"><?=$idea?></textarea></td>
						<td <?=$dark?>><input type="text" name="counter" value="<?=$cnt?>"></td>
						<td <?=$dark?>><input type="text" name="image_link" value="<?=$img?>"></td>
						<td <?=$dark?>> <a href="https://finviz.com/quote.ashx?t=<?=$ticker?>" target="<?=$ticker?>">https://finviz.com/quote.ashx?t=<?=$ticker?></a> </td>
						<td <?=$dark?>> <a href="https://finance.yahoo.com/quote/<?=$ticker?>/chart" target="<?=$ticker?>">https://finance.yahoo.com/quote/<?=$ticker?>/chart</a> </td>
						<td <?=$dark?>><button type='submit'>Save</button></td>
					</form>
					<form action="" method="POST">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="dismiss">
						<input type="hidden" name="account" value="<?=$account?>">
						<input type="hidden" name="status_filter"  value="<?=$status_filter?>">
						<input type="hidden" name="id" value="<?=$id?>">
						<td <?=$dark?>><button type='submit'>Dismiss</button></td>
					</form>
				</tr>

<?php
	$ticklist[]=$ticker;
} // Close foreach
?>
	</table>
	<table style="width: 30%">
				<tr><th>Ticker</th><th>Date <span style="font-size: xx-small;">(yyyy-mm-dd)</span></th><th>Idea</th><th>Action</th></tr>

<?php
if($account!=""){
	$dark=" style='background-color: #e0e0ff'";
?>
				<tr style="width: 50%;">
					<form action="" method="POST" id="addform">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="add">
						<!--<input type="hidden" name="status_filter"  value="<?=$status_filter?>">-->
						<input type="hidden" name="status_filter"  value="1">
						<input type="hidden" name="account" value="<?=$account?>">
						<td <?=$dark?>><input type="text" name="symbol"></td>
						<td <?=$dark?>><input type="text" name="DT" value="<?=date('Y-m-d')?>"></td>
						<td <?=$dark?>><textarea name="idea"></textarea></td>
						<td <?=$dark?>><button type='submit'>Add</button></td>
					</form>
				</tr>
				<tr style="width: 50%;">
					<form action="" method="POST">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="dismiss_all">
						<input type="hidden" name="status_filter"  value="1">
						<input type="hidden" name="account" value="<?=$account?>">
						<td <?=$dark?> colspan='3'>Dismiss all ticker older than <?=$dt?> for this account</td>
						<td <?=$dark?>><button type='submit'>Dismiss all</button></td>
					</form>
				</tr>
<?php
} // Closes if
?>

			</table>
		</div>
		<!-- End Content -->

		<div style="float: none;"></div>
		<div style="margin: 5px;">
			<button id="copybutton">Copy tickers</button>
			<input type="hidden" id="ticklist" value="<?=implode(' ',$ticklist)?>"/>
		</div>

	</div>

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
<?php
function add_watch($conn){
	$account=get_post_data('account',"0");
	$ticker=trim(get_post_data('symbol',''));
	$DT=get_post_data('DT',date('Y-m-d'));
	$idea=trim(get_post_data('idea',''));

	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="insert into Watch (AcctId,Ticker,DT,DDT,Status,ImageLink,Publish,Idea ) 
	select $account,UPPER('$ticker'),'$DT',NULL,1,NULL,NULL,'$idea'";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}

function list_watchlist($conn,$sql){
	$rs=$conn->query($sql);
	return $rs;
}

function save_watch($conn){
	$id=get_post_data('id','-1');
	//$account=get_post_data('account',"0");
	$ticker=trim(get_post_data('symbol',''));
	$DT=get_post_data('active_dt',date('Y-m-d'));
	$status=get_post_data('status',1);
	$DDT=get_post_data('dismiss_dt','');
	// Escape date
	$DDT=$DDT==''?"NULL":"'$DDT'";
	$img=trim(get_post_data('image_link',''));
	$idea=trim(get_post_data('idea',''));

	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="update Watch set Ticker=UPPER('$ticker'),DT='$DT',DDT=$DDT,Status=$status,ImageLink='$img',Idea='$idea' where WatchId=$id";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}

function one_click_dismiss($conn){
	$id=get_post_data('id','-1');

	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="update Watch set DDT=CURDATE(), Status=2 where WatchId=$id";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}

function one_click_dismiss_all($conn){
	$dt=get_post_data('start_dt',date('Y-m-d'));
	$account=get_post_data('account','0');
	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="update Watch set DDT=CURDATE(), Status=2 where AcctId=$account and Status=1 and DT<'$dt'";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}

?>