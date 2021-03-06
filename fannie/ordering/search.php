<?php
/*******************************************************************************

    Copyright 2011 Whole Foods Co-op

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
include('../config.php');
include($FANNIE_ROOT.'src/mysql_connect.php');
?>
<style type="text/css">
a { color:blue; }
</style>
<script type="text/javascript" src="<?php echo $FANNIE_URL; ?>src/jquery/js/jquery-1.4.2.min.js">
</script>
<script type="text/javascript">
function setItem(upc){
	$('#newupc', window.opener.document).val(upc);	
	window.close();
}
function setOwner(cardno){
	$('#memNum', window.opener.document).val(cardno);
	window.opener.memNumEntered();
	window.close();
}
$(document).ready(function(){
	if ($('#q').length != 0)
		$('#q').focus();
});
</script>
<?php

if (isset($_REQUEST['q'])){
	echo '<a href="" onclick="$(\'#one\').show();$(\'#two\').hide();$(\'#three\').hide();return false;">Items</a>';	
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<a href="" onclick="$(\'#one\').hide();$(\'#two\').show();$(\'#three\').hide();return false;">Owners</a>';	
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<a href="" onclick="$(\'#one\').hide();$(\'#two\').hide();$(\'#three\').show();return false;">Brands</a>';	
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<input type="submit" onclick="location=\'search.php\'" value="Back" />';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<input type="submit" onclick="window.close();" value="Close" />';

	echo '<div id="one" style="display:block;">';
	$itemQ = sprintf("SELECT upc,description FROM products WHERE description LIKE %s
		ORDER BY description",$dbc->escape("%".$_REQUEST['q']."%"));
	$itemR = $dbc->query($itemQ);
	if ($dbc->num_rows($itemR) == 0)
		echo 'No matching items';
	else {
		echo '<ul>';
		while($itemW = $dbc->fetch_row($itemR)){
			printf('<li><a href="" onclick="setItem(\'%s\');return false;">%s</a></li>',
				$itemW['upc'],$itemW['description']);
		}
		echo '</ul>';
	}
	echo '</div>';

	echo '<div id="two" style="display:none;">';
	$memQ = sprintf("SELECT CardNo,FirstName,LastName FROM custdata WHERE LastName LIKE %s
		ORDER BY LastName,FirstName",$dbc->escape("%".$_REQUEST['q']."%"));
	$memR = $dbc->query($memQ);
	if ($dbc->num_rows($memR) == 0)
		echo 'No matching owners';
	else {
		echo '<ul>';
		while($memW = $dbc->fetch_row($memR)){
			printf('<li><a href="" onclick="setOwner(%d);return false;">%d %s, %s</a></li>',
				$memW['CardNo'],$memW['CardNo'],$memW['LastName'],$memW['FirstName']);
		}
		echo '</ul>';
	}
	echo '</div>';

	echo '<div id="three" style="display:none;">';
	$brandQ = sprintf("SELECT x.manufacturer FROM prodExtra AS x INNER JOIN products AS p ON
		x.upc=p.upc WHERE x.manufacturer LIKE %s GROUP BY x.manufacturer
		ORDER BY x.manufacturer",$dbc->escape("%".$_REQUEST['q']."%"));
	$brandR = $dbc->query($brandQ);
	if ($dbc->num_rows($brandR) == 0)
		echo 'No matching brands';
	else {
		echo '<ul>';
		while($brandW = $dbc->fetch_row($brandR)){
			printf('<li><a href="search.php?brand=%s">%s</a></li>',
				base64_encode($brandW['manufacturer']),
				$brandW['manufacturer']);
		}
		echo '</ul>';
	}
	echo '</div>';
}
else if (isset($_REQUEST['brand'])){
	$q = sprintf("SELECT p.upc,p.description FROM products AS p
		INNER JOIN prodExtra AS x ON p.upc=x.upc WHERE
		x.manufacturer=%s ORDER by p.description",
		$dbc->escape(base64_decode($_REQUEST['brand'])));
	$r = $dbc->query($q);
	printf("<b>%s items</b>",base64_decode($_REQUEST['brand']));
	echo '<ul>';
	while($itemW = $dbc->fetch_row($r)){
		printf('<li><a href="" onclick="setItem(\'%s\');return false;">%s</a></li>',
			$itemW['upc'],$itemW['description']);
	}
	echo '</ul>';
}
else {
	echo '<form action="search.php" method="get">
		<input type="text" name="q" id="q" />
		<input type="submit" value="Search" /> 
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" onclick="window.close();" value="Close" />
		</form>';
}

?>
