<?php

/******************************************************************************
 *
 * Purpose: SearchTool plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
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

session_start();
require_once("../../incphp/util.php");

// Generate search form

$searchForm = "<form id=\"searchForm\" action=\"blank.html\" onsubmit=\"submitSearch()\" onkeypress=\"return disableEnterKey(event)\">\n";
$searchForm .= writeSearchContainer("inline") . "\n";
$searchForm .= "</form>\n";

header("Content-Type: text/plain");
echo $searchForm;

?>