<?php

/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin initialisation
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

// paramters for themes :
$_SESSION["tavThemesBoxType"] = isset($ini["tavThemesBoxType"]) ? $ini["tavThemesBoxType"] : 0;		
$_SESSION["tavThemesBoxContainer"] = isset($ini["tavThemesBoxContainer"]) ? $ini["tavThemesBoxContainer"] : "mapNorth";		
$_SESSION["tavThemesKeepSelected"] = isset($ini["tavThemesKeepSelected"]) ? $ini["tavThemesKeepSelected"] : false;		

// paramters for views :
$_SESSION["tavViewsBoxType"] = isset($ini["tavViewsBoxType"]) ? $ini["tavViewsBoxType"] : 0;		
$_SESSION["tavViewsBoxContainer"] = isset($ini["tavViewsBoxContainer"]) ? $ini["tavViewsBoxContainer"] : "mapNorth";		
$_SESSION["tavViewsKeepSelected"] = isset($ini["tavViewsKeepSelected"]) ? $ini["tavViewsKeepSelected"] : false;		

// paramters for default theme or view to load :
$_SESSION["tavSetDefault"] = isset($ini["tavSetDefault"]) ? $ini["tavSetDefault"] : "none";
if ($_SESSION["tavSetDefault"] != "theme" && $_SESSION["tavSetDefault"] != "view") {
	$_SESSION["tavSetDefault"] = "none";
} else {
	$_SESSION["defGroups"] = Array();
}
$_SESSION["tavDefaultCodeValue"] = isset($ini["tavDefaultCodeValue"]) ? $ini["tavDefaultCodeValue"] : "";		

// theme and view  file :
if (isset($ini["tavFile"])) {
	$fileTmp = $ini['tavFile'];
	if ($fileTmp{0} == "/" || $fileTmp{1} == ":") {
		$_SESSION["tavFile"] = $fileTmp;
	} else {
		$_SESSION["tavFile"] = $_SESSION["PM_CONFIG_DIR"] . "/" . $fileTmp;
	}
} else {
	$_SESSION["tavFile"] = $_SESSION["PM_CONFIG_DIR"] . "/themesandviews.xml";
}
$_SESSION["tavFile"] = str_replace('\\', '/', $_SESSION["tavFile"]); 		

//$buttons = array_merge($buttons, array ("themesbox" => array(_p("Apply theme"), "0")));
//$buttons = array_merge($buttons, array ("viewsbox" => array(_p("Apply view"), "0")));
//$buttons = array_merge($buttons, array ("themesandviewsdynwin" => array(_p("Themes and views"), "themesandviewsdynwin_click")));
//$buttons = array_merge($buttons, array ("themesandviewswindow" => array(_p("Themes and views"), "themesandviewswindow_click")));
//$buttons = array_merge($buttons, array ("themesandviewsframe" => array(_p("Themes and views"), "themesandviewsframe_click")));
//$buttons = array_merge($buttons, array ("themesandviewsauto" => array(_p("Themes and views"), "themesandviewsauto_click")));
?>