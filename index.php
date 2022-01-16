<?php
require_once "app.php";
require_once "functions.php";
require_once "dropdown_data.php";

$page_id=0;
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

// Add, save etc.
$op=get_post_data('op','');
if($op=="add"){
	add_trade($conn);
}
elseif($op=="save") {
	save_trade($conn);
}

// Build sql for the list
$sql="select TradeId,AcctId,Ticker,CAST(DT as CHAR) as DT,Side,Type,Qty,EntryPrice,TargetPrice,StopPrice,CAST(EntryDT as CHAR) as EntryDT,
ActualEntryPrice,CAST(ExitDT as CHAR) as ExitDT,ActualExitPrice,Status,PL,PLDollar,ImageLink,Publish,Idea 
from Trade 
where DT>='$dt' and DT<='$edt' ";
if($account!=""){
	$sql.=" and AcctId=$account";
}
if($status_filter!="-1"){
	$sql.=" and Status=$status_filter";
}
if($side_filter!="-1"){
	$sql.=" and Side=$side_filter";
}
if($type_filter!="-1"){
	$sql.=" and Type=$type_filter";
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
			Filter trades by:<br/>
			<form action="" method="POST" id="mainform">
				<input type="hidden" name="symbols" id="symbols" value=""/>
				<input type="hidden" name="start_dt" id="start_dt" value="<?=$dt?>"/>
				<input type="hidden" name="end_dt" id="end_dt" value="<?=$edt?>"/>
				<input type="hidden" name="account" id="acct" value="<?=$account?>"/>
				<div class="filter"><div class="filter_label">Status</div> 
					<div class="filter_selection"><?=make_listbox_from_array("status_filter",$status_values2,$status_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">Side</div> 
					<div class="filter_selection"><?=make_listbox_from_array("side_filter",$side_values2,$side_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">Type</div> 
					<div class="filter_selection"><?=make_listbox_from_array("type_filter",$type_values2,$type_filter)?></div>
				</div>
				<div class="filter"><div class="filter_label">Sort dates</div> 
					<div class="filter_selection"><?=make_listbox_from_array("sort_filter",$sort_values,$sort_filter)?></div>
				</div>
			</form>
		</div>
		
		<!-- Content here -->
		<div class="picks">
			<table>
				<tr><th>Trade No</th><th>Ticker</th><th>Date <span style="font-size: xx-small;">(yyyy-mm-dd)</span></th><th>Side</th><th>Type</th><th>Qty</th><th>Entry Price $</th><th>Stop Price $</th><th>Target Exit $</th><th>Entry Date</th><th>Actual Entry $</th><th>Exit Date</th><th>Actual Exit $</th>
				<th>Status</th><th>P/L %</th><th>P/L $</th><th>Idea</th><th>Image URL</th><th>Action</th></tr>
<?php
$trades=list_trades($conn,$sql);

$dark="";
foreach ($trades as $row) {
	$dark=$dark==""?" style='background-color: #aaaaaa'":"";
	$id=$row['TradeId'];
	$account_id=$row['AcctId'];
	$ticker=$row['Ticker'];
	$trade_dt=$row['DT'];
	$side=$row['Side'];
	$type=$row['Type'];
	$qty=$row['Qty'];
	$entry_price=$row['EntryPrice'];
	$target_price=$row['TargetPrice'];
	$stop_price=$row['StopPrice'];
	$actual_entry_price=$row['ActualEntryPrice'];
	$actual_exit_price=$row['ActualExitPrice'];
	$status=$row['Status'];
	$pl=$row['PL'];
	$pld=$row['PLDollar'];
	$img=$row['ImageLink'];
	$idea=trim($row['Idea']);
	$entry_dt=$row['EntryDT'];
	$exit_dt=$row['ExitDT'];

?>
				<tr>
					<form action="" method="POST">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="save">
						<input type="hidden" name="account" value="<?=$account_id?>">
						<input type="hidden" name="id" value="<?=$id?>">
						<input type="hidden" name="status_filter"  value="<?=$status_filter?>">
						<input type="hidden" name="side_filter"  value="<?=$side_filter?>">
						<input type="hidden" name="type_filter"  value="<?=$type_filter?>">
						<input type="hidden" name="sort_filter"  value="<?=$sort_filter?>">
						<td <?=$dark?>> <?=$id?> 
							<a href="https://finviz.com/quote.ashx?t=<?=$ticker?>" target="<?=$ticker?>">F</a> 
							<a href="https://finance.yahoo.com/quote/<?=$ticker?>/chart" target="<?=$ticker?>">Y</a></td>
						<td <?=$dark?>><input type="text" name="symbol" value="<?=$ticker?>"></td>
						<td <?=$dark?>><input type="text" name="trade_dt" value="<?=$trade_dt?>"></td>
						<td <?=$dark?>><?=make_listbox_from_array('side',$side_values,$side)?></td>
						<td <?=$dark?>><?=make_listbox_from_array('type',$type_values,$type)?></td>
						<td <?=$dark?>><input type="qty" name="qty" value="<?=$qty?>"></td>
						<td <?=$dark?>><input type="text" name="entry_price" value="<?=$entry_price?>"></td>
						<td <?=$dark?>><input type="text" name="stop_price" value="<?=$stop_price?>"></td>
						<td <?=$dark?>><input type="text" name="target_price" value="<?=$target_price?>"></td>
						<td <?=$dark?>><input type="text" name="entry_dt"  value="<?=$entry_dt?>"></td>
						<td <?=$dark?>><input type="text" name="actual_entry_price"  value="<?=$actual_entry_price?>"></td>
						<td <?=$dark?>><input type="text" name="exit_dt"  value="<?=$exit_dt?>"></td>
						<td <?=$dark?>><input type="text" name="actual_exit_price"  value="<?=$actual_exit_price?>"></td>
						<td <?=$dark?>><?=make_listbox_from_array('status',$status_values,$status)?></td>
						<td <?=$dark?>><input type="text" name="profit_loss"  value="<?=$pl?>"></td>
						<td <?=$dark?>><input type="text" name="profit_dollar"  value="<?=$pld?>"></td>
						<td <?=$dark?>><textarea name="idea"><?=$idea?></textarea></td>
						<td <?=$dark?>><input type="text" name="image_link" value="<?=$img?>"></td>
						<td <?=$dark?>><button type='submit'>Save</button></td>
					</form>
				</tr>

<?php
	$ticklist[]=$ticker;
} // Close foreach
?>
				<tr><th>Trade No</th><th>Ticker</th><th>Date <span style="font-size: xx-small;">(yyyy-mm-dd)</span></th><th>Side</th><th>Type</th><th>Qty</th><th>Entry Price $</th><th>Stop Price $</th><th>Target Exit $</th><th>Entry Date</th><th>Actual Entry $</th><th>Exit Date</th><th>Actual Exit $</th>
				<th>Status</th><th>P/L %</th><th>P/L $</th><th>Idea</th><th>Image URL</th><th>Action</th></tr>

<?php
if($account!=""){
	$dark=" style='background-color: #e0e0ff'";
?>
				<tr>
					<form action="" method="POST" id="addform">
						<input type="hidden" name="start_dt" value="<?=$dt?>">
						<input type="hidden" name="end_dt" value="<?=$edt?>">
						<input type="hidden" name="op" value="add">
						<input type="hidden" name="account" value="<?=$account?>">
						<!--<input type="hidden" name="status_filter"  value="<?=$status_filter?>">-->
						<input type="hidden" name="status_filter"  value="1">
						<input type="hidden" name="side_filter"  value="<?=$side_filter?>">
						<input type="hidden" name="type_filter"  value="<?=$type_filter?>">
						<input type="hidden" name="sort_filter"  value="<?=$sort_filter?>">
						<td <?=$dark?>>&nbsp;</td>
						<td <?=$dark?>><input type="text" name="symbol"></td>
						<td <?=$dark?>><input type="text" name="trade_dt" value="<?=date('Y-m-d')?>"></td>
						<td <?=$dark?>><?=make_listbox_from_array('side',$side_values,'1')?></td>
						<td <?=$dark?>><?=make_listbox_from_array('type',$type_values,'2')?></td>
						<td <?=$dark?>><input type="qty" name="qty" value="100"></td>
						<td <?=$dark?>><input type="text" name="entry_price" value="0"></td>
						<td <?=$dark?>><input type="text" name="stop_price" value="0"></td>
						<td <?=$dark?>><input type="text" name="target_price" value="0"></td>
						<td <?=$dark?>><input type="text" name="entry_dt" disabled="true"></td>
						<td <?=$dark?>><input type="text" name="actual_entry_price" disabled="true"></td>
						<td <?=$dark?>><input type="text" name="exit_dt" disabled="true>"></td>
						<td <?=$dark?>><input type="text" name="actual_exit_price" disabled="true"></td>
						<td <?=$dark?>>Open</td>
						<td <?=$dark?>><input type="text" name="profit_loss" disabled="true"></td>
						<td <?=$dark?>><input type="text" name="profit_dollar" disabled="true"></td>
						<td <?=$dark?>><textarea name="idea"></textarea></td>
						<td <?=$dark?>><input type="text" name="image_link"></td>
						<td <?=$dark?>><button type='submit'>Add</button></td>
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
function add_trade($conn){
	$account=get_post_data('account',"0");
	$ticker=trim(get_post_data('symbol',''));
	$trade_dt=get_post_data('trade_dt',date('Y-m-d'));
	$side=get_post_data('side',"1");
	$type=get_post_data('type',"2");
	$qty=get_post_data('qty',"100");
	$entry_price=get_post_data('entry_price',0.0);
	$target_price=get_post_data('target_price',0.0);
	$stop_price=get_post_data('stop_price',0.0);
	$img=trim(get_post_data('image_link',''));
	$idea=trim(get_post_data('idea',''));

	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="insert into Trade (AcctId,Ticker,DT,Side,Type,Qty,EntryPrice,TargetPrice,StopPrice,EntryDT,ActualEntryPrice,ExitDT,ActualExitPrice,Status,PL,PLDollar,ImageLink,Publish,Idea) 
	select $account,UPPER('$ticker'),'$trade_dt',$side,$type,$qty,$entry_price,$target_price,$stop_price,NULL,0.00,NULL,0.00,1,0.0,0.0,'$img',NULL,'$idea'";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}

function list_trades($conn,$sql){
	$rs=$conn->query($sql);
	return $rs;
}

function save_trade($conn){
	$id=get_post_data('id','-1');
	//$account=get_post_data('account',"0");
	$ticker=trim(get_post_data('symbol',''));
	$trade_dt=get_post_data('trade_dt',date('Y-m-d'));
	$side=get_post_data('side',"1");
	$type=get_post_data('type',"2");
	$qty=get_post_data('qty',"100");
	$entry_price=get_post_data('entry_price',0.0);
	$target_price=get_post_data('target_price',0.0);
	$stop_price=get_post_data('stop_price',0.0);
	$actual_entry_price=get_post_data('actual_entry_price',0.0);
	$actual_exit_price=get_post_data('actual_exit_price',0.0);
	$status=get_post_data('status',1);
	$pl=get_post_data('profit_loss',0.0);
	$pld=get_post_data('profit_dollar',0.0);
	$entry_dt=get_post_data('entry_dt','');
	// Escape date
	$entry_dt=$entry_dt==''?"NULL":"'$entry_dt'";
	$exit_dt=get_post_data('exit_dt','');
	// Escape date
	$exit_dt=$exit_dt==''?"NULL":"'$exit_dt'";
	$img=trim(get_post_data('image_link',''));
	$idea=trim(get_post_data('idea',''));

	// Calculate profit/loss if they're still set to 0
	// Don't override any manual entries
	if($actual_entry_price>0.0 && $actual_exit_price>0.0){
		if($pl==0.0 && $pld==0.0){
			$profit=100*(($actual_exit_price-$actual_entry_price)/$actual_entry_price);
			$pl=round($profit,2);
			$dollars=$qty*($actual_exit_price-$actual_entry_price);
			$pld=round($dollars,2);
		}
	}

	// TO DO: Convert to a proper prepared statement with bound parameters
	$sql="update Trade set Ticker=UPPER('$ticker'),DT='$trade_dt',Side=$side,Type=$type,Qty=$qty,EntryPrice=$entry_price,TargetPrice=$target_price,StopPrice=$stop_price,EntryDT=$entry_dt,
	ActualEntryPrice=$actual_entry_price,ExitDT=$exit_dt,ActualExitPrice=$actual_exit_price,Status=$status,PL=$pl,PLDollar=$pld,ImageLink='$img',Idea='$idea' where TradeId=$id";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$stmt->close();
}
?>