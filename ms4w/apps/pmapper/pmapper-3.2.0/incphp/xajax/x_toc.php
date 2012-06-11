<?php

/******************************************************************************
 *
 * Purpose: create TOC/legend
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

require_once("../group.php");
session_start();

require_once("../common.php");
require_once("../globals.php");
require_once("../legend.php");
require_once("../custom.php");

$scale            = $_SESSION['geo_scale'];
$defGroups        = $_SESSION['defGroups'];
$useCategories    = $_SESSION['useCategories'];
$layerAutoRefresh = $_SESSION['layerAutoRefresh'];

if (isset($_REQUEST['legendonly'])) {
    $legendonly = 1; 
    if (isset($_REQUEST['swaplegend'])) {
        $method = "swapLegend";
    } else {
        $method = "showMapLegend";
    }
} else {
    $legendonly = 0;
    $method = "updateToc";
}

$categories = $useCategories ? $_SESSION['categories'] : false;

$toc = new Legend($map, $categories, $legendonly);
$tocList = $toc->writeToc();
$tocHTML = $tocList['toc'];
$tocButtons = $tocList['tocbut'];
//printDebug($tocHTML);

header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{tocHTML:'$tocHTML', tocButtons:'$tocButtons'}";

?>
