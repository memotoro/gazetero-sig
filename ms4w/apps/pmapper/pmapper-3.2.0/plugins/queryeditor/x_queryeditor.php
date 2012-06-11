<?php

/******************************************************************************
 *
 * Purpose: AJAX server part of the statistic plugin for p.mapper 
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
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

require_once("../../incphp/group.php");

session_start();
require_once($_SESSION['PM_INCPHP'] . "/common.php");
require_once($_SESSION['PM_INCPHP'] . "/globals.php");
require_once($_SESSION['PM_INCPHP'] . "/query/squery.php");
require_once($_SESSION['PM_INCPHP'] . "/query/search.php");

$operation = $_REQUEST['operation'];

/**
 * Return name and header fields for the pmapper layer specified
 * 
 * The result array contain:
 * - string lists (use valuesToUseTxt and valuesToShowTxt keys)
 * - arrays (use valuesToUse and valuesToShow keys)
 */
function getAttributsRealAndReadNames($layerName) {
	$retVal = Array(); 
	if (strlen($layerName) > 0) {
		$grouplist = $_SESSION["grouplist"];
		if ($grouplist) {
			if (array_key_exists($layerName, $grouplist)) {
				$group = $grouplist[$layerName];
				if ($group) {
					$valuesToShow = $group->selHeaders;
					$layers = $group->layerList;
					if ($layers) {
						$firstLayer = $layers[0];
						if ($firstLayer) {
							$valuesToUse = $firstLayer->selFields;
						}
					}
				}
			}
		}
	}
	$valuesToUseTxt = implode(',', $valuesToUse);
	$valuesToShowTxt = implode(',', $valuesToShow);
	$retVal["valuesToUseTxt"] = $valuesToUseTxt; 
	$retVal["valuesToShowTxt"] = $valuesToShowTxt;
	$retVal["valuesToUse"] = $valuesToUse; 
	$retVal["valuesToShow"] = $valuesToShow;
	return $retVal;	
}

// Request = ask for fields list
if ($operation == "getattributs") {
	$layerName = $_REQUEST["layername"];
	$attributs = getAttributsRealAndReadNames($layerName);
	echo "{'fields':'" . $attributs["valuesToUseTxt"] . "','headers':'" . $attributs["valuesToShowTxt"] . "'}";

// Request = execute query 
} else if ($operation == "query") {

	$layerName = $_REQUEST["layername"];
	$_REQUEST["layerName"] = $layerName;
	$_REQUEST["externalSearchDefinition"] = true;
	$_REQUEST["mode"] = $_REQUEST["search"];
	$mapLayer = $map->getLayerByName($layerName);

	if ($mapLayer) {
		$layerType = $mapLayer->connectiontype;
		// Query received from the editor without modification:
		$originalQuery = $_REQUEST["query"];
		// Query to execute:
		$modifiedQuery = "";
		// Query with the real fields names instead of headers:
		$modifiedQueryWithRealNames = $originalQuery;
		$attributs = getAttributsRealAndReadNames($layerName);
		foreach ($attributs["valuesToUse"] as $indice => $valueToUse) {
			$valueToShow = $attributs["valuesToShow"][$indice];
			$modifiedQueryWithRealNames = str_replace("[" . $valueToShow . "]", "[" . $valueToUse . "]", $modifiedQueryWithRealNames);
		}
		// First field :
		if (preg_match("/\[([^\]]*)\]/", $modifiedQueryWithRealNames, $attributs)) {
			$firstFld = $attributs[1];
		} else {
			$firstFld = "";
		}
		// end of lines :
		$modifiedQueryWithoutEOL = str_replace("\n", " ", $modifiedQueryWithRealNames);
		// SHP :
		if ($layerType == 1) {
			$_REQUEST["layerType"] = "shape";
			$_REQUEST["firstFld"] = $firstFld;
			$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*\\\\'([^\\\]*)\\\\'/", "$1 \"[$2]\" $3 /'$4'/", $modifiedQueryWithoutEOL);
			$modifiedQueryTmp = str_replace(" LIKE ", " =~ ", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("/'%", "/", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("%'/", "/", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("/'", "/^", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("'/", "$/", $modifiedQueryTmp);
//			$modifiedQueryTmp = str_replace("%", ".*", $modifiedQueryTmp);
			if ($modifiedQueryTmp) {
				$modifiedQuery = "((" . $modifiedQueryTmp . "))";
			}
		// PostGIS :
		} else if ($layerType == 6) {
			$_REQUEST["layerType"] = "postgis";
			$_REQUEST["firstFld"] = $firstFld;
			$modifiedQueryTmp = preg_replace("/([^\[]*)\[([^\]]*)\]\s*([^\s]*)\s*\\\\'([^\\\]*)\\\\'/", "$1 $2 $3 /'$4'/", $modifiedQueryWithoutEOL);
			$modifiedQueryTmp = str_replace(" LIKE ", " ~* ", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("/'%", "'", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("%'/", "'", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("/'", "'^", $modifiedQueryTmp);
			$modifiedQueryTmp = str_replace("'/", "$'", $modifiedQueryTmp);
//			$modifiedQueryTmp = str_replace("%", ".*", $modifiedQueryTmp);
			if ($modifiedQueryTmp) {
				$modifiedQuery = $modifiedQueryTmp;
			}
		}
		header("Content-type: text/plain; charset=$defCharset");
		// Execute query :
		if ($modifiedQuery) {
			$mapLayerFilter = $mapLayer->getFilter();
			if ($mapLayerFilter) {
				$modifiedQuery = $mapLayerFilter . " AND " . $modifiedQuery;
			}
			$_REQUEST["qStr"] = $modifiedQuery;
			$mapQuery = &new Query($map);
			$mapQuery->q_processQuery();
			$queryResult = $mapQuery->q_returnQueryResult();
			//$numResultsTotal = $mapQuery->q_returnNumResultsTotal();
		}

		echo "{'mode':'$mode', 'queryResult':$queryResult}";
	}	
}

//exit();
?>