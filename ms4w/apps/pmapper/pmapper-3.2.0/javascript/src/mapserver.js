
/******************************************************************************
 *
 * Purpose: main interaction with Mapserver specific requests 
 *          like zoom, pan, etc. 
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


//********************************************************
// CONFIGURATION
//********************************************************/


// SET TO TRUE IF CURSOR SHALL CHANGE ACCORDING TO ACTIVE TOOL
var useCustomCursor = true;


//******************************************************* //
//         JAVASCRIPT FUNCTIONS FOR ZOOM, QUERY,          //
//******************************************************* //

/* Specifies how far (in pixels) a user needs to drag the mouse
 * to enable zoom to rectangle, otherwise zoom to point.
 * Should be set to >3
 ****************************************************************/
var jitter = 10;


/**
 * FUNCTION IS CALLED BY ZOOMBOX -> FUNCTION chkMouseUp(e)
 * main function for zoom/pan interface
 * calls different zoom functions (see below)
 */
function zoombox_apply(minx, miny, maxx, maxy) {
    var imgbox = minx + "+" + miny + "+" + maxx + "+" + maxy;
    var imgxy  = minx + "+" + miny;

    // NORMAL MOUSE ACTIONS IN MAIN MAP //
    if (refmapClick == false) {

        // ZOOM/PAN ACTIONS
        var varform = _$("varform");
        var vmode = varform.mode.value;
        
        if (vmode == 'map' || rightMouseButton) {
            showloading();
            // Only click
            if ((minx + jitter) > maxx && (miny + jitter) > maxy) {
                if (varform.zoom_type.value == 'zoomrect') {
                    if (rightMouseButton) {
                        zoom_factor = 1; 
                    } else {
                        zoom_factor = 2;
                    }
                    zoompoint(zoom_factor, imgxy);
                    
                } else {
                   // Pan
                   var zoom_factor = varform.zoom_factor.value;
                   zoompoint(zoom_factor, imgxy);
                }
            
            // Zoombox 
            } else {
                zoomin(imgbox);
            }

        // QUERY/IDENTIFY ACTIONS
        // query on all visible groups
        } else if (vmode == 'query') {
            showqueryresult('query', imgxy);
        // query only on selected group with multiselect
        } else if (vmode == 'nquery') {
        	var selform = _$("selform");
            if (!selform.selgroup) return false;
            if (selform.selgroup.selectedIndex != -1) {
                // only with single click
                if ((minx + jitter) > maxx && (miny + jitter) > maxy) {     // x/y point
                    showqueryresult('nquery', imgxy);
                // with zoom box
                } else {
                    showqueryresult('nquery', imgbox);                      // rectangle
                }
            }
        } else if (vmode == 'poi') {
            openPoiDlg(imgxy);
        } else {
            try {
                eval(vmode + '_start(imgbox)');
                return false;
            } catch(e) {
            
            }
        }

    // ACTIONS IN REF MAP //
    } else {
        zoomref(imgxy);
    }
}



/**
 * Zoom to point
 */
function zoompoint(zoomfactor, imgxy) {
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoompoint&zoom_factor='+zoomfactor+'&imgxy='+imgxy;
    showloading();
    updateMap(mapurl);
}

/**
 * Zoom to rectangle
 */
function zoomin(extent) {
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomrect&imgbox='+extent  ;
    //alert(mapurl);
    updateMap(mapurl);
}

/**
 * Zoom to geo-extent (map units), applied from info page link
 */
function zoom2extent(layer,idx,geoextent) {
    showloading();
    // Check if resultlayers shall be passed
    if (layer == 0 && idx == 0) {                            // no
        var layerstring = '';
    } else {
        var layerstring = '&resultlayer='+layer+'+'+idx;     // yes
    }
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomextent&extent='+geoextent+layerstring;
    //document.varform.zoomselected.value = '1';
    updateMap(mapurl);
}

/**
 * Zoom to full extent
 */
function zoomfullext() {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomfull';
    updateMap(mapurl);
}

/**
 * Go back to pevious extent
 */
function goback() {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomback';
    updateMap(mapurl);
}

/**
 * Go forward
 */
