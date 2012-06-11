<?php

/******************************************************************************
 *
 * Purpose: central class to create map image
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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


class PMap
{
    /*===========================================================================
                                  PUBLIC FUNCTIONS
     ============================================================================*/

    function PMap($map)
    {
        $this->map = $map;
        
        $this->pmap_addCustomLayers();
        
        $this->pmap_getGroups();
        setGroups($this->map, $this->groups, 0, 1);
        
        
        $this->pmap_setGeoExt();
        $this->pmap_setMapWH();
        
        $this->pmap_createMap();
        $this->pmap_createMapImage();
        
        $this->pmap_setHistory();
        $this->pmap_registerSession();
    }
    
    
    function pmap_addCustomLayers()
    {
        include_once("customlayers.php");
        //$this->pmap_addWMSGroups();
        if (isset($_SESSION['url_points'])) {
            if (count($_SESSION['url_points']) > 0) {
                $urlLayer = new URL_Layer($this->map);
            }
        }
    }
    
    
    /**
     * Return the URL to map image
     */
    function pmap_returnMapImgURL()
    {
        return $this->mapURL;
    }
    
    function pmap_returnScalebarImgURL()
    {
        return $this->scalebarURL;
    }

    /**
     * Create JavaScript variables from PHP variables
     */
    function pmap_returnMapJSParams()
    {
        $this->xdelta_geo = $this->GEOEXT["maxx"] - $this->GEOEXT["minx"];
        $this->ydelta_geo = $this->GEOEXT["maxy"] - $this->GEOEXT["miny"];
        
        $this->pmap_getRefBoxStr();
        
        //$no_dd = ($this->map->units != 5 ? 0 : 1);        
        $mstr['mapW'] = $this->mapwidth;
        $mstr['mapH'] = $this->mapheight;
        $mstr['refW'] = $this->refW;
        $mstr['refH'] = $this->refH;
        $mstr['minx_geo'] = $this->GEOEXT["minx"];
        $mstr['maxy_geo'] = $this->GEOEXT["maxy"];
        $mstr['xdelta_geo'] = $this->xdelta_geo;
        $mstr['ydelta_geo'] = $this->ydelta_geo;
        $mstr['refBoxStr'] = $this->refBoxStr;

        return $mstr;
    }
    
    /**
     * Return the pan string for the reference box
     */
    function pmap_returnRefBoxStr()
    {
        return $this->refBoxStr;
    }

    function pmap_returnMapW()
    {
        return $this->mapwidth;
    }
    
    function pmap_returnMapH()
    {
        return $this->mapheight;
    }

    /**
     * Return the scale
     */
    function pmap_returnGeoScale()
    {
        return number_format(round($this->geo_scale, -1), 0, '', '');
    }
    


    /*========================================================================================
                                     PRIVATE FUNCTIONS
     ========================================================================================*/

    /**
     * SWITCH ON/OFF LAYERS/GROUPS (READ FROM URL || SID)
     */
    function pmap_getGroups() 
    {
        if (isset($_REQUEST["groups"])) {
            $drawGroupStr = $_REQUEST["groups"];
            $this->groups = explode(",", $drawGroupStr);
        } elseif (isset($_SESSION["groups"]) && count($_SESSION["groups"]) > 0) {
            $this->groups = $_SESSION["groups"];
        } else {
            $this->groups = $_SESSION["defGroups"];
        }
    }



    /**
     * SET EXTENTS OF GEOEXT-OBJECT:
     * - from map file if first called
     * - from SID for next calls
     */
    function pmap_setGeoExt()
    {
        // 2nd OR HIGHER CALL
        if (isset($_SESSION["GEOEXT"])) {
            $this->GEOEXT = $_SESSION["GEOEXT"];
            $this->geoext0 = ms_newrectObj();
            $this->geoext0->setextent($this->GEOEXT["minx"],$this->GEOEXT["miny"],$this->GEOEXT["maxx"],$this->GEOEXT["maxy"]);
        
            $this->old_geo_scale = $_SESSION["geo_scale"];
        
        
        } elseif (isset($_SESSION['zoom_extparams'])) {
            $ext = $_SESSION['zoom_extparams'];
            $this->map->setextent($ext[0], $ext[1], $ext[2],$ext[3]);
            //unset($_SESSION['zoom_extparams']);
            
            $this->geoext0 = ms_newrectObj();
            $this->geoext0->setextent($ext[0], $ext[1], $ext[2],$ext[3]);
            
            $this->historyBack = array();
            $this->historyFwd = array();
        
        // INITIAL START
        } else {
            if (isset($_SESSION['mapMaxExt'])) {
                $me = $_SESSION['mapMaxExt'];
                $this->geoext0 = ms_newrectObj();
                $this->geoext0->setextent($me["minx"],$me["miny"],$me["maxx"],$me["maxy"]);
                
            } else {
                $this->geoext0 = $this->map->extent;
            }
            
            
            $this->GEOEXT["minx"] = $this->geoext0->minx;
            $this->GEOEXT["miny"] = $this->geoext0->miny;
            $this->GEOEXT["maxx"] = $this->geoext0->maxx;
            $this->GEOEXT["maxy"] = $this->geoext0->maxy;
            $_SESSION["GEOEXT"] = $this->GEOEXT;
        
            // calculate scale
            $dpi = $this->map->resolution;
            $mapW = $_SESSION["mapwidth"];
            $this->old_geo_scale = ($mapW /($this->GEOEXT["maxx"] - $this->GEOEXT["minx"])) / (0.0254 / $dpi);
            
            $this->historyBack = array();
            $this->historyFwd = array();
        }
    }



    /**
     * MAP DIMENSION X/Y
     */
    function pmap_setMapWH()
    {
        $this->mapwidth = $_SESSION["mapwidth"];
        $this->mapheight = $_SESSION["mapheight"];
        $this->map->set("width", $this->mapwidth);
        $this->map->set("height", $this->mapheight);
    }


    
    /**
     * SET HISTORY EXTENTS IN ARRAYS FOR BACK AND FORWARD 
     */
    function pmap_setHistory()
    {
        $maxHistory = 8; // <===== ADAPT IF NECESSARY <=====
        
        $this->historyBack = $_SESSION["historyBack"];
        $this->historyFwd  = $_SESSION["historyFwd"];
        
        // Compare new with old extent (just take minx and maxy)
        // if different: add new extent to history
        if ($this->GEOEXT["minx"] != $this->OLDGEOEXT["minx"] && $this->GEOEXT["maxy"] != $this->OLDGEOEXT["maxy"] && is_array($this->OLDGEOEXT)) {
            if ($_REQUEST["zoom_type"] == "zoomback") {
                $this->historyFwd[] = $this->OLDGEOEXT;
            } else {
                $this->historyBack[] = $this->OLDGEOEXT;
            }
            
            //Limit history arrays to max value
            if (count($this->historyBack) > $maxHistory) {
                array_shift($this->historyBack);
            }
            if (count($this->historyFwd) > $maxHistory) {
                array_shift($this->historyFwd);
            }
        }
    }



    /**
     * REFERENCE MAP: ADJUST REFBOX
     */
    function pmap_getRefBoxStr()
    {
        // REFERENCE MAP DIMENSION X/Y
        $refMap = $this->map->reference;
        
        if ($refMap) {
            $this->refW = $refMap->width;
            $this->refH = $refMap->height;
            
            $refExtent = $refMap->extent;
            $refXmin = $refExtent->minx;
            $refYmin = $refExtent->miny;
            $refXmax = $refExtent->maxx;
            $refYmax = $refExtent->maxy;
            
            $xdelta_ref = $refXmax - $refXmin;
            $ydelta_ref = $refYmax - $refYmin;
            
            $pixSizeX = $xdelta_ref / $this->refW;
            $pixSizeY = $ydelta_ref / $this->refH;
            $refBoxL  = max(0, round((($this->GEOEXT["minx"]-$refXmin) / $pixSizeX) - 0.5));
            $refBoxT  = max(0, round((($refYmax - $this->GEOEXT["maxy"]) / $pixSizeY)) - 1);
            $refBoxW  = min(($this->refW - $refBoxL), round($this->xdelta_geo / $pixSizeX));
            $refBoxH  = min(($this->refH - $refBoxT), round($this->ydelta_geo / $pixSizeY));
        
            $this->refBoxStr = "$refBoxL,$refBoxT,$refBoxW,$refBoxH";
        } else {
            $this->refBoxStr = false;
        }
        //return $refBoxStr;
    }
    
    
    
    /**
     * CREATE MAP IMAGE
     */
    function  pmap_createMapImage()
    {
        // Set image format according to settings
        $this->pmap_setImgFormat();
        
        
        // DRAW MAP AND SET GEO EXTENT (IN MAP UNITS) IN SID FOR SUBSEQUENT ZOOM ACTIONS //
        $mapImg = $this->map->draw();
        
        // CHECK iF THERE'S AN XY-LAYER AND THEN DRAW IT
        if ($_SESSION["existsXYLayer"]) {
            $this->pmap_drawXYLayer($mapImg); 
        }
        
        // write old geoextent to var before changing
        $this->OLDGEOEXT = $this->GEOEXT; 
        //error_log($this->OLDGEOEXT);
        
        // convert numbers for GEOEXT and geo_scale to string 
        // to avoid saving numbers as full float number in SID
        $this->GEOEXT["minx"] = strval($this->map->extent->minx);
        $this->GEOEXT["miny"] = strval($this->map->extent->miny);
        $this->GEOEXT["maxx"] = strval($this->map->extent->maxx);
        $this->GEOEXT["maxy"] = strval($this->map->extent->maxy);
        
        $this->geo_scale = $this->map->scale;
        
        
        // PRODUCE MAP, REFERENCE AND SCALEBAR IMAGE //
        //$this->mapURL = $mapImg->saveWebImage();
        $this->mapURL = mapSaveWebImage($this->map, $mapImg);
        
        $scalebarImg = $this->map->drawScaleBar();
        //$this->scalebarURL = $scalebarImg->saveWebImage();
        $this->scalebarURL = mapSaveWebImage($this->map, $scalebarImg);
        
        
        if (isset($_SESSION['ul'])) {
            //error_log($_SESSION['ul']);
            //$urlLayer = new URL_Layer($this->map, $mapImg);
        }
        
        
        $mapImg->free();
        $scalebarImg->free();
    }
    
    /** 
     * Set image format according to settings
     * if alternative format is defined for some layers
     * and if one of these layers is active, then use altImgFormat
     */
    function  pmap_setImgFormat($print=false)
    {
        $altImgFormatLayers = $_SESSION['altImgFormatLayers'];
        $altImgFormat = $_SESSION['altImgFormat'];
        $useAltImgFormat = 0;
        if ($altImgFormatLayers && $altImgFormat) {
            foreach ($altImgFormatLayers as $ai) {
                $mapLay = $this->map->getLayerByName($ai);
                if ((in_array($mapLay->name, $this->groups) || in_array($mapLay->group, $this->groups)) && checkScale($this->map, $mapLay, $this->map->scale)) {
                   $useAltImgFormat = 1;
                   break;
                } 
            }
        }
        
        if (!$print) {
            if ($useAltImgFormat) {
                $this->map->selectOutputFormat($_SESSION["altImgFormat"]);
            } else {
                $this->map->selectOutputFormat($_SESSION["imgFormat"]);
            }
        } else {
            if ($useAltImgFormat) {
                $this->map->selectOutputFormat($_SESSION["printAltImgFormat"]);
            } else {
                $this->map->selectOutputFormat($_SESSION["printImgFormat"]);
            }
        }
    }
    

    /**
     * CREATE NEW MAP
     * 
     */
    function pmap_createMap()
    {
        
        // CHECK IF THERE ARE RESULTLAYERS (HIGHLIGHT) AND ADD THEM TO MAP
        $this->pmap_checkResultLayers();
    
        //
        // GET ZOOMTYPE
        // zoomrect, zoompoint, zoomscale, zoomfull, zoomextent, ref, zoomback, zoomfwd
        // Default:  start with 'zoom rect'
        if (isset($_REQUEST["zoom_type"])) {
            $zoom_type = $_REQUEST["zoom_type"];
        } else {
            $zoom_type = "zoomrect";
        }
    
        //
        // GET ZOOMFACTOR
        // <0: zoom out, 1: pan, >1: zoom in
        // Default: 1
        if (isset($_REQUEST["zoom_factor"])) {
            $this->zoom_factor = $_REQUEST["zoom_factor"];
        } else {
            $this->zoom_factor = "1";
        }
    
    
        // =================   APPLY PARAMETERS TO MAP ================= //
    
        $this->maxextent = $this->getMapMaxExtent();
    
        // ZOOM TO RECTANGLE (ZOOMBOX -> PIXEL COORDINATES) OR FULL EXTENT
        if ($zoom_type == "zoomrect") {
            $this->pmap_zoomrect();
           
        // ZOOM TO POINT -> Pan, Zoomout
        } elseif ($zoom_type == "zoompoint") {
            $this->pmap_zoompoint();
    
        // ZOOM TO SCALE
        } elseif ($zoom_type == "zoomscale") {
            $scale = $_REQUEST["scale"];
            $this->pmap_zoomscale($scale);
            
        // ZOOM TO FULL EXTENT
        } elseif ($zoom_type == "zoomfull") {
            // Get external parameters via URL (eg from links)
            if (isset($_SESSION['zoom_extparams'])) {
                $ext = $_SESSION['zoom_extparams'];
                //printDebug($ext);
                $this->map->setextent($ext[0], $ext[1], $ext[2],$ext[3]);
                unset($_SESSION['zoom_extparams']);
            } else {
                $this->map->setextent($this->maxextent->minx, $this->maxextent->miny, $this->maxextent->maxx, $this->maxextent->maxy);
            }
    
        // ZOOM TO EXTENT (GEO-EXTENT)
        } elseif ($zoom_type == "zoomextent") {
            $this->pmap_zoomextent(); 
    
        // PAN VIA REFERENCE MAP
        } elseif ($zoom_type == "ref") {
            $this->pmap_zoomref();
    
        // ZOOM BACK TO PREVIOUS EXTENT
        } elseif ($zoom_type == "zoomback") {
            $this->pmap_zoomback();
            
        // ZOOM FORWARD 
        } elseif ($zoom_type == "zoomfwd") {
            $this->pmap_zoomfwd();
        
        // ZOOM TO GROUP 
        } elseif ($zoom_type == "zoomgroup") {
            $this->pmap_zoomgroup();
        
        }
    
    } // END OF pmap_createMap()
    
    
    /************************************************
     * ZOOM&PAN FUNCTIONS
     ************************************************/
    /**
     * Zoom to rectangle
     */
    function pmap_zoomrect()
    {
        if (isset($_REQUEST["imgbox"])) {
            $imgbox_str = $_REQUEST["imgbox"];
            //error_log($imgbox_str);
            if ($imgbox_str != "") {
                $imgbox_arr = explode(" ", $imgbox_str);
                // New map extent in image pixel ((0,0) top-left)
                $pix_minx = $imgbox_arr[0];
                $pix_miny = $imgbox_arr[1];
                $pix_maxx = $imgbox_arr[2];
                $pix_maxy = $imgbox_arr[3];
                
                if ($pix_minx == $pix_maxx) $pix_maxx = $pix_maxx + 3;  ## increase max extent if min==max
                if ($pix_miny == $pix_maxy) $pix_maxy = $pix_maxy - 3;  ##

                $pixext = ms_newrectObj();
                $pixext->setextent($pix_minx,$pix_miny,$pix_maxx,$pix_maxy);
            }
            
        // Zoom to full extent when starting
        } else {
            $pixext = ms_newrectObj();
            $pixext->setextent(0, 0, $this->mapwidth, $this->mapheight);
        }
        
        $this->map->zoomrectangle($pixext, $this->mapwidth, $this->mapheight, $this->geoext0);
        $pixext->free();
    }
    
    /**
     * Zoom to point
     */
    function pmap_zoompoint()
    {
        if (isset($_REQUEST["imgxy"])) {
            if ($_REQUEST["imgxy"] != "") {
                $imgxy_str = $_REQUEST["imgxy"];
                $imgxy_arr = explode(" ", $imgxy_str);
            } else {
                $imgxy_arr = array ($this->mapwidth/2, $this->mapheight/2);
            }
        } else {
            $imgxy_arr = array ($this->mapwidth/2, $this->mapheight/2);
        }

        // Create x/y-point for zoom center
        $x_pix = $imgxy_arr[0];
        $y_pix = $imgxy_arr[1];
        $xy_pix = ms_newPointObj();
        $xy_pix->setXY($x_pix, $y_pix);
        
        $this->map->zoompoint($this->zoom_factor, $xy_pix, $this->mapwidth, $this->mapheight, $this->geoext0, $this->maxextent);
        $xy_pix->free();
    }
    
    /**
     * Zoom to scale
     */
    function pmap_zoomscale($scale)
    {
        $x_pix = $this->mapwidth/2;
        $y_pix = $this->mapheight/2;
        $xy_pix = ms_newPointObj();
        $xy_pix->setXY($x_pix, $y_pix);
        $this->map->zoomscale($scale, $xy_pix, $this->mapwidth, $this->mapheight, $this->geoext0, $this->maxextent);
        $xy_pix->free();
    }
    
    /**
     * Zoom to specified geo extent
     */
    function pmap_zoomextent()
    {
        $extent_str = $_REQUEST["extent"];
        $extent_arr = explode(" ", $extent_str);
        $this->map->setextent($extent_arr[0], $extent_arr[1], $extent_arr[2],$extent_arr[3]);
    }
    
    /**
     * Zoom via reference map
     */
    function pmap_zoomref()
    {
        $imgxy_str = $_REQUEST["imgxy"];
        $imgxy_arr = explode(" ", $imgxy_str);
        $x_pix = $imgxy_arr[0];
        $y_pix = $imgxy_arr[1];

        $refmap = $this->map->reference;
        $refmapwidth = $refmap->width;
        $refmapheight = $refmap->height;

        $GEOEXT = $_SESSION["GEOEXT"];
        $geo0DeltaX = $GEOEXT["maxx"] - $GEOEXT["minx"];
        $geo0DeltaY = $GEOEXT["maxy"] - $GEOEXT["miny"];
        $newMapExtent = $this->refMapClick ($this->map, $x_pix, $y_pix, $refmapwidth, $refmapheight, $geo0DeltaX, $geo0DeltaY);

        $this->map->setextent($newMapExtent[0], $newMapExtent[1], $newMapExtent[2], $newMapExtent[3] );
    }
    
    /**
     * Zoom to previous extent
     */
    function pmap_zoomback()
    {
        $historyBack = $_SESSION["historyBack"];
        if (count($historyBack) > 0 ) {
            $OLDGEOEXT = array_pop($historyBack);
            $_SESSION["historyBack"] = $historyBack;
            $this->map->setextent($OLDGEOEXT["minx"], $OLDGEOEXT["miny"], $OLDGEOEXT["maxx"], $OLDGEOEXT["maxy"] );
        } else {
            $GEOEXT = $_SESSION["GEOEXT"];
            $this->map->setextent($GEOEXT["minx"], $GEOEXT["miny"], $GEOEXT["maxx"], $GEOEXT["maxy"] );
        }
    }
    
    /**
     * Zoom forward 
     */
    function pmap_zoomfwd()
    {
        $historyFwd = $_SESSION["historyFwd"];
        if (count($historyFwd) > 0) {
            $OLDGEOEXT = array_pop($historyFwd);
            $_SESSION["historyFwd"] = $historyFwd;
            $this->map->setextent($OLDGEOEXT["minx"], $OLDGEOEXT["miny"], $OLDGEOEXT["maxx"], $OLDGEOEXT["maxy"] );
        } else {
            $GEOEXT = $_SESSION["GEOEXT"];
            $this->map->setextent($GEOEXT["minx"], $GEOEXT["miny"], $GEOEXT["maxx"], $GEOEXT["maxy"] );
        }
    }
    
    /**
     * Zoom to a group of layers
     */
    function pmap_zoomgroup()
    {
        require_once("map/pmapgroup.php");
        $groupname = $_REQUEST["groupname"];
        
        $pmapGroup = new PMapGroup($this->map, $groupname);
        $groupExt = $pmapGroup->getGroupExtent(true);
        
        pm_logDebug(3, $groupExt, "P.MAPPER-DEBUG: map.php/pmap_zoomgroup() - groupExt");
        
        $this->map->setextent($groupExt['minx'], $groupExt['miny'], $groupExt['maxx'], $groupExt['maxy']);
    }
        
    
    /**
     * Set map maximum extent
     * takes values from session if set via URL (in initmap()->getMapInitURL())
     */
    function getMapMaxExtent()
    {
        if (isset($_SESSION['mapMaxExt'])) {
            $me = $_SESSION['mapMaxExt'];
            $mapMaxExt = ms_newrectObj();
            $mapMaxExt->setextent($me["minx"],$me["miny"],$me["maxx"],$me["maxy"]);
        } else {
            $mapMaxExt = $this->map->extent;
        }
        
        return $mapMaxExt;
    }
    
    
    /**
     * CHECK IF THERE ARE RESULTLAYERS TO ADD
     */
    function pmap_checkResultLayers()
    {
        // ADD RESULTLAYER: MARKING SHAPE(S) AND ADD AS IN NEW CREATED LAYER
        if (isset($_REQUEST["resultlayer"])) {
            $resultlayerStr = $_REQUEST["resultlayer"];
            if ($resultlayerStr == "remove") {
                session_unregister("resultlayers");
            } else {
                $resultlayer = explode(" ", $resultlayerStr);
                $reslayname = $resultlayer[0];
                $shpindexes = explode("|", $resultlayer[1]);
                $resultlayers[$reslayname] = $shpindexes;
                $this->pmap_addResultLayer($reslayname, $shpindexes);
                $_SESSION["resultlayers"] = $resultlayers;
            }
        } elseif (isset($_SESSION["resultlayers"])) {
            $resultlayers = $_SESSION["resultlayers"];
            foreach ($resultlayers as $reslayer => $shpindexes) {
                $this->pmap_addResultLayer($reslayer, $shpindexes);
            }
        }
    }
    

    /**
     * FOR ZOOM TO SELECTED.
     * Adds a new layer to the map for highlighting feature
     */
    function pmap_addResultLayer($reslayer, $shpindexes)
    {
        $resulttilelayers = $_SESSION["resulttilelayers"];
        $resulttilelayer = $resulttilelayers[$reslayer];
    
        $qLayer = $this->map->getLayerByName($reslayer);
        $qlayType = $qLayer->type;
        $layNum = count($this->map->getAllLayerNames());
    
    
        // TEST IF LAYER HAS THE SAME PROJECTION AS MAP
        $mapProjStr = $this->map->getProjection();
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
            $hlMapLayer->set("name", "pmapper_reslayer");
            $newResLayer = ms_newLayerObj($this->map, $hlMapLayer);
            
        } else {
            // create dynamically
            $hlDynLayer = 1;
            $newResLayer = ms_newLayerObj($this->map);
            $newResLayer->set("name", "pmapper_reslayer");
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
                    $resTileShpIdx = $resulttilelayer[$resShpIdx];
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
        //$layOrder = $this->map->getLayersDrawingOrder();
        while ($newResLayerIdx < ($layNum-1)) {
            $this->map->moveLayerUp($newResLayerIdx);
        }
    }
    
    
    
    /**
     * REGISTER SESSION VARIABLES
     */
    function pmap_registerSession()
    {    
        $_SESSION["groups"]  = $this->groups;
        if (isset($this->resultlayers)) $_SESSION["resultlayers"] = $this->resultlayers;
        $_SESSION["GEOEXT"]      = $this->GEOEXT;
        $_SESSION["geo_scale"]   = $this->geo_scale;
        $_SESSION["historyBack"] = $this->historyBack;
        $_SESSION["historyFwd"]  = $this->historyFwd;
    } 
    
    
    
    /**
     * RETURN NEW MAP EXTENT FOR CLICK ON REF IMAGE
     * Taken from the gMap demo by DMSolution
     */
    function refMapClick ($map, $nClickPixX, $nClickPixY, $dfWidthPix, $dfHeightPix, $dfDeltaX, $dfDeltaY)
    {
        $dfKeyMapXMin = $map->reference->extent->minx;
        $dfKeyMapYMin = $map->reference->extent->miny;
        $dfKeyMapXMax = $map->reference->extent->maxx;
        $dfKeyMapYMax = $map->reference->extent->maxy;
    
        $nClickGeoX = $this->mapPix2Geo($nClickPixX, 0, $dfWidthPix, $dfKeyMapXMin,  $dfKeyMapXMax, 0);
        $nClickGeoY = $this->mapPix2Geo($nClickPixY, 0, $dfHeightPix, $dfKeyMapYMin, $dfKeyMapYMax, 1);
    
        $dfMiddleX = $nClickGeoX;
        $dfMiddleY = $nClickGeoY;
    
        $dfNewMinX = $dfMiddleX - ($dfDeltaX/2);
        $dfNewMinY = $dfMiddleY - ($dfDeltaY/2);
        $dfNewMaxX = $dfMiddleX + ($dfDeltaX/2);
        $dfNewMaxY = $dfMiddleY + ($dfDeltaY/2);
    
    
        // --------------------------------------------------------------------
        //      not go outside the borders (map extent as in map file).
        // --------------------------------------------------------------------
        $maxExtent = $map->extent;
        $dfMaxExtMinX = $maxExtent->minx;
        $dfMaxExtMinY = $maxExtent->miny;
        $dfMaxExtMaxX = $maxExtent->maxx;
        $dfMaxExtMaxY = $maxExtent->maxy;
    
        if ($dfNewMinX < $dfMaxExtMinX) {
            $dfNewMinX = $dfMaxExtMinX;
            $dfNewMaxX = $dfNewMinX + ($dfDeltaX);
        }
        if ($dfNewMaxX > $dfMaxExtMaxX) {
            $dfNewMaxX = $dfMaxExtMaxX;
            $dfNewMinX = $dfNewMaxX - ($dfDeltaX);
        }
        if ($dfNewMinY < $dfMaxExtMinY) {
            $dfNewMinY = $dfMaxExtMinY;
            $dfNewMaxY = $dfNewMinY + ($dfDeltaY);
        }
        if ($dfNewMaxY > $dfMaxExtMaxY) {
            $dfNewMaxY = $dfMaxExtMaxY;
            $dfNewMinY = $dfNewMaxY - ($dfDeltaY);
        }
    
        $mapExtents = array ($dfNewMinX, $dfNewMinY, $dfNewMaxX, $dfNewMaxY);
        return ($mapExtents) ;
    }
    
    
    /**
     * TRANSFORM PIXEL COORDINATE TO MAP UNITS
     * Taken from the gMap demo by DMSolution
     */
    function mapPix2Geo($nPixPos, $dfPixMin, $dfPixMax, $dfGeoMin, $dfGeoMax, $nInversePix)
    {
        $dfWidthGeo = $dfGeoMax - $dfGeoMin;
        $dfWidthPix = $dfPixMax - $dfPixMin;
    
        $dfPixToGeo = $dfWidthGeo / $dfWidthPix;
    
        if (!$nInversePix)
            $dfDeltaPix = $nPixPos - $dfPixMin;
        else
            $dfDeltaPix = $dfPixMax - $nPixPos;
    
        $dfDeltaGeo = $dfDeltaPix * $dfPixToGeo;
        $dfPosGeo = $dfGeoMin + $dfDeltaGeo;
    
        return ($dfPosGeo);
    }
    
    
    
    /**
     * DRAW XY LAYER: ADD POINTS FROM DB TO MAP IMAGE
     */
    function pmap_drawXYLayer($img) 
    {
        $grouplist = $_SESSION["grouplist"];
        
        // Loop through groups and layers to find XYLayers
        foreach ($grouplist as $grp) {
            $glayerList = $grp->getLayers();
            foreach ($glayerList as $glayer) {
                $XYLayerProperties = $glayer->getXYLayerProperties();
                
                if ($XYLayerProperties) {       // XYLayer found, now process 
                
                    $mLayer = $this->map->getLayer($glayer->getLayerIdx());
                    $mLayerStatus = $mLayer->status;
                    
                    if ($mLayerStatus == MS_ON) {        // Check if XYLayer is switched ON
                        $pearDbClass = $_SESSION['pearDbClass'];    
                        require_once ("$pearDbClass.php");
                        require_once ("query/query.php");
                        
                        // Check if Layer projection differs from Map projection
                        $changeLayProj = checkProjection($this->map, $mLayer);
                        
                        // Map extent for limiting query 
                        if ($changeLayProj) {
                            $me = $this->map->extent;
                            $mapExt = ms_newRectObj();
                            $mapExt->setExtent($me->minx, $me->miny, $me->maxx, $me->maxy); 
                            $mapExt->project($changeLayProj['mapProj'], $changeLayProj['layProj']);
                        } else {
                            $mapExt = $this->map->extent;
                        }
                        $map_xmin = $mapExt->minx;
                        $map_ymin = $mapExt->miny;
                        $map_xmax = $mapExt->maxx;
                        $map_ymax = $mapExt->maxy;
                        
                        if ($changeLayProj) $mapExt->free();
                        
                        
                        // XY Layer Properties
                        $dsn          = $XYLayerProperties["dsn"];
                        $xyTable      = $XYLayerProperties["xyTable"];
                        $x_fld        = $XYLayerProperties["x_fld"];
                        $y_fld        = $XYLayerProperties["y_fld"];
                        $classidx_fld = $XYLayerProperties["classidx_fld"];
                        
                        $resFldStr = $mLayer->getMetaData("RESULT_FIELDS");
                        $resFieldList = preg_split('/[\s,]+/', $resFldStr);
                        
                        // Prepare query
                        if (preg_match("/@/", $xyTable)) {          // Check for WHERE filter in table definition
                            $xyList = preg_split('/@/', $xyTable);
                            $whereFilter = $xyList[1];
                            $xyTable = $xyList[0];
                        }
                        $sql_SELECT = "SELECT $x_fld, $y_fld, $classidx_fld FROM $xyTable ";
                        $sql_WHERE  = "WHERE " . ($whereFilter ? $whereFilter . " AND " : "") . " $x_fld >= $map_xmin  AND $x_fld <= $map_xmax AND $y_fld >= $map_ymin AND $y_fld <= $map_ymax";
                        
                        $sql = ("$sql_SELECT  $sql_WHERE");
                        pm_logDebug(3, $sql, "P.MAPPER-DEBUG: map.php/pmap_drawXYLayer() - SQL Cmd:");
  
                        
                        // init DB class
                        $db = new $pearDbClass;
    
                        // Connect to DB       
                        $dbh = $db->connect($dsn);
                        if ($db->isError($dbh)) {
                            db_logErrors($dbh);
                            die();
                        }
                        
                        // Execute query 
                        $res = $dbh->query($sql);
                        if ($db->isError($res)) {
                            db_logErrors($res);
                            die();
                        }

                        // Draw points on map image
                        while ($xyRow = $res->fetchRow()) {
                            $ptX = $xyRow[0];
                            $ptY = $xyRow[1];
                            $clIdx = $xyRow[2];
                            
                            $xyPoint = ms_newPointObj();
                            $xyPoint->setXY($ptX, $ptY);
                            $xyPoint->draw($this->map, $mLayer, $img, $clIdx, "");
                            $xyPoint->free(); 
                        }
                        
                        $res->free();
                        $dbh->disconnect();
                    }
                }
            }
        }
    }


}


?>
