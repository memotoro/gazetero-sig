<?php

/******************************************************************************
 *
 * Purpose: Function for easy includes in php scripts
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

// if no $$filesname specified, only the jquery file is return
// else, the jqueryfile and the others specified...
// for instance : "blockUI" will return the string for "jquery-1.1.3.1.pack.js" and "jquery.blockUI.js"
function getJQueryFiles($filesname = null) {
	$jqueryFiles = scandirByExt($_SESSION["PM_INCPHP"] . "/../" . $_SESSION["PM_JAVASCRIPT"] . "/jquery", "js");
	$jsFiles = "";
	sort($jqueryFiles);
	$urlReqDir = getURLReqDir();
	foreach ($jqueryFiles as $jqf) {
		if (strpos($jqf, "jquery-") === 0) { 
			$urlJS = $urlReqDir . "../../" . $_SESSION["PM_JAVASCRIPT"];
			$jsFiles .= "<script type=\"text/javascript\" src=\"" . $urlJS . "/jquery/$jqf\"></script>\n";
		}
		if ($filesname) {
			foreach($filesname as $filename) {
				if (strpos($jqf, "jquery." . $filename) === 0) { 
					$urlJS = $urlReqDir . "../../" . $_SESSION["PM_JAVASCRIPT"];
					$jsFiles .= "<script type=\"text/javascript\" src=\"" . $urlJS . "/jquery/$jqf\"></script>\n";
				}
			}
		}
	}
	return $jsFiles;
}

function getURLReqDir() {
	$urlReqDir = $_ENV["REQUEST_URI"];
	if (strrpos($urlReqDir, "/") > 0) {
		$urlReqDir = substr($urlReqDir, 0, strrpos($urlReqDir, "/") + 1);
	}
	return $urlReqDir;
}

function getCSSReference($prefixDir) {
	$ret = "";
	$cssFiles = scandirByExt($_SESSION['PM_CONFIG_DIR'], "css");
	if (count($cssFiles) > 0) {
    	foreach ($cssFiles as $cf) {
        	$ret .= " <link rel=\"stylesheet\" href=\"" . $prefixDir . "config/". $_SESSION['PM_CONFIG_LOCATION'] ."/$cf\" type=\"text/css\" />\n";
		}
	}

	//- from config/common dir
	if (file_exists($_SESSION['PM_BASECONFIG_DIR'] . "/common")) {
		$cssFilesCommon = scandirByExt($_SESSION['PM_BASECONFIG_DIR'] . "/common", "css");
	    if (count($cssFilesCommon) > 0) {
			foreach ($cssFilesCommon as $cf) {
	        	$ret .= " <link rel=\"stylesheet\" href=\"" . $prefixDir . "config/common/$cf\" type=\"text/css\" />\n";
			}
		}
	}
	return $ret;
}

?>