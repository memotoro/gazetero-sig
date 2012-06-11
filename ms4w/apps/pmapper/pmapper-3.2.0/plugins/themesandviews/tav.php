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

require_once("../../incphp/group.php");
session_start();
require_once($_SESSION["PM_INCPHP"] . "/globals.php");
require_once($_SESSION["PM_INCPHP"] . "/common.php");


class ThemeOrView 
{
	public $name;
	public $description;
	public $type;
	public $layers;
	public $minx;
	public $miny;
	public $maxx;
	public $maxy;
	public $imgUrl;
	
	public function __construct($name, $description, $type) {
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->layers = Array();
	}
	
	public function addLayer($name, $opacity = "map") {
		$opacityTmp = "map";
		if ($opacity && ($opacity != "map")) {
			if (is_numeric(opacity)) {
				if ( (0 <= $opacity) && ($opacity <= 100)) {
					$opacityTmp = $opacity;
				}
			}
		}
		$layer = array("name" => $name, "opacity" => $opacityTmp);
		$this->layers[] = $layer;
	}
}

// return the list of theme or view (or twice), with layers list or not
function returnListThemesAndViews($doThemes, $doViews, $loadLayers) {
	$listThemesAndViews = Array();
	if ($doThemes || $doViews) {
		$allGroups = $_SESSION["allGroups"];
		$xmlfileThemesAndViewsFile = $_SESSION["tavFile"];
		$listThemes = Array();
		$listViews = Array();
		if (file_exists($xmlfileThemesAndViewsFile)) {
	    	$xmlThemesAndViews = simplexml_load_file($xmlfileThemesAndViewsFile);
		    foreach ($xmlThemesAndViews->themeorview as $xmlThemeOrView) {
		    	if ($xmlThemeOrView->name && $xmlThemeOrView->description && $xmlThemeOrView->type) {
		    		if ( ($xmlThemeOrView->type == "Theme") || ($xmlThemeOrView->type == "View") ) {
		    			$themeOrView = new ThemeOrView((string) $xmlThemeOrView->name, (string) $xmlThemeOrView->description, (string)$xmlThemeOrView->type);
				    	if ($loadLayers && $xmlThemeOrView->layers) {
				    		foreach ($xmlThemeOrView->layers->layer as $xmlLayer) {
					    		if (in_array((string) $xmlLayer->name, $allGroups)) {
					    			$layerInserted = false;
				    				if ($xmlLayer->opacity) {
				    					if ( (0 <= (integer)$xmlLayer->opacity) && ((integer)$xmlLayer->opacity <= 100)) { 
				    						$themeOrView->addLayer((string) $xmlLayer->name, (integer) $xmlLayer->opacity);
				    						$layerInserted = true;
				    					}
				    				}
					    			if (!$layerInserted) {
					    				$themeOrView->addLayer((string) $xmlLayer->name, "map");
					    			}
					    		}
				    		}
				    	}
			    		if ($xmlThemeOrView->imgUrl) {
			    			$themeOrView->$imgUrl = (string) $xmlThemeOrView->imgUrl;
			    		}
				    	if ($doThemes && ( (string)$xmlThemeOrView->type == "Theme")) {
				    		$listThemes[] = $themeOrView;
				    	}
				    	if ($doViews && ( (string)$xmlThemeOrView->type == "View")) {
				    		if ($xmlThemeOrView->extent) {
					    		if ($xmlThemeOrView->extent->minx && $xmlThemeOrView->extent->miny && $xmlThemeOrView->extent->maxx && $xmlThemeOrView->extent->maxy) {
					    			$themeOrView->minx = (double)$xmlThemeOrView->extent->minx;
						    		$themeOrView->miny = (double)$xmlThemeOrView->extent->miny;
						    		$themeOrView->maxx = (double)$xmlThemeOrView->extent->maxx;
						    		$themeOrView->maxy = (double)$xmlThemeOrView->extent->maxy;
					    		}
				    		}
							$listViews[] = $themeOrView;
				    	}
		    		}
		    	}
		    }
		}
		if ($doThemes && $doViews) {
			$listThemesAndViews = array_merge($listThemes, $listViews);
		}
		else if ($doThemes) {
			$listThemesAndViews = array_merge($listThemesAndViews, $listThemes);
		} else if ($doViews) {
			$listThemesAndViews = array_merge($listThemesAndViews, $listViews);
		}
	}
	return $listThemesAndViews;
}

