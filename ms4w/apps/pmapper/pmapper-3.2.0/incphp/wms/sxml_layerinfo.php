<?php

/******************************************************************************
 *
 * Purpose: parses WMS capapilities
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

class WMS_CapabilitiesLayers
{
    
    function WMS_CapabilitiesLayers($xmlString)
    {
        // Load the XML source
        $this->dom = new DOMDocument;
        $this->dom->loadXML($xmlString);
        
        $this->wms = $this->wmscl_writeWMSLayers();
    }
    
    
    function wmscl_returnLayerDefinitions()
    {
        return $this->wms;
    }
    
    
   /**
    * write out WMS Layers definitions
    * 
    */    
    function wmscl_writeWMSLayers() {
        
        // SimpleXML parser
        $sxml = simplexml_import_dom($this->dom);
        
        // Version
        $_SESSION['wms_version'] = (string)$sxml['version']; //$wms_version;
        
        // Title
        $wms_title = $sxml->Service->Title;
        $wms['title'] = addslashes($wms_title);
        
        // Formats
        $cap_wms_formats = $sxml->Capability->Request->GetMap->Format;
        foreach ($cap_wms_formats as $f) {
            if (preg_match('/image/', $f)) {
                $wms_formats[] = "$f";
            }
        }
        $formatHTML = $this->wmscl_writeFormatHTML($wms_formats);
        $wms['formats'] = $formatHTML; 
        
        
        // SRS
        $layerInfo = $sxml->Capability->Layer;
        $capSrsList = $layerInfo->SRS;
        foreach ($capSrsList as $s) {
            $srsList[] = (string)$s;
        }
        $srsHTML = $this->wmscl_writeEpsgHTML($srsList);
        $wms['srs'] = $srsHTML;
        
        
        // Layers
        $layerList = $layerInfo->Layer;
        
        $this->idc = 1;
        $this->pidc = 1;
        
        //$this->csl = "    d.add(0,-1,'<span class=\"wms_title\">$wms_title</span>');\n";
        $this->csl = "    d.add(0,-1,'<input type=\"text\" id=\"wmsLayerTitle\" class=\"wms_title\" value=\"$wms_title\"></input>');\n";
        
        foreach ($layerList as  $layer) {
            $layerHTML = $this->wmscl_writeLayerHTML($layer, "0");
        }
        
        $wms['cs'] = $this->csl;
        $wms['csAbstr'] = $this->csAbstr;
        
        return $wms;
    }
    
    
   /**
    * write HTML for WMS Layers
    */   
    function wmscl_writeLayerHTML($layer, $nested)
    {
        $nestedLayers  = $layer->xpath('Layer');
        $layerName     = $layer->Name;
        $layerTitle    = addslashes($layer->Title);
        $layerAbstract = $layer->Abstract;
        $layerStyles   = $layer->Style;
        
        $cbx = count($nestedLayers) > 0 ? 0 : 2;
        
        $this->csl .= "    d.add($this->idc,$nested,'$layerTitle','javascript:showAbstract(\\'abstract_$layerName\\')','','','','','','$layerName','',$cbx);\n";
        
        $layPID = $this->idc;
        
        if (strlen($layerName) > 0 && count($nestedLayers) < 1) {
            $this->csl .= $this->wmscl_writeLayerStyleHTML($layerStyles, $layPID, $layerName);
        }
        $this->pidc++;
        $this->idc++;
        
    
        $this->csAbstr .= "<div class=\"layer_abstract\" id=\"abstract_$layerName\"><b>$layerTitle</b><br/>$layerAbstract</div>\n";
        
        // If layer is nested layer, add it below parent layer (layPID of parent layer)
        
        if (count($nestedLayers) > 0) { 
            foreach ($nestedLayers as $l) {
               $this->wmscl_writeLayerHTML($l, $layPID);
            }
        }
    }
    
   /**
    * write HTML for Layer Styles
    */ 
    function wmscl_writeLayerStyleHTML($layerStyles, $layPID, $layerName)
    {
        $addDefaultStyle = 1;
        foreach ($layerStyles as $style) {
            $styleName  = $style->Name;
            if (preg_match("/default/i", $styleName)) {
                $addDefaultStyle = 0;
            }
        }
        
        //Layer Styles
        if ($addDefaultStyle && count($layerStyles) < 1) {
            $this->idc++;
            $csty .= "    d.add($this->idc,$layPID,'Default','','','','','','','default000', 'style_$layerName', 1);\n";
        }
        
        $scnt = 0;
        foreach ($layerStyles as $style) {
            $rad = ($scnt < 1 ? 1.1 : 1);
            $styleName  = $style->Name;
            $styleTitle = $style->Title;
            $this->idc++;
            $csty .= "    d.add($this->idc,$layPID,'$styleTitle','','','','','','','$styleName', 'style_$layerName', $rad);\n";
            $scnt++;
        }
        
        return $csty;
    }


   /**
    * write HTML for available WMS image formats
    */ 
    function wmscl_writeFormatHTML($wms_formats)
    {
        $html = "<select name=\"imgformat\">";
        foreach ($wms_formats as $f) {
            $sel = (trim($f) == "image/jpeg" ? "selected" : "");
            $html .= "<option value=\"$f\" $sel >$f</option>";
        }
        $html .= "</select>";
        
        return $html;
    }
    
    
    /**
    * write HTML for available WMS image formats
    */ 
    function wmscl_writeEpsgHTML($epsg_codes)
    {
        include_once("epsg_list.inc");
        $html = "<select name=\"epsg\">";
        //$html .= "<option value=\"0\" $sel >" . _p("default") . "</option>";
        foreach ($epsg_codes as $e) {
            $epsg = trim(strtolower($e));
            $epsg_id = str_replace("epsg:", "", $epsg);
            $sridtxt = $epsgL[$epsg_id];
            
            //$sel = ($epsg == "epsg:4326" ? "selected" : "");
            $html .= "<option value=\"$epsg\" $sel >$epsg, $sridtxt</option>";
        }
        
        $html .= "</select>";
        
        return $html;
    }

}

?>