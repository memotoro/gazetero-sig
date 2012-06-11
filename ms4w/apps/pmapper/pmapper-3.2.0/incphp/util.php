<?php

/******************************************************************************
 *
 * Purpose: utilities, eg. toolbar creation
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


/**
 * WRITE ZOOM SLIDER
 */
function writeScaleslider($gSlide)
{
    for ($i=0;$i<count($gSlide);$i++) {
        $sln = $gSlide[$i];
        echo ("<td><a href=\"javascript:zoom2scale($sln)\"><img src=\"images/slider/".$i."_sl_off.gif\" name=\"slbut$sln\" border=0  width=12 title=\"1:$sln\" alt=\"1:$sln\"></a></td> \n");
    }
}



/**
 * PRINT TOOLBAR BUTTONS, NEW VERSION
 */
function writeButtons($buttons, $toolbarTheme="default", $toolBarOrientation="v", $toolbarImgType="gif", $cellspacing="4")
{   
    $html = "<table class=\"TOOLBAR\" border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"0\">\n";
    $html .= ($toolBarOrientation == "v" ? "" : "<tr>");

    foreach ($buttons as $b => $ba) {
        $html .= ($toolBarOrientation == "v" ? "<tr>" : "");

        if (preg_match("/^space/i", $b)) {
            $html .= "<td class=\"tsepspace\" style=" . ($toolBarOrientation == "v" ? "height:" : "width:") . $ba . "px\"> </td> ";
            
        } elseif (preg_match("/^separator/i", $b)) {
            $iewa = ($_SESSION['userAgent'] == "ie" ? "<img alt=\"separator\" src=\"images/blank.gif\" />" : "");
            if ($toolBarOrientation == "v") {
                $html .= "<td class=\"tsepv\" >$iewa</td> ";
            } else {
                $html .= "<td class=\"tseph\">$iewa</td> ";
            } 

        } else {
            $html .= "<td class=\"TOOLBARTD\" id=\"tb_$b\"  " . 
                    //($ba[1] == "0" ?  "onmousedown=\"setTbTDButton('$b');domouseclick('$b')\"" : "onmousedown=\"TbDownUp('$b','d')\" onmouseup=\"TbDownUp('$b','u')\"") .
                    ($ba[1] == "0" ?  "onmousedown=\"setTbTDButton('$b');\"" : "onmousedown=\"TbDownUp('$b','d')\" onmouseup=\"TbDownUp('$b','u')\"") .
                    " onclick=\"" . ($ba[1] == "0" ? "domouseclick('$b')" : "$ba[1]()") .  "\">" .
                    "<img id=\"img_$b\"  src=\"images/buttons/$toolbarTheme/$b"."_off.$toolbarImgType\" title=\"$ba[0]\" alt=\"$ba[0]\"  /></td>" ;
        }

        $html .= ($toolBarOrientation == "v" ? "</tr> \n" : "\n");
    }
    $html .= ($toolBarOrientation == "v" ? "" : "</tr> \n");
    $html .= "</table>";

    return $html;
}


/**
 * Create DHTML MENU
 */
function writeMenu($menu, $menuid, $menuname)
{
     $html  = "<a id=\"pm_". $menuid ."_start\" class=\"pm_menu_button\"   onclick=\"pmMenu_toggle('pm_$menuid');\">" . _p($menuname) . "<img src=\"images/menudown.gif\" alt=\"\" /></a>\n";
     $html .= "<ul id=\"pm_" . $menuid . "\" class=\"pm_menu\" >";
     foreach ($menu as $m => $ma) {
         $html .= "<li id=\"pmenu_" . $ma[1] . "\">" . $ma[0] . "</li>\n";      
     }
     $html .= "</ul>";
     return $html;   
}


/**
 * Create tabs for TOC/Legend
 */
function writeTocTabs($tablist, $enable=false)
{
    $tocTabs = "";
    if ($_SESSION['layerAutoRefresh'] == 0) {
        $tocTabs .= "<div id=\"autoRefreshButton\"></div>";
    }
    if ($_SESSION['legStyle'] == "swap" || $enable) { 
        $tocTabs .= "  <div id=\"tocTabs\">\n       <ul class=\"tocTabs\">\n";
        foreach($tablist as $k => $v) {
            $tocTabs .= "         <li><a href=\"javascript:" . $v[1] . "()\"  id=\"tab_$k\">" . $v[0] . "</a></li>\n";                
        }
		$tocTabs .= "       </ul> \n     </div>\n";
    } else {
        $tocTabs .= "";
    }
    return $tocTabs;
}


function writeSearchContainer($style)
{
    $html  = "<table class=\"pm_searchcont\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
    $html .= "<tr>";  
    $html .= "<td id=\"searchoptions\" class=\"pm_searchoptions\" style=\"padding:0px 8px\"></td>";  
    if ($style == "block") $html .= "</tr><tr>";    
    $html .= "<td id=\"searchitems\" class=\"pm_search_$style\"></td>";
    $html .= "</tr>";  
    $html .= "</table>";
    
    return $html;
}





?>
