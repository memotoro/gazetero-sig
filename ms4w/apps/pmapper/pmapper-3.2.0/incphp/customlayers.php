<?php

/******************************************************************************
 *
 * Purpose: add dynamic custom layers to map object
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


class URL_Layer
{

    function URL_Layer($map, $mapImg=false)
    {
        $this->map = $map;
        $this->mapImg = $mapImg;
        
        $this->url_createLayer();
    
    
    }
    
    function url_createLayer()
    {
        
        $txtLayer = ms_newLayerObj($this->map);
        $txtLayer->set("name", "url_txtlayer");
        $txtLayer->set("type", 0);
        $txtLayer->set("status", MS_ON);
        
        $url_points = $_SESSION['url_points'];
        
        foreach ($url_points as $upnt) {
            // Create line, add xp point, create shape and add line and text, add shape to layer
            //$pointList = explode(",", $f);
            $px  = $upnt[0];
            $py  = $upnt[1];
            $txt = $upnt[2];
            
            $newLine = ms_newLineObj();
            $newLine->addXY($px, $py);
            
            $newShape = ms_newShapeObj(0);
            $newShape->add($newLine);
            $newShape->set("text", $txt);
            $txtLayer->addFeature($newShape);
            
            // Class properties
            $pntClass = ms_newClassObj($txtLayer);
            $clStyle = ms_newStyleObj($pntClass);
            $clStyle->color->setRGB(0, 0, 255);
            $clStyle->outlinecolor->setRGB(255, 0, 0);
            $clStyle->set("symbolname", "circle");
            $symSize = 10;
            $clStyle->set("size", $symSize);
            
            // Label properties
            //$pntClass->label->set("position", MS_UR);
            $pntClass->label->set("position", MS_AUTO);
            //$pntClass->label->set("size", "small");
            $pntClass->label->set("font", "FreeSans");
            $pntClass->label->set("type", MS_TRUETYPE);
            $pntClass->label->set("size", 8);
            $pntClass->label->set("antialias", MS_FALSE);
            $pntClass->label->set("buffer", 2);
            $pntClass->label->set("wrap", ",");
            $pntClass->label->color->setRGB(0, 0 , 0);
            $pntClass->label->backgroundcolor->setRGB(255, 255, 210);
            $pntClass->label->backgroundshadowcolor->setRGB(170, 170 , 170);
            $pntClass->label->set("backgroundshadowsizex", 2);
            $pntClass->label->set("backgroundshadowsizey", 2);
            //$pntClass->label->set("force", MS_TRUE);
        
        }
    
    }

}


class WMS_Client
{
    function WMS_Client($map, $init, $query=false, $urlinit=false)
    {
        $this->map = $map;
        $this->wms_connectiontimeout = "20";
        
        $this->wmsc_addLayersToMap($init, $query, $urlinit);
    }
    
    
   /**
    * Add the WMS layers to the map
    * read properties from session 
    * 
    * @init: specify if layer is initialized fro the first time
    */
    function wmsc_addLayersToMap($init, $query, $urlinit)
    {
        if (isset($_SESSION['wms_layers'])) {
            $wms_layers = $_SESSION['wms_layers'];
            if (count($wms_layers) > 0) {
                $allGroups = $_SESSION['allGroups0'];
                
                foreach($wms_layers as $k => $wl) {
                    $connection = (!$query ? $wl['connection'] : $wl['connection'] . "QUERY_LAYERS=" . strtoupper($wl['wms_name']));
                    error_log("$query  $connection");
                    $wmsLayerName = "dyn_wms_layer_$k";
                    $wmsLayer = ms_newLayerObj($this->map);
                    $wmsLayer->set("name",  $wmsLayerName);
                    $wmsLayer->set("type",  "RASTER");
                    $wmsLayer->set("status", MS_OFF);
                    $wmsLayer->set("connectiontype",  MS_WMS);
                    $wmsLayer->set("connection",  $connection);
                    if (preg_match('/epsg/', $wl['wms_srs'])) {
                        $wmsLayer->setProjection("+init=" . $wl['wms_srs']);
                    }
                    
                    // set WMS metadata
                    $wmsLayer->setMetaData("wms_server_version", $wl['wms_server_version']); //"1.1.1";
                    $wmsLayer->setMetaData("wms_connectiontimeout", $this->wms_connectiontimeout);
                    $wmsLayer->setMetaData("wms_name", $wl['wms_name']);
                    $wmsLayer->setMetaData("wms_format", $wl['wms_format']);
                    $wmsLayer->setMetaData("wms_transparent", $wl['wms_transparent']);
                    $wmsLayer->setMetaData("wms_style", $wl['wms_style']);
                    $wmsLayer->setMetaData("wms_srs", strtoupper($wl['wms_srs']));
                    
                    $wmsLayer->setMetaData("DESCRIPTION", $wl['title']);

                    // add to allGroups 
                    //$groups = $_SESSION["groups"];
                    //$groups[] = $wmsLayerName;
                    $allGroups[] = $wmsLayerName;
                    
                }
                
                
                if ($init) {
                    // if categories used, add layer to category "cat_dynwms"
                    if ($_SESSION['useCategories']) {
                        $categories = $_SESSION['categories'];
                        if (!array_key_exists('cat_dynwms', $categories)) {
                            $categories['cat_dynwms'] = array();
                        }
                        $categories['cat_dynwms'][] = $wmsLayerName;
                        $_SESSION['categories'] = $categories;
                    }
                    
                    $wmsUrlEnc = "&wmslayers=";
                    $u = 0;
                    // Create string for URL encoding of WMS layer properties
                    foreach($wms_layers as $k => $wl) {
                        $sep = $u < 1 ? "" : "|";
                        $wms4url = "";
                        foreach($wl as $k => $v) {
                            $wms4url .= "$k=$v||";
                        }
                        $wms4url = substr($wms4url, 0, -2);
                        $wmsUrlEnc .= $sep . urlencode(base64_encode($wms4url));
                        $u++;
                    }
                    
                    $_SESSION['wmsUrlEnc'] = $wmsUrlEnc;
                    
                    
                    // Re-initialize Groups
                    require_once("initgroups.php");
                    //$_SESSION["groups"] = $groups;
                    $_SESSION['allGroups'] = $allGroups;
                    $iG = new Init_groups($this->map, $allGroups, $_SESSION['gLanguage'], 1);
                    
                }
            }
        }
        
        
        
        //$this->map->save("d:/webdoc/tmp/saved.map");
        
    }  

    
}


?>
