<?php
/*******************************************************************************

    Copyright 2013 Whole Foods Co-op, Duluth, MN

    This file is part of Fannie.

    IT CORE is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    IT CORE is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include_once(dirname(__FILE__).'/../../classlib2.0/item/ItemModule.php');

class AllLanesItemModule extends ItemModule {

	function ShowEditForm($upc){
		global $FANNIE_LANES;
		$upc = str_pad($upc,13,0,STR_PAD_LEFT);
		$queryItem = "SELECT * FROM products WHERE upc = ?";

		$ret = '<fieldset id="AllLanesFieldset">';
		$ret .= '<legend>Lane Status</legend>';
		
		for($i=0;$i<count($FANNIE_LANES);$i++){
			$f = $FANNIE_LANES[$i];
			$sql = new SQLManager($f['host'],$f['type'],$f['op'],$f['user'],$f['pw']);
			if ($sql === False){
				$ret .= "Can't connect to :ane ".($i+1)."<br />";
				continue;
			}
			$prep = $sql->prepare_statement($queryItem);
			$resultItem = $sql->exec_statement($prep,array($upc));
			$num = $sql->num_rows($resultItem);

			if ($num == 0){
				$ret .= "Item <span style=\"color:red;\">$upc</span> not found on Lane ".($i+1)."<br />";
			}
			else if ($num > 1){
				$ret .= "Item <span style=\"color:red;\">$upc</span> found multiple times on Lane ".($i+1)."<br />";
				while ($rowItem = $sql->fetch_array($resultItem)){
					$ret .= "{$rowItem['upc']} {$rowItem['description']}<br />";
				}
			}
			else {
				$rowItem = $sql->fetch_array($resultItem);
				$ret .= "Item <span style=\"color:red;\">$upc</span> on Lane ".($i+1)."<br />";
				$ret .= "Price: {$rowItem['normal_price']}";
				if ($rowItem['special_price'] <> 0){
					$ret .= "&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:green;\">ON SALE: {$rowItem['special_price']}</span>";
				}
				$ret .= "<br />";
			}
			if ($i < count($FANNIE_LANES) - 1){
				$ret .= "<hr />";
			}
		}
		$ret .= '</fieldset>';
		return $ret;
	}
}

?>
