<?php

/******************************************************************************
 *
 * Purpose: class for TOC and legend creation
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

class Legend
{
    var $map;         
    var $categories;  
    var $legendonly;  
    var $legPath; 
    var $catStyle;    
    var $grpStyle;    
    var $legStyle;
    var $catInfoLink; 
    var $grpInfoLink;    
    var $grouplist;   
    var $defGroups;   
    var $allGroups;
    var $groups;    
    var $icoW;        
    var $icoH;        
    var $imgFormat;   
    var $scaleLayers; 
    var $scale;      
    var $catWithCheckbox;
       
    
    function Legend($map, $categories=0, $legendonly=0)
    {
        $this->map         = $map;
        $this->categories  = $categories;
        $this->legendonly  = $legendonly;
        $this->legPath     = "images/legend/";
        
        //$this->mutualDisableList = $_SESSION['mutualDisableList'];
        $this->catStyle    = $_SESSION["catStyle"];
        $this->grpStyle    = $_SESSION["grpStyle"];
        $this->legStyle    = $_SESSION["legStyle"];
        $this->grpInfoLink = $_SESSION['grpInfoLink'];
        $this->catInfoLink = $_SESSION['catInfoLink'];
        $this->grouplist   = $_SESSION["grouplist"];
        $this->defGroups   = $_SESSION["defGroups"];
        $this->allGroups   = $_SESSION["allGroups"];
        $this->icoW        = $_SESSION["icoW"];  // Width in pixels
        $this->icoH        = $_SESSION["icoH"];  // Height in pixels
        $this->imgExt      = $_SESSION["imgFormatExt"];
        $this->scaleLayers = $_SESSION["scaleLayers"];
        $this->scale       = $_SESSION['geo_scale'];
        
        $this->catWithCheckbox = $_SESSION['catWithCheckbox'];
    
        // GET LAYERS FOR DRAWING AND IDENTIFY
        if (isset ($_SESSION["groups"]) && count($_SESSION["groups"]) > 0){
            $this->groups = $_SESSION["groups"];
        } else {
            $this->groups = $this->defGroups;
        }    
    }
    
    
    function writeToc()
    {
        $toc = "";
        $tocbut = "";
                
        if ($_SESSION['layerAutoRefresh'] == 0) {    
            $tocbut .= $this->printButton(_p("Refresh Map"), "changeLayersDraw()");         
        }
        
        // Legend style POPUP: add 'show' button        
        if ($this->legStyle == "popup" && !$this->legendonly) {    
            $tocbut .= $this->printButton(_p("Show Legend"), "showPopupLegend()");         
        } 
        
        // Legend stle SWAP: add 'show' button
        /*
        if ($this->legStyle == "swap") {    
            if (!$this->legendonly) {
                $tocbut .=  $this->printButton(_p("Show Legend"), "swapToLegendView()");
            } else {
                $tocbut .=  $this->printButton(_p("Show Layers"), "swapToLayerView()");
            }       
        }
		*/
        
        $tabclass = $this->legendonly ? "class=\"legtab\"" : "";   
        $catW     = $this->legStyle == "attached" ?  "100" : "97";
        $toc .= "<table width=\"$catW%\" $tabclass cellspacing=\"0\" cellpadding=\"0\">";
        
        // using CATEGORIES
        if ($this->categories && !$this->legendonly) {
            $toc .= $this->_printCategory();
        
        // NO categories
        } else {
            foreach ($this->grouplist as $grp){
                if ($this->legendonly) {
                    if (in_array($grp->getGroupName(), $this->groups) && $this->checkGroup($this->map, $grp, $this->scale)) {
                        $toc .= $this->_printGroup($grp);
                    }
                } else {
                    $toc .= $this->_printGroup($grp);
                }
            }
        }
        
        $toc .= "</table>";
        
        // Add dummy image for onLoad event to initialize tree or reset checkboxes of groups
        if ((($this->grpStyle == "tree" && $this->legStyle == "attached") || $this->catStyle == "tree" ) && !$this->legendonly) {            
            $toc .= "<script type=\"text/javascript\">treeInit(\"$this->catStyle\", \"$this->grpStyle\");</script>";
        } elseif (!$this->legendonly) {            
            $toc .= "<script type=\"text/javascript\">setDefGroups();tocPostLoading();</script>";
        }
        
        //error_log($toc);
        $tocList['toc'] = $toc;
        $tocList['tocbut'] = $tocbut;
        
        return $tocList;
    }  
    
    
    function printButton($value, $jsFunction) 
    {
        $bstr  = "<div class=\"TOOLFRAME\">";
        $bstr .= "<input type=\"button\" value=\"$value\"  id=\"legbutton\" onclick=\"$jsFunction\"";  //style=\"margin:3px 0px 5px 0px;width:100%; height:22px\"
        $bstr .= "onmouseover=\"changeButtonClr(this, \\'over\\')\" onmouseout=\"changeButtonClr (this, \\'out\\')\"";
        $bstr .= " /></div>";  
        return $bstr; 
    }
    
    
    
    function _printCategory()
    {
        $cstr = "";
        foreach ($this->categories as $cat=>$catL) {
            $catDescr = addslashes(_p($cat));
            $cstr .= "<tr>";
            
                // Add open/close icon for TREE style
                if ($this->catStyle == "tree") {
                    $cstr .= "<td class=\"opcl\"><img src=\"images/tree/plus.gif\" id=\"" . $cat . "_timg\" onclick=\"tg(\\'$cat\\')\" alt=\"\" /></td>";
                }
                if ($this->catWithCheckbox) {
                    $cstr .= "<td class=\"cbx\"><input type=\"checkbox\" name=\"catscbx\" value=\"$cat\" id=\"cinput_$cat\" onclick=\"javascript:setcategories(\\'$cat\\')\" /></td>";
                }
                $cstr .= "<th colspan=\"2\" class=\"cat\" id=\"tcat_$cat\">";
                
                // Add open/close HREF for TREE style
                if ($this->catStyle == "tree") {      
                    if ($this->catInfoLink == 1) {
                        $cstr .= "<a href=\"javascript:showCategoryInfo(\\'$cat\\')\">$catDescr</a>";
                    } else {
                        $cstr .= "<a href=\"javascript:tg(\\'$cat\\')\">$catDescr</a>";
                    }
                } else {
                    if ($this->catInfoLink == 1) {
                        $cstr .= "<a href=\"javascript:showCategoryInfo(\\'$cat\\')\">$catDescr</a>";
                    } else {
                        $cstr .= "$catDescr";
                    }
                }
                if ($this->catInfoLink == 2) {
                    $cstr .= "<a href=\"javascript:showCategoryInfo(\\'$cat\\')\"><img src=\"images/infolink.gif\" alt=\"\" /></a>";
                }
                $cstr .= "</th>";
                
                
            $cstr .= "</tr>";
            $cstr .= "<tr>";
            $cstr .= "<td></td>";
            if ($this->catWithCheckbox) $cstr .= "<td></td>";            
            $cstr .= "<td><div class=\"catc\" id=\"$cat\">";
            $cstr .= "<table class=\"TOC\"  width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">";
            
            foreach ($this->grouplist as $grp){
                if (in_array($grp->groupName, $catL, TRUE)) {
                    $cstr .= $this->_printGroup($grp);
                }
            }
            $cstr .= "</table></div></td></tr>";
        }
        
        return $cstr;
    }
    
       

    function _printGroup($grp)
    {
        $grpName = $grp->getGroupName();
        $grpDescr = addslashes($grp->getDescription());
        $glayerList = $grp->getLayers();
        
        // Settings for groups with raster layer without classes
        $ltype = 0; 
        $numClassesGrp = 0;       
        foreach ($glayerList as $glayer) {
            $skipLegend = $glayer->getSkipLegend();
            $numClassesGrp += count($glayer->getClasses());
            $legLayer = $this->map->getLayer($glayer->getLayerIdx());
            //if ($legLayer->getMetadata("LEGENDICON")) $numClassesGrp += 1;
        }
        
        $gstr = "<tr>";
            
            // Add open/close IMG icon for TREE style
            if ($this->grpStyle == "tree" && $this->legStyle == "attached" && !$this->legendonly) {
                $ocImg = $numClassesGrp > 0 ? "plus" : "empty";
                //error_log("$grpName  $ocImg");
                $gstr .= "<td class=\"opcl\"><img alt=\"\" src=\"images/tree/$ocImg.gif\" id=\"" . $grpName . "_timg\" onclick=\"tg(\\'$grpName\\')\" /></td>";
            }
            
            // Add  <input type="checkbox"  >
            if (!$this->legendonly) {
                $gstr .= "<td class=\"cbx\"><input type=\"checkbox\" name=\"groupscbx\" value=\"$grpName\" id=\"ginput_$grpName\" onclick=\"javascript:setlayers(\\'$grpName\\',false)\" /></td>";
                $grpcolspan = "";
            } else {
                $grpcolspan = " colspan=\"2\"";
            }
            
            // Add open/close HREF for TREE style
            $gstr .= "<th class=\"grp\" $grpcolspan id=\"tgrp_$grpName\">";
            if ($this->grpStyle == "tree" && $this->legStyle == "attached" && !$this->legendonly && $numClassesGrp > 0) {      
                if ($this->grpInfoLink == 1) {
                    $gstr .= "<a href=\"javascript:showGroupInfo(\\'$grpName\\')\"><span class=\"vis\" id=\"spxg_$grpName\">$grpDescr</span></a>";
                } else {
                    $gstr .= "<a href=\"javascript:tg(\\'$grpName\\')\"><span class=\"vis\" id=\"spxg_$grpName\">$grpDescr</span></a>";
                }
            } else {
                if ($this->grpInfoLink == 1) {
                    $gstr .= "<a href=\"javascript:showGroupInfo(\\'$grpName\\')\"><span class=\"vis\" id=\"spxg_$grpName\">$grpDescr</span></a>";
                } elseif (!$this->legendonly || $skipLegend <= 1) {
                    $gstr .= "<span class=\"vis\" id=\"spxg_$grpName\">$grpDescr</span>";
                }
            }
            
            if ($this->grpInfoLink == 2) {
                $gstr .= "<a href=\"javascript:showGroupInfo(\\'$grpName\\')\"><img src=\"images/infolink.gif\" alt=\"\" /></a>";
            }
            $gstr .= "</th>";
            
        $gstr .= "</tr>";
        
        // Create CLASS entries for all LAYERS
        if ($this->legStyle == "attached" || $this->legendonly) {
            if (!$this->legendonly && $numClassesGrp > 0) {
            // If not only legend and if group has classes: add DIV for tree
                $colspan = ($this->grpStyle == "tree" ? 2 : 1);
                $gstr .= "<tr>";
                $gstr .= "<td colspan=\"$colspan\"></td>";
                $gstr .= "<td>";
                $gstr .= "<div class=\"grpc\" id=\"$grpName\">";
                $gstr .= "<table width=\"100%\"  class=\"legtab\" cellspacing=\"0\" cellpadding=\"0\">";
            }
            
            $count = 0;
            foreach ($glayerList as $glayer) {
                $legLayer = $this->map->getLayer($glayer->getLayerIdx());
                $legLayerName = $legLayer->name;
                $legLayerType = $legLayer->type;
                $legIconPath = $legLayer->getMetadata("LEGENDICON");
                $skipLegend = $glayer->getSkipLegend();
                $numClassesLay = count($glayer->getClasses());
                                            
                // All layers but RASTER layers WITHOUT class definitions
                if ((($legLayer->type < 3 && $skipLegend < 1) || $numClassesLay > 0) && $skipLegend != 2) {
    
                    $classes = $glayer->getClasses();
                    $clno = 0;
                    foreach ($classes as $cl) {
                        $legIconPath = $legLayer->getClass($clno)->keyimage; 
                        $icoUrl = $legIconPath ? $legIconPath : $this->legPath.$legLayerName.'_i'.$clno.'.'.$this->imgExt;
                        
                        $gstr .= "<tr>";
                        $gstr .= "<td style=\"width: " . $this->icoW . "px\"><img alt=\"legend\" src=\"$icoUrl\" width=\"$this->icoW\" height=\"$this->icoH\" /> </td>";
                        $gstr .= "<td><span class=\"vis\" id=\"spxg_$grpName$count$clno\">" . addslashes($cl) . "</span></td>";
                        $gstr .= "</tr> ";
                        $clno++;
                    }
                    
                    //#$totalClno += $clno;  // used for condition adding group to 'mainNodes' JS array (see below)
                }
                
                $count++;
            }
            
            if (!$this->legendonly && $numClassesGrp > 0) $gstr .= "</table></div></td></tr>";
        }
        
        return $gstr;
    
    }



    // CHECK IF GROUP HAS VISIBLE LAYER AT CURRENT SCALE
    function checkGroup($map, $grp, $scale)
    {
        $printGroup = 0;
        $glayerList = $grp->getLayers();
        foreach ($glayerList as $glayer) {
            $tocLayer = $map->getLayer($glayer->getLayerIdx());
            if ((checkScale($map, $tocLayer, $scale) == 1) && $tocLayer->type != 5) {
                $printGroup = 1;
            }
        }
        return $printGroup;
    } 

}



function writeJSArrays()
{
    $mutualDisableList = $_SESSION['mutualDisableList'];   
    if (count($mutualDisableList) > 0) {
        $js_array = "PMap.mutualDisableList = ['" . implode("','", $mutualDisableList) . "'];";
    } else {
        $js_array = "PMap.mutualDisableList = false;";
    }   
    
    if (isset($_SESSION["groups"]) && count($_SESSION["groups"]) > 0) {
        $defGroups   = $_SESSION["groups"];
    }else{
        $defGroups   = $_SESSION["defGroups"];
    }
    
    $js_array .= "\nPMap.defGroupList = ['ginput_" . implode("','ginput_", $defGroups) . "'];";


    return $js_array;
}







?>