// Generates HTML boxes
function returnComboThemesAndViews($arrayOfThemesAndViews, $fctOnChange) {
	$cboxStr = "";
	$cboxStr .= "<select name=\"selgroup\"";
	if (strlen($fctOnChange) > 0) {
		$cboxStr .= " onchange=\"" . $fctOnChange . "\"";
	}
	$cboxStr .= ">";
    
    $cboxStr .= "<option value=\"\">&nbsp;</option>";
    foreach ($arrayOfThemesAndViews as $themeOrView) {
    	$cboxStr .= "<option value=\"" . $themeOrView->name . "\" >";
   		$cboxStr .= $themeOrView->description;
    	$cboxStr .= "</option> ";
    }
    $cboxStr .= "</select>";
    
    return $cboxStr;
}

// Generates HTML form with boxes
/* $namePartial = "Theme" or "View" */
function returnFormComboForWin($namePartial, $array) {
	$cboxStr = "";
	$cboxStr .= "<form id=\"show" . $namePartial . "sBoxForm\" class=\"tavShowBoxForm\" action=\"\">\n<div class=\"selectbox\">\n";
	$cboxStrTmp = returnComboThemesAndViews($array, "submitShow" . $namePartial . "Box()");
	$cboxStr .= (strlen($cboxStrTmp) > 0) ? _p("Show " . strtolower($namePartial)) . " " . $cboxStrTmp : "\n";
	$cboxStr .= "</div>\n</form>\n";
	return $cboxStr;
}

// return list of layers with transparency / opacity for the specified theme or view
function returnListLayers($tavName, $tavIsTheme) {
	$layers = Array();

	if (tavName) {
		$xmlfileThemesAndViewsFile = $_SESSION["tavFile"];
		if (file_exists($xmlfileThemesAndViewsFile)) {
	    	$xmlThemesAndViews = simplexml_load_file($xmlfileThemesAndViewsFile);
		    foreach ($xmlThemesAndViews->themeorview as $xmlThemeOrView) {
		    	if ($xmlThemeOrView->type) {
		    		if ($tavIsTheme ? $xmlThemeOrView->type == "Theme" : $xmlThemeOrView->type == "View") {
		    			if ($xmlThemeOrView->name) {
		    				if (((string) $xmlThemeOrView->name) == $tavName) {
			    				if ($xmlThemeOrView->layers) {
									$allGroups = $_SESSION["allGroups"];
			    					foreach ($xmlThemeOrView->layers->layer as $xmlLayer) {
							    		if (in_array((string) $xmlLayer->name, $allGroups)) {
							    			$layer = Array();
							    			$layer["name"] = (string) $xmlLayer->name;
							    			$layer["opacity"] = "map";
						    				if ($xmlLayer->opacity) {
												$valTmp = (string)$xmlLayer->opacity;
												if (is_numeric($valTmp)) {
													if ( (0 <= (integer)$xmlLayer->opacity) && ((integer)$xmlLayer->opacity <= 100)) { 
														$layer["opacity"] = (integer) $xmlLayer->opacity;
													}
						    					}
					    					}
						    				$layers[] = $layer;
						    			}
						    		}
					    		}
		    				}
				    	}
		    		}
		    	}
		    }
		}
	}
	return $layers;
}

// extent of specified view
function returnExtent($tavName) {
	$extent = Array();

	if ($tavName) {
		$xmlfileThemesAndViewsFile = $_SESSION["tavFile"];
		if (file_exists($xmlfileThemesAndViewsFile)) {
	    	$xmlThemesAndViews = simplexml_load_file($xmlfileThemesAndViewsFile);
		    foreach ($xmlThemesAndViews->themeorview as $xmlThemeOrView) {
		    	if ($xmlThemeOrView->type) {
		    		if ($xmlThemeOrView->type == "View") {
		    			if ($xmlThemeOrView->name) {
		    				if (((string) $xmlThemeOrView->name) == $tavName) {
					    		if ($xmlThemeOrView->extent) {
						    		if ($xmlThemeOrView->extent->minx && $xmlThemeOrView->extent->miny && $xmlThemeOrView->extent->maxx && $xmlThemeOrView->extent->maxy) {
						    			$extent["minx"] = (double)$xmlThemeOrView->extent->minx;
							    		$extent["miny"] = (double)$xmlThemeOrView->extent->miny;
							    		$extent["maxx"] = (double)$xmlThemeOrView->extent->maxx;
							    		$extent["maxy"] = (double)$xmlThemeOrView->extent->maxy;
						    		}
					    		}
		    				}
				    	}
		    		}
		    	}
		    }
		}
	}
	return $extent;
}

?>