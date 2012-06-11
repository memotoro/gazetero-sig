<?php

/******************************************************************************
 *
 * Purpose: WMS class for querying WMS services and create WMS layers
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


class WMS_Client
{
    var $map;
    var $wms_tmpdir;
    var $wms_urlid;
    var $wms_context_FN;
    
    
    
    function WMS_Client($map)
    {
        $this->map = $map;
        $this->wms_tmpdir = $_SESSION['wms_tmpdir']; 
        
        $this->wms_createLayer();
        $this->wms_addLayer();
        
        $this->wms_registerService();
    }


    function wms_registerService()
    {
        if (isset($_SESSION['wms_grouplist'])) {
            $wms_grouplist = $_SESSION['wms_grouplist'];
        } 
        $wms_group = new WMS_Group($this->map, $this->wms_urlid);
        
        $wms_grouplist[$this->wms_urlid] = $wms_group;
        $_SESSION['wms_grouplist'] = $wms_grouplist;
    }
    
    
   /**
    * Add the WMS from context XML to map 
    */    
    function wms_addLayer()
    {
        $layerListBefore = $this->map->getAllLayerNames();
        $this->map->loadMapContext($this->wms_context_FN, MS_TRUE);
        $layerListAfter = $this->map->getAllLayerNames();
        
        $newWMSLayers = single_diff($layerListBefore, $layerListAfter);
        $_SESSION['newWMSLayers'] = $newWMSLayers;
        
    }
    

   /**
    * Create new WMS layer 
    */
    function wms_createLayer()
    {
        // Create map layer for current $map
        $layerStr  = $_REQUEST['layers'];
        $styleStr  = $_REQUEST['styles'];
        $this->imgformat = $_REQUEST['imgformat'];
        
        $this->wms_urlid = $_SESSION['wms_urlid'];
        $wms_title = $_SESSION['wms_title'];
        
        $wms_context = $this->wms_xsltTransform();
        $this->wms_createContextXML($wms_context);
        
    }
    
    
   /**
    * Parse capabilities with XSL 
    */
    function wms_xsltTransform()
    {
        // Load the capabilities XML source
        $tmpCapsFN = $this->wms_tmpdir . "tmpcaps_" . session_id() . ".xml";
        $xml = new DOMDocument;
        $xml->load($tmpCapsFN);
        
        // Configure the transformer
        $xsl = new DOMDocument; 
        $xsl->load('xsl/caps2Context.xsl');
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl); 
        
        // Modify parameters
        $proc->setParameter('http://www.opengis.net/context', 'defaultFormat', $this->imgformat);
        //$proc->setParameter("http://www.opengis.net/context", "selectedLayer", "//Capability/Layer/Layer[Name='global_mosaic']");
        $wms_context = $proc->transformToXML($xml);
        
        return $wms_context;
    }
    
   /**
    * Write Map Context XML file
    */
    function wms_createContextXML($wms_context)
    {
        // Create WMS Map context file in session directory
        $wms_urlid = $_SESSION['wms_urlid'];
        $this->wms_context_FN = $this->wms_tmpdir . $wms_urlid . "_" . session_id() . ".xml";
        $_SESSION['wms_context_FN'] = $this->wms_context_FN;
        
        $wms_context_fh = fopen($this->wms_context_FN, 'w');
        fwrite($wms_context_fh, $wms_context);
        fclose($wms_context_fh);
    }
    
       
}


class WMS_Group 
{
    var $wms_groupName;
    var $wms_description;
    var $wms_glayerList;
    
    function WMS_Group($map, $wms_urlid)
    {
        $this->wms_groupName  = basename($wms_urlid, ".xml");
        $this->wms_description = $_SESSION['wms_title'];
        
                
        
        foreach ($_SESSION['newWMSLayers'] as $wms_layName) {
            // Get layer info from map file
            $mapLay = $map->getLayerByName($wms_layName);
            $mapLayName = $mapLay->name;
            $mapLayType = $mapLay->type;
            $mapLayIndex = $mapLay->index;
    
            // Write layer properties to glayer object
            $wms_glayer = new WMS_GLayer($wms_layName);
            $wms_glayer->wmsgl_setLayerIdx($mapLayIndex);
            $wms_glayer->wmsgl_setLayerType($mapLayType);
            
            $this->wms_glayerList[] = $wms_glayer;
        
        }
    
    }
    
    
    function wmsg_getGroupName()
    {
        return $this->wms_groupName;
    }
    
    function wmsg_getGroupDesc()
    {
        return $this->wms_description;
    }

    function wmsg_getGlayerlist()
    {
        return $this->wms_glayerList;
    }
    
}





class WMS_GLayer 
{
    
    function WMS_GLayer($name)
    {
        $this->wmsgl_layerName = $name;
    }
    
    function wmsgl_setLayerIdx($mapLayIndex)
    {
        $this->wmsgl_layerIdx = $mapLayIndex;
    }
    
    function wmsgl_setLayerType($mapLayType)
    {
        $this->wmsgl_layerType = $mapLayType;
    }
    
    
    // Get properties
    function wmsgl_getLayerName()
    {
        return $this->wmsgl_layerName;
    }
    
    function wmsgl_getLayerIdx()
    {
        return $this->wmsgl_layerIdx;
    }
    
    function wmsgl_getLayerType()
    {
        return $this->wmsgl_layerType;
    }

}









?>