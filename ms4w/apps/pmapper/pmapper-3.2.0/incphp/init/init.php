<?php

/******************************************************************************
 *
 * Purpose: Initialize application parameters; calls initmap.php
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
 *
 * This file is part of p.mapper.
 *
 * p.mapper is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * p.mapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

/**
 * Initialize settings (using "initmap.php")
 */
$initMap = new Init_map($map, $PM_MAP_FILE, $ini, $gLanguage);
$initMap->initAllParameters();

$infoWin = $_SESSION['infoWin'];
$enableRightMousePan = $_SESSION['enableRightMousePan'];

$mapW = $map->width; //540;
$mapH = $map->height; //430;
$_SESSION["mapwidth"]  = $mapW;
$_SESSION["mapheight"] = $mapH;


/**
 * Reference map section
 */
$refmap = $map->reference;
$refW = $refmap->width;
$refH = $refmap->height;
$refImg = basename($refmap->image);


/**
 * p.mapper version
 */
$PM_VERSION = $ini["pmapper"];


/**
 * JS Arrays for legend/TOC
 */
$jsArrays = writeJSArrays();


/**
 * ZOOM TO PRE-DEFINED EXTENTS
 */
if (isset($_REQUEST["zoomLayer"])) {          // EXTENT READ FROM FEATURE
    $zoomLayer = $_REQUEST["zoomLayer"];
    $zoomQuery = $_REQUEST["zoomQuery"];
    $mapFrameURL = $initMap->getMapInitURL($map, $zoomLayer, $zoomQuery);
}


/**
 * Parameters for Slider
 */
$maxScale = ($ini['sliderMax'] == "max") ? $initMap->returnMaxScale($map, $mapH) : $ini['sliderMax'];
$minScale = $ini['sliderMin'];
$dgeo = $initMap->returnXYGeoDimensions();


/**
 * Get JS & CSS file references
 */
$jsReference  = $initMap->returnJSReference();
$jsConfigReference  = $initMap->returnJSConfigReference();
$cssReference = $initMap->returnCSSReference();
$jsInitFunctions = $initMap->returnjsInitFunctions();



/**
 * Set default for toolbar button directory
 */
if (!isset($toolbarTheme)) $toolbarTheme = "default";
if (!isset($toolbarImgType)) $toolbarImgType = "gif";

/**
 * Check for common php_config.file under /config/common/
 */
if (file_exists($PM_BASECONFIG_DIR . "/common/php_config.php")) {
    require_once($PM_BASECONFIG_DIR . "/common/php_config.php");
}

/**
 * Include files for plugins
 */
foreach ($_SESSION['plugin_phpFileList'] as $pf) {
    require_once($pf);
}




?>