<?php
function connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME){
    // Connecting to the database
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
    // If we don't connect to the database it will spit out an error for us to fix
    if(!$conn) {
      die("Connection failed: ".mysqli_connect_error());
    }
    return $conn;
} 

function get_post_data($field,$default=''){
	return isset($_POST[$field])?trim($_POST[$field]):$default;
}


function make_listbox_from_sql_table($name,$sql,$conn,$selval='1'){
    $rows=$conn->query($sql);
	$rc="<select name='$name' id='$name'><option value=''> Any </option>";
	foreach ($rows as $row) {
		$k=$row['k'];
		$v=$row['v'];
		$sel=$k==$selval?"selected":"";
		$rc.="<option value='$k' $sel> $v </option>";
	}
	$rc.="</select>";
	return $rc;
}


function make_listbox_from_array($name,$data,$selval=''){
	$rc="<select name='$name' id='$name'>";
	foreach ($data as $key => $value) {
		$sel=$key==$selval?"selected":"";
		$rc.="<option value='$key' $sel> $value </option>";
	}
	$rc.="</select>";
	return $rc;
}


function parse_symbols($tickers){
	$has_quotes=strpos($tickers,"'");
	if(strpos($tickers,",")==FALSE){
		$ticks=explode(" ",$tickers);
	}
	else{
		$ticks=explode(",",$tickers);
	}
	if($has_quotes!==FALSE){
		$rc=implode(",",$ticks);
	}
	else{
		$comma="";
		$rc="";
		foreach($ticks as $t){
			$rc.="$comma'$t'";
			$comma=",";
		}
	}
	return strtoupper($rc);
}

function render_navbar($selval=0){
	$sel=["","","","","",""];
	$sel[$selval]="class='current_page'";
	return "<a href='index.php' ".$sel[0].">Trades</a> <a href='watchlist.php' ".$sel[1].">Watch</a> <a href='stats.php' ".$sel[2].">Stats</a> <a href='backup.php' ".$sel[3].">Backup</a>";
}

?>