function gofwd() {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomfwd';
    updateMap(mapurl);
}

/**
 * Zoom to layer/group
 */
function zoom2group(gid) {
    showloading();
    var groupname = gid.substr(5);
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomgroup&groupname=' + groupname;
    updateMap(mapurl);
}

/**
 * Zoom to selection
 */
function zoom2selected() {
    if (typeof(PMap.extentSelectedFeatures)!='undefined') {
        if (PMap.extentSelectedFeatures) {
            showloading();
            var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomextent&extent='+PMap.extentSelectedFeatures;
            updateMap(mapurl);
            //var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomselected';
            //updateMap(mapurl);
        }
    }
}


/**
 * Draw map with new layers/groups
 */
function changeLayersDraw() {
   	showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint';
    updateMap(mapurl);
}


/**
 * Syop loading on click
 */
function clickStopLoading() {
    stoploading();
    if (document.all) { 
        document.execCommand('Stop')
    } else {
        window.stop();
    }
}




/**
 * Pan via arrow buttons or keyboard
 */
function arrowpan(direction) {
    showloading();
    var pansize = 0.1;   // defines how much to pan
    var px, py;
    if (direction == 'n') {
        px = (mapW - 1) / 2;
        py = (0 + pansize) * mapH;
    } else if (direction == 's') {
        px = (mapW - 1) / 2;
        py = (1 - pansize) * mapH;
    } else if (direction == 'e') {
        px = (1 - pansize) * mapW;
        py = (mapH - 1) / 2;
    } else if (direction == 'w') {
        px = (0 + pansize) * mapW;
        py = (mapH - 1) / 2;
    }
    
    zoompoint(1, px + "+" + py);
}


/**
 * Reference image zoom/pan
 */
function zoomref(imgxy) {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=ref&imgxy='+imgxy  ;
    updateMap(mapurl);
}

/**
 * Set overview image to new one
 */
function setRefImg(refimgsrc){
     var refimg = parent.refFrame.document.getElementById('refimg');
     refimg.src = refimgsrc;
}


/**
 * Zoom to scale
 */
function zoom2scale(scale) {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&mode=map&zoom_type=zoomscale&scale='+scale;
    updateMap(mapurl);
}

/**
 * Write scale to input field after map refresh
 */
function writescale(scale) {   
    if (_$("scaleform")) _$("scaleform").scale.value = scale;
}




/**
 * Mouse click button functions (for toolbar)
 */
function domouseclick(button) {
	var varform = _$("varform");
    resetFrames();
    switch (button) {
    case 'zoomin':
        varform.mode.value = 'map';
        varform.zoom_type.value = 'zoomrect';
        varform.maction.value = 'box';
        varform.tool.value = 'zoomin';
        break;
    case 'zoomout':
        varform.mode.value = 'map';
        varform.zoom_type.value = 'zoompoint';
        varform.zoom_factor.value = '-2';
        varform.maction.value = 'click';
        varform.tool.value = 'zoomout';
        break;
    case 'identify':
        varform.mode.value = 'query';
        varform.maction.value = 'click';
        varform.tool.value = 'identify';
        break;
    case 'pan':
        varform.mode.value = 'map';
        varform.zoom_type.value = 'zoompoint';
        varform.zoom_factor.value = '1';
        varform.maction.value = 'pan';
        varform.tool.value = 'pan';
        break;
    case 'select':
        varform.mode.value = 'nquery';
        varform.maction.value = 'box';
        var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+SID;
        updateSelectTool(selurl, '');
        //_$('loadFrame').src = selurl;
        varform.tool.value = 'select';
        break;
    case 'auto_identify':
        varform.mode.value = 'iquery';
        varform.maction.value = 'move';
        varform.tool.value = 'auto_identify';
        var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+SID+'&autoidentify=1';
        updateSelectTool(selurl, '');
        break;
    case 'measure':
        varform.maction.value = 'measure';
        varform.mode.value = 'measure';
        varform.tool.value = 'measure';
        createMeasureInput();
        break;
    case 'digitize':
        varform.mode.value = 'digitize';
        varform.maction.value = 'click';
        varform.tool.value = 'digitize';
        break;
    case 'poi':
        varform.mode.value = 'poi';
        varform.maction.value = 'click';
        varform.tool.value = 'poi';    
        break;
    default:
        // for anything else (new) apply function 'button_mclick()'
        try {
            eval(button + '_click()');
            return false;
        } catch(e) {
        
        }
    }
    
    // Set cursor appropriate to slected tool 
    if (useCustomCursor) {
        setCursor(false, false);
    }
}

