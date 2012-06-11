<?php

/******************************************************************************
 *
 * Purpose: general printing functions
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


class PrintMap extends PMap
{
    var $map;   
    var $mapW;  
    var $mapH;  
    var $scale; 
    var $groups;
    var $imgUrlList;
    var $rStr;
    
    
    function PrintMap($map, $mapW, $mapH, $scale, $printType, $imgDPI, $imgFormat=false, $prefmap=true)
    {
        $this->map    = $map;
        $this->mapW   = $mapW;
        $this->mapH   = $mapH;
        $this->scale  = $scale;
        $this->groups = $_SESSION["groups"];
        
        // Check for custom layers
        $this->pmap_addCustomLayers();
        
        // Set active groups/layers
        setGroups($map, $this->groups, $scale, 1);
        
        // Check and if necessary add result layers to map
        $this->pmap_checkResultLayers();
        
        // Check for XY Layers (event layers)
        $existsXYLayer = ($_SESSION["existsXYLayer"] ? 1 : 0);
        
        // Set width and height
        $this->map->set("width", $this->mapW);
        $this->map->set("height", $this->mapH);
    
        // ZOOM TO PRE-DEFINED SCALE OR MAP EXTENT FROM SESSIONID
        $this->zoom2scale();
    
        
        // DEFINE SCALEBAR/REFERENCE-MAP IMG
        $sbarImg = $this->createScaleBar($printType, $imgDPI);
        $this->map->selectOutputFormat("jpeg");
        //$this->pmap_setImgFormat(true);
        $refImg = $this->map->drawReferenceMap();
        
        
        // CREATE MAP IMAGE AND PASTE SCALEBAR AND REFERENCE MAP
        switch ($printType) {
            // HTML OUTPUT
            case "html":
                $this->pmap_setImgFormat(true);
                $mapImg = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImg); 
                }
                
                //$mapImg->pasteImage($sbarImg, 0, 3, $this->mapH-25);
                //if ($prefmap) $mapImg->pasteImage($refImg, -1);
                //$this->imgUrlList[] = $mapImg->saveWebImage();
                $this->imgUrlList[] = mapSaveWebImage($this->map, $mapImg);
                $this->imgUrlList[] = mapSaveWebImage($this->map, $refImg, true);
                $this->imgUrlList[] = mapSaveWebImage($this->map, $sbarImg);
                $mapImg->free();
                
                $this->writePrintLegendHTML();
                
                break;
        
            // PDF OUTPUT
            case "pdf":
                // Increase size and resolution for better print quality (factor set in config.ini -> pdfres)
                // Note: resolution has to be increased, too, to keep scale dependency of layers
                $this->pmap_setImgFormat(true);
                $pdfres = $_SESSION["pdfres"];
                
                // Increase Label size according to magnificion for PDF output
                $this->increaseLabels($pdfres);
                
                $this->map->set("width", $this->mapW * $pdfres);
                $this->map->set("height", $this->mapH * $pdfres);
                $this->map->set("resolution", 96 * $pdfres);
                $mapImgHR = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImgHR); 
                }
                
                /*$this->imgUrlList[] = $mapImgHR->saveWebImage();
                $this->imgUrlList[] = $refImg->saveWebImage();
                $this->imgUrlList[] = $sbarImg->saveWebImage();*/
                
                $this->imgUrlList[] = mapSaveWebImage($this->map, $mapImgHR);
                $this->imgUrlList[] = mapSaveWebImage($this->map, $refImg, true);
                //$this->imgUrlList[] = $refImg->saveWebImage();
                $this->imgUrlList[] = mapSaveWebImage($this->map, $sbarImg);
    
                $mapImgHR->free();
                break;
        
            // DOWNLOAD HIGH RESOLUTION IMAGE
            case "dl":
                //$this->map->selectOutputFormat("jpeg");
                if ($imgFormat) {
                    $this->map->selectOutputFormat($imgFormat);  
                } else {
                    $this->pmap_setImgFormat(true);
                }
                
                // Increase Label size according to DPI
                $factor = round($imgDPI / 96);
                $this->increaseLabels($factor);
                
                $mapImgHR = $this->map->draw();
                
                // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
                if ($existsXYLayer) {
                    $this->pmap_drawXYLayer($mapImgHR); 
                }
                
                // GeoTIFF output
                if ($imgFormat) {
                    $tmpFileName = str_replace('\\', '/', $this->map->web->imagepath) . substr(SID, 10) . ".tif";
                    $mapImgHR->saveImage($tmpFileName, $this->map);
                    $this->imgUrlList[] = $tmpFileName;
                
                // JPG or PNG output
                } else {
                    $this->imgUrlList[] = mapSaveWebImage($this->map, $mapImgHR);
                    $legImg = $this->map->drawLegend();
                    $this->imgUrlList[] = mapSaveWebImage($this->map, $legImg);
                    $legImg->free();
                }
                
                $mapImgHR->free();
                break;
        }
    
        $refImg->free();
        $sbarImg->free();
    
    }
    
    function returnImgUrlList()
    {
        return $this->imgUrlList;
    }
    
    
    
    function returnLegStr()
    {
        return $this->rStr;
    }
    
    
    
    /**
     * Increase label size for PDF print and download
     */
    function increaseLabels($factor)
    {
        $layers = $this->map->getAllLayerNames();
        
        foreach ($layers as $ln) {
            $layer = $this->map->getLayerByName($ln);
            if ($layer->labelitem) {
                $numclasses = $layer->numclasses;
                $classes = array();
                for ($cl=0; $cl < $numclasses; $cl++) {
                    $class = $layer->getClass($cl);
                    if ($label = $class->label) {
                        if ($label->type == 0) {
                            $labelSize0 = $label->size;
                            $label->set("size", $labelSize0 * $factor);
                        }
                    }
                }
            }
        }
    }
    
    
    /**
     * ZOOM MAP TO SPECIFIED SCALE
     */
    function zoom2scale()
    {
        $GEOEXT = $_SESSION["GEOEXT"];
        $geoext0 = ms_newrectObj();
        $geoext0->setextent($GEOEXT["minx"],$GEOEXT["miny"],$GEOEXT["maxx"],$GEOEXT["maxy"]);
    
        // PREPARE MAP IMG 
        $x_pix = $this->mapW/2;
        $y_pix = $this->mapH/2;
    
        $xy_pix = ms_newPointObj();
        $xy_pix->setXY($x_pix, $y_pix);
    
        $this->map->zoomscale($this->scale, $xy_pix, $this->mapW, $this->mapH, $geoext0);
        $xy_pix->free();
    }



    /*
     * Draw Scale Bar
     ***************************************/
    function createScaleBar($printType, $imgDPI)
    {
        $this->pmap_setImgFormat(true); 
        $scalebar = $this->map->scalebar;
        $sbarlabel = $scalebar->label;
        $scalebar->set("transparent", MS_OFF);
    
        if ($printType == "dl" && $imgDPI >= 200) {
            $sbarlabel->set("size", MS_GIANT);
            $scalebar->set("width", $this->map->width * 0.3);
            $scalebar->set("height", $this->map->height * 0.011);
        }
        
        $sbarlabel->color->setRGB(0, 0, 0);
        $sbarlabel->outlinecolor->setRGB(255, 255, 255);
    
        $sbarImg = $this->map->drawScaleBar();
    
        return $sbarImg;
    }
    
    
    
    
    
    //===================================================================================//
    //                            LEGEND                                                 //
    //===================================================================================//
    
    /*
     * CREATES HTML LEGEND FOR PRINT OUTPUT
     *********************************************************************************/
    function writePrintLegendHTML()
    {
        $grouplist = $_SESSION["grouplist"];
        $defGroups = $_SESSION["defGroups"];
        $icoW      = $_SESSION["icoW"];  // Width in pixels
        $icoH      = $_SESSION["icoH"];  // Height in pixels
        $imgExt    = $_SESSION["printImgFormatExt"];
    
        // GET LAYERS FOR DRAWING AND IDENTIFY
        if (isset ($_SESSION["groups"]) && count($_SESSION["groups"]) > 0){
            $groups = $_SESSION["groups"];
        }else{
            $groups = $defGroups;
        }
    
        $this->rStr .= "<table class=\"print_legendtable\"> \n";
        $legPath = "images/legend/";
    
        foreach ($grouplist as $grp){
            if (in_array($grp->getGroupName(), $groups, TRUE)) {
                $glayerList = $grp->getLayers();
    
                $numcls = 0;
                            
                foreach ($glayerList as $glayer) {
                    $legendLayer = $this->map->getLayer($glayer->getLayerIdx());
                    $numClasses = count($glayer->getClasses());
                    $skipLegend = $glayer->getSkipLegend();
                    
                    if (($legendLayer->type < 3 || $legIconPath || $numClasses > 0) && checkScale($this->map, $legendLayer, $this->scale) == 1 && $skipLegend < 2) {
                    //if ($legendLayer->type < 3 && checkScale($map, $legendLayer, $scale) == 1) {
                        $numcls += $legendLayer->numclasses;
                        
                        $legLayerName = $glayer->getLayerName();
                        $layClasses = $glayer->getClasses();
                        $clsno = 0;
                        foreach ($layClasses as $cl) {
                            $legIconPath = $legendLayer->getClass($clsno)->keyimage;
                            $icoUrl = $legIconPath ? $legIconPath : $legPath.$legLayerName.'_i'.$clsno.'.'.$imgExt;
                            $grpClassList[] = array($cl, $icoUrl);
                            $clsno++;
                        }
                    }
                }
                
                // Only 1 class for Layer -> 1 Symbol for Group
                if ($numcls == 1) {
                    $legLayer = $leglayers[0];
                    $icoUrl = $grpClassList[0][1];
    
                    $this->rStr  .=  "   <tr>";
                    $this->rStr  .=  "<th><img src=\"$icoUrl\" width=\"$icoW\" height=\"$icoH\" alt=\"ico\" /></th>";
                    $this->rStr  .=  "<th style=\"width:100%\" colspan=\"3\">" . $grp->getDescription() . "</th>";
                    $this->rStr  .=  "</tr> \n";
    
                // More than 2 classes for Group  -> symbol for *every* class
                } elseif ($numcls > 1) {
                    $this->rStr  .=  ("\n  <tr><th colspan=\"4\">" . $grp->getDescription() . "</th></tr> \n");
    
                    $clscnt = 0;
                    foreach ($grpClassList as $cls) {
                        $clsStr = $cls[0];
                        $icoUrl = $cls[1];
    
                        $legLayerName = $legLayer->name;
                        $classes = $glayer->getClasses();                   
                        
                        $this->rStr .= (($clscnt % 2) ? "" : "  <tr>" ); 
    
                        $this->rStr .= ("<td style=\"width:$icoW\"><img src=\"$icoUrl\" width=\"$icoW\" height=\"$icoH\" alt=\"ico\" /> </td>");  
                        $this->rStr .= ("<td>$clsStr</td> ");
    
                        if ($clscnt % 2) {   // after printing RIGHT column
                            $this->rStr .= ("  </tr> \n");
                        } else {           // after printing LEFT column
                            if ($clscnt == ($numcls - 1)) {    // Begin new group when number of printed classes equals total class number
                                $this->rStr .= ("<td></td></tr> \n");
                            } else {
                                     // Continue in same group, add only new class item
                            }
                        }
                        $clscnt++;
                    }
                }
                unset($grpClassList);
            }
        }
        
        $this->rStr .= "</table> \n";
    }


} // END CLASS PRINTMAP




?>
