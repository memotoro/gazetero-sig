<?php

/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
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

/******************************************************************************
 * Generation of the boxes witch permit to apply a Theme or a View
 ******************************************************************************/
require_once("tav.php");

header("Content-Type: text/plain; charset=$defCharset");

$selStr = "";
if ($_REQUEST["type"] == "Theme") {
	$themes = returnListThemesAndViews(true, false, false);

	// Print combo box with all themes
	$selStr = "<form id=\"selThemesBoxForm\" action=\"\"><div class=\"selectbox\">";
	if (count($themes) > 0) {
	
	    $selStr .=  _p("Apply theme") . " ";
	    $selStr .= "";
	    $selStr .= returnComboThemesAndViews($themes, "submitSelThemeBox()");
	}
	$selStr .= "</div></form>";

} else if ($_REQUEST["type"] == "View") {
	$views = returnListThemesAndViews(false, true, false);

	// Print combo box with all views
	$selStr = "<form id=\"selViewsBoxForm\" action=\"\"><div class=\"selectbox\">";
	if (count($views) > 0) {
	
	    $selStr .=  _p("Apply view") . " ";
	    $selStr .= "";
	    $selStr .= returnComboThemesAndViews($views, "submitSelViewBox()");
	}
	$selStr .= "</div></form>";
}

// return JS object literals "{}" for XMLHTTP request 
$selStr = addcslashes($selStr, "'");
echo "{selStr:'$selStr'}";
?>