/**
 * custom sample script for extending tool functions
 */
function poi_mclick() {
    var varform = _$("varform");
    varform.mode.value = 'poi';
    varform.maction.value = 'click';
    varform.tool.value = 'poi'; 
    
    if (useCustomCursor) {
        setCursor(false, 'crosshair');
    }
}


/**
 * Called by various activated tools to disable certain displayed features for measure and select
 */
function resetFrames() {
	hideHelpMessage();
    var varform = _$("varform");
    if (varform.mode.value == 'nquery' || varform.mode.value == 'iquery' || varform.maction.value == 'measure') {
        if (varform.maction.value == 'measure') {
            resetMeasure();
        }
        if (varform.mode.value == 'iquery' || varform.mode.value == 'nquery') hideObj(_$('iqueryLayer'));
        
    } else {
        $('#mapToolArea').html('');
    }
}


/**
 * Create the measure input elements
 */
function createMeasureInput() {
    var mStr =  '<form name="measureForm"><table class="TOOLFRAME"><tr><td NOWRAP>' + localeList['Total'] + pmMeasureUnits.distance + '</td><td><input type=text size=9 name="sumLen"></td>';
    mStr += '<td id="mSegTxt" value="&nbsp;&nbsp;' + localeList['Segment'] + '" NOWRAP>&nbsp;&nbsp;' + localeList['Segment'] + pmMeasureUnits.distance + '</td><td><input type=text size=9 name="segLen"></td>';
    mStr += '<td width=130 class="TDAR"><input type="button" id="cbut_measure" value="' + localeList['Clear'];
    //mStr += '"  class="button_off"  onClick="javascript:clearMeasure()" onmouseover="changeButtonClr(this, \'over\')" onmouseout="changeButtonClr (this, \'out\')" >';
    mStr += '"  class="button_off"  name="custombutton" onClick="javascript:clearMeasure()" >';
    mStr += '</td></tr></table></form>';
    
    $('#mapToolArea').html(mStr);
    pmCButton_init('cbut_measure');
    showHelpMessage(localeList['digitize_help']);
}


/**
 * Reload application
 */
function reloadMap(remove) {
    showloading();
    var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint';
    if (remove) {
        mapurl += '&resultlayer=remove';
        PMap.extentSelectedFeatures = null;
    }
    updateMap(mapurl);
}


/**
 * Show help message over map
 */
function showHelpMessage(hm) {
    $('#helpMessage').html(hm).showv();
}

/**
 * Hide help message over map
 */
function hideHelpMessage() {
    $('#helpMessage').html('').hidev();
}


/**
 * Close info win and unregister session var 'resultlayer'
 */
function clearInfo() {
	var varform = _$("varform");
    varform.zoomselected.value = '0';
        reloadMap(true);
}




/**
 * Show loading splash image
 */
function showloading(){    // waiting/working gif-animation
    $("#loading").showv();
}

/**
 * Hide loading splash image
 */
function stoploading(){
    $('#loading').hidev();
}


function pmIndicator_show(x, y) {
    if (x) {
        $('#pmIndicatorContainer').css({top: parseInt(y) + offsY - 35 +'px', left: parseInt(x) + offsX - 15 +'px'}).show();
    } else {
        $('#pmIndicatorContainer').css({top:'5px', right:'5px'}).show();
    }
}

function pmIndicator_hide() {
    $('#pmIndicatorContainer').hide();
}


/**
 * Set slider image depending on scale
 * Values defined in 'config.ini'
 */
function setSlider(curscale) {
    if (myslider) {
        var sliderPos = getSliderPosition(curscale);
        myslider.setPosition(sliderPos);
        if (_$('refsliderbox')) hideObj(_$('refsliderbox'));
    }
    return false;
}



