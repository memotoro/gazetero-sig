
/******************************************************************************
 *
 * Purpose: sets the layout parameters (width/height, left/top) of the GUI  
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


function pmLayout_init() {
    Layout.update();
    //if ($.browser.mozilla) 
    tocResizeUpdate();
}


function PM_Layout2(){}


PM_Layout.prototype.update = function () 
{
    
    // Browser window inner dimensions
    var winix = 0, winiy = 0;
    if ( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
        winix = window.innerWidth;
        winiy = window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
        //IE 6+ in 'standards compliant mode'
        winix = parseInt(document.documentElement.clientWidth);
        winiy = parseInt(document.documentElement.clientHeight);
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
        //IE 4 compatible
        winix = document.body.clientWidth;
        winiy = document.body.clientHeight;
    }
    
    
    if (_$('pm_master')) {
        var pm_master = $('#pm_master');
        if (this.MasterResize) {
            var masterW = winix - (this.MasterLeft + this.MasterMarginE);
            var masterH = winiy - (this.MasterTop + this.MasterMarginS);
        } else {
            var masterW = this.MasterWidth;
            var masterH = this.MasterHeight;
        }
        
        this.setParams(pm_master, this.MasterLeft, this.MasterTop, masterW, masterH);
    } else {
        var masterW = winix;
        var masterH = winiy;
    }

    
    var north = $('#north');
    var south = $('#south');
    var west  = $('#west');
    var east  = $('#east');
    
    var mapZone  = $('#mapZone');
    var map      = $('#map');
    var mapNorth = $('#mapNorth');
    var mapSouth = $('#mapSouth');
    var mapWest  = $('#mapWest');
    var mapEast  = $('#mapEast');
    
    var mapimgLayer = $('#mapimgLayer');
    var mapImg      = $('#mapImg');
    
    var loading = $('#loading');
    
    var refZone  = $('#refZone');
    var infoZone = $('#infoZone');
    
    
    // Get new dimensions and positions
    var innerElemH = masterH - (this.NorthHeight + this.SouthHeight + this.MarginOuterHorizN + this.MarginOuterHorizS);  
    var innerElemT = this.NorthHeight + this.MarginOuterHorizN;
    var InfoZoneTotH = this.InfoZoneHeight > 0 ? this.InfoZoneHeight + this.MarginInnerHoriz : 0;
    
    var mapZoneH = innerElemH - InfoZoneTotH;
    var mapZoneW = masterW - (this.MarginOuterVertW + this.MarginOuterVertE + this.MarginInnerVertW + this.MarginInnerVertE  + this.WestWidth + this.EastWidth);
    var mapZoneL = this.MarginOuterVertW + this.WestWidth + this.MarginInnerVertW;
    
    var mapT = innerElemT + this.MapNorthHeight;
    mapW = mapZoneW - (this.MapWestWidth + this.MapEastWidth);
    mapH = mapZoneH - (this.MapNorthHeight + this.MapSouthHeight);
    
    var mapWestEastH = this.MapWestEastFull ?  mapZoneH : mapH;
    var mapWestEastT = this.MapWestEastFull ? 0 : this.MapNorthHeight;
    
    var mapNorthSouthW = this.MapWestEastFull ? mapW : mapZoneW;
    var mapNorthSouthL = this.MapWestEastFull ? this.MapWestWidth : 0;
    
    var westH = (this.InfoZoneStyle == 1 ? innerElemH : mapZoneH) - (this.RefZoneHorizPos == 'w' ? this.RefZoneHeight + this.MarginInnerHoriz : 0); 
    var eastH = (this.InfoZoneStyle == 2 ? innerElemH : mapZoneH) - (this.RefZoneHorizPos == 'e' ? this.RefZoneHeight + this.MarginInnerHoriz : 0);; 
    var westT = (this.RefZoneHorizPos == 'w' ? (this.RefZoneVertPos == 'n' ? innerElemT + this.RefZoneHeight + this.MarginInnerHoriz : innerElemT) :  innerElemT); 
    var eastT = (this.RefZoneHorizPos == 'e' ? (this.RefZoneVertPos == 'n' ? innerElemT + this.RefZoneHeight + this.MarginInnerHoriz : innerElemT) :  innerElemT);

    var refZoneL = (this.RefZoneHorizPos == 'w' ? this.MarginOuterVertW : mapZoneL + mapZoneW + this.MarginInnerVertE);
    var refZoneT = (this.RefZoneVertPos == 'n' ? innerElemT : (this.RefZoneHorizPos == 'w' ? westT + westH + this.MarginInnerHoriz : eastT + eastH + this.MarginInnerHoriz ));
    var refZoneW = (this.RefZoneHorizPos == 'w' ? this.WestWidth : this.EastWidth);

    if (this.InfoZoneStyle == 1) {
        var infoZoneL = mapZoneL;
        var infoZoneW = mapZoneW + this.MarginInnerVertE + this.EastWidth;
    } else if (this.InfoZoneStyle == 2) {
        var infoZoneL = this.MarginOuterVertW;
        var infoZoneW = this.WestWidth + this.MarginInnerVertW + mapZoneW;
    } else {
        var infoZoneL = this.MarginOuterVertW;
        var infoZoneW = masterW - (this.MarginOuterVertW + this.MarginOuterVertE);
    }
    
    
    // Apply new settings to DIV objects    
    //   (obj, L, T, W, H)
    
    this.setParams(north, 0, 0, masterW, this.NorthHeight);
    //this.setParams(south, 0, 0, masterW, this.SouthHeight);
    south.left(0 + 'px');
    south.css('bottom', 0 + 'px');
    south.width(masterW + 'px');
    south.height(this.SouthHeight + 'px');
    
    this.setParams(west, this.MarginOuterVertW, westT, this.WestWidth, westH);
    this.setParams(east, mapZoneL + mapZoneW + this.MarginInnerVertE, eastT, this.EastWidth, eastH);
    
    this.setParams(mapZone, mapZoneL, innerElemT, mapZoneW, mapZoneH);
    this.setParams(map, this.MapWestWidth, this.MapNorthHeight, mapW, mapH);
    this.setParams(mapWest, 0, mapWestEastT, this.MapWestWidth, mapWestEastH);
    this.setParams(mapEast, this.MapWestWidth + mapW, mapWestEastT, this.MapEastWidth, mapWestEastH);
    this.setParams(mapNorth, mapNorthSouthL, 0, mapNorthSouthW, this.MapNorthHeight);
    this.setParams(mapSouth, mapNorthSouthL, this.MapNorthHeight + mapH, mapNorthSouthW, this.MapSouthHeight);
    
    this.setParams(refZone, refZoneL, refZoneT, refZoneW, this.RefZoneHeight);
    this.setParams(infoZone, infoZoneL, mapZoneH + this.NorthHeight + this.MarginOuterHorizN + this.MarginInnerHoriz, infoZoneW, this.InfoZoneHeight);

    this.setParams(mapimgLayer, 0, 0, mapW, mapH);
    this.setParams(mapImg, 0, 0, mapW, mapH);
    
    var loadimg = _$('loadingimg');
    this.setParams(loading, (mapW/2 - objW(loadimg)/2), (mapH/2 - objH(loadimg)/2), objW(loadimg), objH(loadimg));


    // Update Slider s1
    updateSlider_s1(mapW, mapH) ;
    
    // RELOAD MAP!!!
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+ '&mapW=' + mapW + '&mapH=' + mapH + '&zoom_type=zoompoint';

    // Timer-controlled resize of map for stupid IE resize event behaviour
    if (navigator.appName.indexOf("Microsoft")!=-1) {
        clearTimeout(PMap.resize_timer);
        PMap.resize_timer = setTimeout("updateMap('" + mapurl + "', '')",500);     
    } else {
        updateMap(mapurl, '');   
    }

}


PM_Layout.prototype.setParams = function (obj, L, T, W, H)
{
    if (obj) {
        obj.left(L + 'px');
        obj.top(T + 'px');
        obj.width(W + 'px');
        obj.height(H + 'px');
        
        // hide object if width or height == 0 px
        if (W < 1 || H < 1) obj.hidev();
    }
}
