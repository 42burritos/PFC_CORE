<?php
/*******************************************************************************

    Copyright 2012 Whole Foods Co-op

    This file is part of Fannie.

    Fannie is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Fannie is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include("../../config.php");

require_once($FANNIE_ROOT.'src/mysql_connect.php');
require($FANNIE_ROOT.'src/csv_parser.php');
require($FANNIE_ROOT.'src/tmp_dir.php');

if (!isset($_REQUEST['lc_col'])){
	$tpath = sys_get_temp_dir()."/vendorupload/";
	$fp = fopen($tpath."lcimp.csv","r");
	echo '<h3>Select columns</h3>';
	echo '<form action="load.php" method="post">';
	echo '<table cellpadding="4" cellspacing="0" border="1">';
	$width = 0;
	$table = "";
	for($i=0;$i<5;$i++){
		$line = fgets($fp);
		$data = csv_parser($line);
		$table .= '<tr><td>&nbsp;</td>';
		$j=0;
		foreach($data as $d){
			$table .='<td>'.$d.'</td>';
			$j++;
		}
		if ($j > $width) $width = $j;
		$table .= '</tr>';
	}
	echo '<tr><th>LC</th>';
	for($i=0;$i<$width;$i++){
		echo '<td><input type="radio" name="lc_col" value="'.$i.'" /></td>';
	}
	echo '</tr>';
	echo '<tr><th>Description</th>';
	for($i=0;$i<$width;$i++){
		echo '<td><input type="radio" name="desc_col" value="'.$i.'" /></td>';
	}
	echo '</tr>';
	echo '<tr><th>Origin</th>';
	for($i=0;$i<$width;$i++){
		echo '<td><input type="radio" name="origin_col" value="'.$i.'" /></td>';
	}
	echo '</tr>';
	echo $table;
	echo '</table>';
	echo '<input type="submit" value="Continue" />';
	echo '</form>';
	exit;
}

$LC = (isset($_REQUEST['lc_col'])) ? (int)$_REQUEST['lc_col'] : 0;
$DESC = (isset($_REQUEST['desc_col'])) ? (int)$_REQUEST['desc_col'] : 2;
$ORIGIN = (isset($_REQUEST['origin_col'])) ? (int)$_REQUEST['origin_col'] : 4;

$tpath = sys_get_temp_dir()."/vendorupload/";
$fp = fopen($tpath."lcimp.csv","r");
while(!feof($fp)){
	$line = fgets($fp);
	$data = csv_parser($line);
	if (!is_array($data)) continue;
	if (count($data) < 3) continue;

	if (!isset($data[$LC])) continue;
	if (!isset($data[$DESC])) continue;
	if (!isset($data[$ORIGIN])) continue;

	$l = $data[$LC];
	$d = $data[$DESC];
	$o = $data[$ORIGIN];
	if (!is_numeric($l) || $l != (int)$l) continue;

	$q = "SELECT p.upc FROM products AS p INNER JOIN
		upcLike AS u ON p.upc=u.upc WHERE
		u.likeCode=$l AND p.upc NOT IN (
		select upc from productUser)";
	$r  = $dbc->query($q);
	while($w = $dbc->fetch_row($r)){
		$ins = "INSERT INTO productUser (upc) VALUES ('{$w['upc']}')";
		$dbc->query($ins);
	}

	$up = sprintf("UPDATE productUser AS p INNER JOIN
		upcLike AS u ON p.upc=u.upc
		SET p.description=%s,
		p.brand=%s WHERE u.likeCode=%d",
		$dbc->escape($d),$dbc->escape($o),$l);
	$dbc->query($up);

}
fclose($fp);
unlink($tpath."lcimp.csv");

$page_title = "Fannie - Data import";
$header = "Upload Completed";
include($FANNIE_ROOT."src/header.html");

echo "Data import complete<p />";

include($FANNIE_ROOT."src/footer.html");

?>
