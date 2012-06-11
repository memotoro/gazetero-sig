<?php

/******************************************************************************
 *
 * Purpose: Extension of transparency plugins
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once("../../incphp/group.php");
session_start();

$grouplist = $_SESSION["grouplist"];

$js = "";

// use opacity or transparency (defined in the ini config file) ?
$js .= "
var transp2UseOpacity = " . ($_SESSION["transp2UseOpacity"] ? "true" : "false") . ";
";

// generation of 2 arrays, for keeping sliders object in order to call the "setPosition" js function later
$js .= "
function transp2initArrays () {
";
$indice = 0;
if ($grouplist) {
	foreach ($grouplist as $grp){
		$grpName = $grp->getGroupName();
		if ($grpName) {
			$js .= "
	if (typeof(transp2groupnames[" . $indice . "]) == 'undefined') {
		transp2groupnames[" . $indice . "] = '" . $grpName ."';
	}
	if (typeof(transp2sliders[" . $indice . "]) == 'undefined') {
		transp2sliders[" . $indice . "] = null;
	}";
			$indice++;
		}
	}
}
$js .= "
}
";

// function to associate with each slider to update group transparency / opacity value
if ($grouplist) {
	foreach ($grouplist as $grp){
		$grpName = $grp->getGroupName();
		$glayerList = $grp->getLayers();
		
		$js .= "
function setTransparencyFor_" . $grpName . "(sliderPosition) {
	setGroupTransparency(sliderPosition, \"" . $grpName . "\");
}
	";
	}
}

if (isset($js)) {
	if ($js != "") {
		echo $js;
	}
}

?>