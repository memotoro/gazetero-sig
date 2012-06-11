<?php

/******************************************************************************
 *
 * Purpose: Common functions used in plugins
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
 * The software is distributed in the hope that it will be useful,
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
 * Return an array of layers, depending of the name of a layer or a group
 * It is possible to pass an array in parameter
 */
function getLayersByGroupOrLayerName($map, $groupOrLayer) {
	$mapLayers = Array();

	if ($map) {
		$manyGroupOrLayer = explode(",", $groupOrLayer);
		foreach($manyGroupOrLayer as $oneGroupOrLayer) {
			$oneGroupOrLayer = trim($oneGroupOrLayer);
			if ($oneGroupOrLayer) {
				// If we are searching a group (not a layer) :
				$mapLayersIndexes = $map->getLayersIndexByGroup($oneGroupOrLayer);
				if ($mapLayersIndexes) {
					foreach ($mapLayersIndexes as $iLayerIndex) {
						$mapLayer = $map->getLayer($iLayerIndex);
						if ($mapLayer) {
							$mapLayers[] = $mapLayer;
						}
					}
				} else {
					$mapLayer = @$map->getLayerByName($oneGroupOrLayer);
					if ($mapLayer) {
						$mapLayers[] = $mapLayer;
					}
				}
			}
		}
	}

	return $mapLayers;
}

/**
 * highlight object :
 * (from map.php : PMAP::pmap_addResultLayer)
 */
function addResultLayer($map, $reslayer, $shpindexes, $shptileindexes=-1) {
//    $qLayer = $map->getLayerByName($reslayer);
	$qLayers = getLayersByGroupOrLayerName($map, $reslayer);
	if (qLayers) {
		$qLayer = $qLayers[0];
		if ($qLayer) {
		    $qlayType = $qLayer->type;
		    $layNum = count($map->getAllLayerNames());
		
		    // TEST IF LAYER HAS THE SAME PROJECTION AS MAP
		    $mapProjStr = $map->getProjection();
		    $qLayerProjStr = $qLayer->getProjection();
		
		    if ($mapProjStr && $qLayerProjStr && $mapProjStr != $qLayerProjStr) {
		        $changeLayProj = 1;
		        $mapProjObj = ms_newprojectionobj($mapProjStr);
		        $qLayerProjObj = ms_newprojectionobj($qLayerProjStr);
		    }
		
		    // NEW RESULT LAYER
		    if ($_SESSION['PM_HL_MAP_FILE']) {
		        // load from template map file
		        $hlDynLayer = 0;
		        $hlMap = ms_newMapObj($_SESSION['PM_HL_MAP_FILE']);
		        $hlMapLayer = $hlMap->getLayerByName("highlight_$qlayType");
		        $newResLayer = ms_newLayerObj($map, $hlMapLayer);
		        
		    } else {
		        // create dynamically
		        $hlDynLayer = 1;
		        $newResLayer = ms_newLayerObj($map);
		        $newResLayer->set("name", "reslayer");
		        if ($qlayType == 0) {
		            $newResLayer->set("type", 0);  // Point for point layer
		        } elseif ($qlayType == 1 || $qlayType == 2) {
		            $newResLayer->set("type", 1);  // Line for line && polygon layers
		        }
		        ##$newResLayer->set("type", $qlayType);  // Take always same layer type as layer itself
		    }
		
		    // ADD SELECTED SHAPE TO NEW LAYER
		    //# when layer is an event theme
		    if ($qLayer->getMetaData("XYLAYER_PROPERTIES") != "") {
		        foreach ($shpindexes as $cStr) {
		            $cList = preg_split('/@/', $cStr);
		            $xcoord = $cList[0];
		            $ycoord = $cList[1];
		            $resLine = ms_newLineObj();   // needed to use a line because only a line can be added to a shapeObj  
		            $resLine->addXY($xcoord, $ycoord);
		            $resShape = ms_newShapeObj(1);
		            $resShape->add($resLine);
		            $newResLayer->addFeature($resShape);
		        }
		    //# 'normal' layers
		    } else {
		        // Add selected shape to new layer
		        $qLayer->open();
		        foreach ($shpindexes as $resShpIdx) {
		            if (preg_match("/@/", $resShpIdx)) {
		                $idxList = explode("@", $resShpIdx);
		                $resTileShpIdx = $idxList[0];
		                $resShpIdx = $idxList[1];
		            } else {
		                $resTileShpIdx = $shptileindexes;
		            }
		
		            $resShape = $qLayer->getShape($resTileShpIdx, $resShpIdx);
		            
		            // Change projection to map projection if necessary
		            if ($changeLayProj) {
		                // If error appears here for Postgis layers, then DATA is not defined properly as:
		                // "the_geom from (select the_geom, oid, xyz from layer) AS new USING UNIQUE oid USING SRID=4258" 
		                $resShape->project($qLayerProjObj, $mapProjObj);
		            }
		            
		            $newResLayer->addFeature($resShape);
		        }
		        
		        $qLayer->close();
		    }
		    
		    
		    $newResLayer->set("status", MS_ON);
		    $newResLayerIdx = $newResLayer->index;
		
		    if ($hlDynLayer) {
		        // SELECTION COLOR
		        $iniClrStr = trim($_SESSION["highlightColor"]);
		        $iniClrList = preg_split('/[\s,]+/', $iniClrStr);
		        $iniClr0 = $iniClrList[0];
		        $iniClr1 = $iniClrList[1];
		        $iniClr2 = $iniClrList[2];
		    
		        // CREATE NEW CLASS
		        $resClass = ms_newClassObj($newResLayer);
		        $clStyle = ms_newStyleObj($resClass);
		        $clStyle->color->setRGB($iniClr0, $iniClr1, $iniClr2);
		        $clStyle->set("symbolname", "circle");
		        $symSize = ($qlayType < 1 ? 10 : 5);
		        $clStyle->set("size", $symSize);
		    }
		
		    // Move layer to top (is it working???)
		    //$layOrder = $map->getLayersDrawingOrder();
		    while ($newResLayerIdx < ($layNum-1)) {
		        $map->moveLayerUp($newResLayerIdx);
		    }
		}
	}
}

/**
 * Return groups available in array
 */
function getAvailableGroups($map, $onlyChecked, $onlyNonRasters, $onlyVisibleAtScale) {
	// only checked groups :
	if ($onlyChecked) {
		$groupsStep1 = $_SESSION["groups"];
	} else {
		$groupsStep1 = $_SESSION["allGroups"];
	}

	$groupsStep2 = Array();
	$scale = $_SESSION["geo_scale"];
	$grouplist = $_SESSION["grouplist"];

	foreach ($grouplist as $grp){
	    if (in_array($grp->getGroupName(), $groupsStep1, TRUE)) {
	        $glayerList = $grp->getLayers();
	        foreach ($glayerList as $glayer) {
	            $mapLayer = $map->getLayer($glayer->getLayerIdx());

	            $groupOk = true;

	            // no raster layers / groups :
	            if ($onlyNonRasters) {
	            	if ($mapLayer->type >= 3) {
	            		$groupOk = false;
	            	}
		        }

            	// only visible layers / groups depending on scale :
            	if ($onlyVisibleAtScale) {
            		if (checkScale($map, $mapLayer, $scale) <> 1) {
            			$groupOk = false;
            		}
            	}

            	if ($groupOk) {
                	$groupsStep2[] = $grp;
	                break;
	            }
	        }
	    }
	}
	
	return $groupsStep2; 
}


?>