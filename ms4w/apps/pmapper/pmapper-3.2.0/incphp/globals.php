<?php

/******************************************************************************
 *
 * Purpose: globally used variables
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

// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();


// Set character set for correct display of special characters
$defCharset = $_SESSION['defCharset'];


/**
 * LANGUAGE
 */
$gLanguage = $_SESSION["gLanguage"];
include_once($_SESSION['PM_INCPHP'] . "/locale/language_" . $gLanguage . ".php");



/**
 * LOAD MAPSCRIPT MODULE
 */
$msVersion = $_SESSION['msVersion']; 
if (!extension_loaded('MapScript')) {
    dl("php_mapscript$msVersion." . PHP_SHLIB_SUFFIX);
}



/**
 * INITIALIZE MAP
 */
$PM_MAP_FILE = $_SESSION['PM_MAP_FILE'];
$map = ms_newMapObj($PM_MAP_FILE);
//$mapTmpFile = $_SESSION['web_imagepath'] . session_id() . ".map";
//$map->save($mapTmpFile);



/** ========== DEPRECATED ==============
 * DEFINE ZOOM STEPS FOR ZOOM SLIDER 
 */
//$gSlide = preg_split('/[\s,]+/', $ini["gSlide"]);




?>