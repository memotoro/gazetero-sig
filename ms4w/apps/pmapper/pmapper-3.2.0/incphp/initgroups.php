<?php

/******************************************************************************
 *
 * Purpose: initialize groups and glayers and save definitions in session
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


require_once("group.php");

class Init_groups
{
    var $map;
    var $allGroups;
    var $gLanguage;
    var $map2unicode;
    
    function Init_groups($map, $allGroups, $gLanguage, $map2unicode)
    {
        $this->map = $map;
        $this->allGroups = $allGroups;
        $this->gLanguage = $gLanguage;
        $this->map2unicode = $map2unicode;

        $initGroups = $this->_defineLists();
        $this->_createGroups($initGroups);
    }
    
    
    function _defineLists()
    {
        $groupOrder = $this->allGroups;
        
        $mapGroupsNames = $this->map->getAllGroupNames();
        $mapLayers = $this->map->getAllLayerNames();
        //printDebug($mapLayers);
        
        // Create array for groups in map file
        foreach ($mapGroupsNames as $mgn) {
            $mapGroups[$mgn] = $this->map->getLayersIndexByGroup($mgn);
        }
        
        //Add layers as groups if not assigned to any group
        foreach($mapLayers as $l) {
            $layer = $this->map->getLayerByName($l);
            $layIdx = $layer->index;
            $layGrp = $layer->group;
            if ($layGrp == "") {
                $mapGroups[$l] = array($layIdx);
            }
        }
        
        // Sort group array according to order of $groupOrder
        foreach($groupOrder as $g) {
            if (count($mapGroups[$g]) > 0) {
                $initGroups[$g] =  $mapGroups[$g];
            } else {
                error_log("Could not create group '$g' defined in groupOrder in 'config.ini'. Check if name is correct.", 0);
            }
        }
        
        return $initGroups;
    }   

    
   /**
    * Initialize GROUPS
    * set group and layer properties
    *
    */
    function _createGroups($initGroups)
    {
        $existsXYLayer = 0;
        foreach ($initGroups as $gn=>$layerList) {
            $group = new GROUP($gn);
        
            $i = 1;
        
            // Loop through LAYERS of current group
            foreach ($layerList as $layIdx) {
                // Get layer info from map file
                $mapLay = $this->map->getLayer($layIdx);
                $mapLayName = $mapLay->name;
                $mapLayType = $mapLay->type;
                $mapLayConnType = $mapLay->connectiontype;
                //error_log("$mapLayName - $mapLayConnType");
        
                // Write layer properties to glayer object
                $glayer = new GLAYER($mapLayName);
                $glayer->setLayerIdx($layIdx);
                $glayer->setLayerType($mapLayType);
        
                // Add result field list
                if ($mapLayType <= 3 || $mapLayType == 5 || $mapLayConnType != 7) {    // result fields only for queryable layers point (0), line (1), polygon (2), annotation (5)
                    $selFields0 = $this->_initResultFields($this->map, $mapLay, $mapLayType);
                    
                    // Trim spaces
                    if (is_array($selFields0)) {
                        if (count($selFields0) > 0) {
                            //printDebug($selFields0);
                            $selFields = array();
                            foreach ($selFields0 as $sf0) {
                                // If field name starts with '&' then translate
                                $sf = (substr(trim($sf0), 0, 1) == '&' ? _p(trim($sf0)) : trim($sf0));
                                $selFields[] = $sf;
                            }
                            $glayer->setResFields($selFields);
                        }
                    }   
                }
                
                // Add hyperlink fields
                if ($this->_getHyperFieldList($mapLay)) {
                    $glayer->setHyperFields($this->_getHyperFieldList($mapLay));
                }
                
                // Add JOIN properties if defined
                if ($this->_getJoinProperties($mapLay)) {
                    $glayer->setTableJoin($this->_getJoinProperties($mapLay));
                }
                
                // Add classes
                $numclasses = $mapLay->numclasses;
                $classes = array();
                for ($cl=0; $cl < $numclasses; $cl++) {
                    $class = $mapLay->getClass($cl);
                    $className = $this->mapStringEncode($class->name);
                    if (strlen($className) > 0) {
                        $classname = _p(trim($className));
                        $classes[] = $classname; //str_replace("'", "\\'", $classname);
                    }
                }
                $glayer->setClasses($classes);
        
                // Check/Set labelitems if defined
                if ($mapLay->labelitem) {
                    $labelItem = _p($mapLay->labelitem);
                    $glayer->setLabelItem($labelItem);
                }              
                
                // Check/Set layer transparency (opacity)
                if (floatval($_SESSION['MS_VERSION']) >= 5) { 
                    $glayer->setOpacity($mapLay->opacity);
                } else {
                    $glayer->setOpacity($mapLay->transparency);
                }
                                
                
                // Check if layer is XY layer
                $XYLayerPropStr = $this->_returnMetaData($mapLay, "XYLAYER_PROPERTIES");
                if ($XYLayerPropStr != "") {
                    $glayer->setXYLayerAttribute();
                    $XYLayerPropList = $this->_getXYLayerPropList($XYLayerPropStr);
                    $glayer->setXYLayerProperties($XYLayerPropList);
                    pm_logDebug(3, $XYLayerPropList, "P.MAPPER-DEBUG: initgroups.php/_createGroups() - XYLayerProperties for layer $mapLayName");
                    
                    // Set in sessionid that XYLayer exists
                    $existsXYLayer = 1;
                }                
                
                //Check for skipLegend
                // 1: only for TOC_TREE, 2: always skip legend
                $skipLegend = $this->_returnMetaData($mapLay, "SKIP_LEGEND");
                $skipLegend = ($skipLegend == "" ? 0 : $skipLegend);
                $glayer->setSkipLegend($skipLegend);
                
                // Layer Encoding
                $glayer->setLayerEncoding($this->_returnMetaData($mapLay, "LAYER_ENCODING"));                
                
                // now add layer to group
                $group->addLayer($glayer);
        
                // set group description and result headers, process only for 1st layer of group
                if ($i == 1) {
                    // Set group description
                    $description  = $this->_initDescription($mapLay);
                    $group->setDescription($description);
        
                    // Set result group headers
                    if ($mapLayType <= 3 || $mapLayType == 5) {
                        $selHeaders  = $this->_initResultHeaders($this->map, $mapLay, $mapLayType, $this->gLanguage);
                        $group->setResHeaders($selHeaders);
                    }
                    $i = 0;
                }
        
            }
            $grouplist[$gn] = $group;
        }
        
        // Save everything in session
        $_SESSION["existsXYLayer"] = $existsXYLayer;
        $_SESSION["grouplist"] = $grouplist;
    
    }
    
    
    
   /**
    * Get layer description, result fields and headers from map file 
    * or take default values if there's no definition in map file
    */
    
    function _returnMetaData($layer, $metaTag) {
        $metaString = $layer->getMetaData($metaTag);
        return $metaString;
    }
    
    
    function _initResultFields($map, $mapLay, $mapLayType) {
        $metaString = $this->_returnMetaData($mapLay, "RESULT_FIELDS");
        
        if ($metaString != "") {
            $metaList = split(",", $metaString);
        } else {
            if ($mapLayType != 3) {
                $mapLay->open();       
                $metaList = $mapLay->getItems();
                $mapLay->close();
            } else {
                $metaList = array();
            }
        }
        return $metaList;    
    }
    
    
    function _initResultHeaders($map, $mapLay, $mapLayType) {
        $metaString = $this->_returnMetaData($mapLay, "RESULT_HEADERS");
        
        if ($metaString != "") {
            $metaList0 = split(",", $metaString);
            foreach ($metaList0 as $m) {
                $metaList[] = _p(trim($this->mapStringEncode($m)));
            }
            
        } else {
            if ($mapLayType != 3) {
                $mapLay->open();       
                $metaList = $mapLay->getItems();
                $mapLay->close();
            } else {
                $metaList = array();
            }
        }
        return $metaList;    
    }
    
    function _initDescription($mapLay) {
        $metaString = $this->_returnMetaData($mapLay, "DESCRIPTION");
        
        if ($metaString != "") {
            $descriptionTag = _p($this->mapStringEncode($metaString));
        } else {
            $descriptionTag = $mapLay->name;
        }

        return preg_replace(array("/\\\/", "/\|/"), array("", ""), trim($descriptionTag));  // ESCAPE APOSTROPHES (SINGLE QUOTES) IN NAME WITH BACKSLASH
    }
    
    
   /**
    * CHECK FOR HYPERLINK FIELDS
    * Check if hyperlink fields have been declared in map file
    */
    function _getHyperFieldList($glayer)
    {
        // First split string into field arrays, then the chunks into field name and alias for link
        if ($hyperMeta = $glayer->getMetaData("RESULT_HYPERLINK")) {
            $hyperStr = preg_split('/,/', $hyperMeta);
            foreach ($hyperStr as $hs) {
                if (preg_match ('/\|\|/', $hs)) {
                    $hfa = preg_split('/\|\|/', $hs);
                    $hyperFieldsAlias[trim($hfa[0])] = _p($this->mapStringEncode(trim($hfa[1])));
                    $hyperFieldsValues[] = trim($hfa[0]);
                } else {
                    $hyperFieldsValues[] = trim($hs);
                    $hyperFieldsAlias = array();
                }
            }
            return array($hyperFieldsValues, $hyperFieldsAlias);
        } else {
            return NULL;
        }
    }
    
    
   /**
    * CHECK FOR DB JOINS
    * Check if DB joins have been declared in map file
    */
    function _getXYLayerPropList($XLLayerMetaStr) 
    {
        $XYLayerList = preg_split("/\|\|/", $XLLayerMetaStr);
        
        $XYLayerProperties["dsn"] = $XYLayerList[0];
        $XYLayerProperties["xyTable"] = $XYLayerList[1];
                
        $XYLayerFldList = preg_split("/,/", $XYLayerList[2]);
        $XYLayerProperties["x_fld"]        = $XYLayerFldList[0];
        $XYLayerProperties["y_fld"]        = $XYLayerFldList[1];
        $XYLayerProperties["classidx_fld"] = $XYLayerFldList[2];
        
        $XYLayerProperties["noQuery"] = $XYLayerList[3];
        
        return $XYLayerProperties;
    }
    
    
    
   /**
    * CHECK FOR DB JOINS
    * Check if DB joins have been declared in map file
    */
    function _getJoinProperties($qLayer) 
    {
        if ($qLayer->getMetaData("RESULT_JOIN")) {
            $joinStrMeta = $qLayer->getMetaData("RESULT_JOIN");
            $joinList = preg_split("/\|\|/", $joinStrMeta);
            
            $joinPropList["dsn"] = $joinList[0];
    
            // Join table properties
            $tableProp  = preg_split("/\@/", $joinList[1]);
            $joinPropList["fromTable"]     = $tableProp[0];
            $joinPropList["fromField"]     = $tableProp[1];
            $joinPropList["fromFieldType"] = $tableProp[2];
            $joinFieldStr = $tableProp[3];
            $joinPropList["joinFields"] = $joinFieldStr;        
    
            // Field in Shapefile to join to
            $joinPropList["toField"] =  $joinList[2];
    
            // Join type: one-to-one (0) or one-to-many (1)
            $joinPropList["one2many"] = $joinList[3];
            
            return $joinPropList;
        } else {
            return false;
        }
    }
    
    
    function mapStringEncode($inString)
    {
        if ($this->map2unicode) {
            $mapfile_encoding = trim($this->map->getMetaData("MAPFILE_ENCODING"));
            if ($mapfile_encoding) {
                if ($mapfile_encoding != "UTF-8") {
                    $outString = iconv($mapfile_encoding, "UTF-8", $inString);
                } else {
                    $outString = $inString;
                }
            } else {
                $outString = utf8_encode($inString);
            }
        } else {
            $outString = $inString;
        }
        return $outString;
    }
	


} // class

?>