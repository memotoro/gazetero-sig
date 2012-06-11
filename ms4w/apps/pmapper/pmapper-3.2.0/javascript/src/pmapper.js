
/******************************************************************************
 *
 * Purpose: core p.mapper functions (init, user interaction, open popups) 
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

/**
 * Locales function to get locale string
 */
function _p(str) {
    return localeList[str];
}


/** 
 * LOAD MAP IMAGE INTO PARENT WINDOW MAP DIV
 */
function loadMapImg(mapImgSrc) {
    // SWAP MAP IMG
    var theMapImg = _$("mapImg");
    theMapImg.src = mapImgSrc;
}

/**
 * Reset parameters of some DIV's
 */
function resetMapImgParams() {
    var theMapImgL = _$("mapimgLayer");
    var theMapImg  = _$("mapImg");
    
    theMapImg.style.width = mapW+"px";
    theMapImg.style.height = mapH+"px";
    
    theMapImgL.style.top  = 0+"px";  
    theMapImgL.style.left = 0+"px";
    theMapImgL.style.width = mapW+"px";
    theMapImgL.style.height = mapH+"px";
    
    theMapImgL.style.clip = 'rect(auto auto auto auto)';  // NEEDED TO RESET DIV TO NON-CLIPPED AND ORIGINAL SIZE
    
    $('#zoombox').hidev();
    $('#loading').hidev();
    
    maploading = false;
    
    var varformMode = _$("varform").mode.value;
    if (varformMode == 'measure') {
        resetMeasure();
        polyline = toPxPolygon(geoPolyline);
        if (polyline.getPointsNumber()>0) {
            drawPolyline(jg,polyline);
        }
    }
}


/**
 * Update s1 value for slider settings
 */
function updateSlider_s1(pixW, pixH) {
    var maxScale1 = ((PMap.dgeo_x * PMap.dgeo_c) / pixW) / (0.0254 / 96);
    var maxScale2 = ((PMap.dgeo_y * PMap.dgeo_c) / pixH) / (0.0254 / 96);
    PMap.s1 = Math.max(maxScale1, maxScale2);
}



/*****************************************************************************
 * SWAP FUNCTIONS FOR TOOLBAR TD -> USE ALTERNATIVELY TO IMAGE SWAP
 * Changes TD class (default.css -> .TOOLBARTD...) in toolbar
 ********************************************************************/
/**
 * Function for state buttons (CLICKED TOOLS: zoomin, pan, identify, select, measure)
 * set class for active tool button
 */
function setTbTDButton(button) {
    if (PMap.tbImgSwap != 1) {
        $("#mapZone .TOOLBARTD").addClass('TOOLBARTD_OFF').removeClass('TOOLBARTD_ON');
        $('#tb_' + button).removeClass('TOOLBARTD_OFF').addClass('TOOLBARTD_ON').removeClass('TOOLBARTD_OVER');
    } else {
        $("#mapZone .TOOLBARTD").each(function() {
            //$(this).addClass('TOOLBARTD_OFF').removeClass('TOOLBARTD_ON');
            $(this).find('>img').imgSwap('_on', '_off');
        });
        $('#tb_' + button).find('>img').imgSwap('_off', '_on').imgSwap('_over', '_on');
    }
}

/**
 * MouseDown/Up, only set for stateless buttons
 */
function TbDownUp(elId, status){
    var but = $('#tb_' + elId);
    if (status == 'd') {
        if (PMap.tbImgSwap != 1) {
            but.addClass('TOOLBARTD_ON').removeClass('TOOLBARTD_OFF').removeClass('TOOLBARTD_OVER');
        } else {
            but.find('>img').imgSwap('_off', '_on').imgSwap('_over', '_on');
        }
    } else {
        if (PMap.tbImgSwap != 1) {
            but.addClass('TOOLBARTD_OFF').removeClass('TOOLBARTD_ON').addClass('TOOLBARTD_OVER');
        } else {
            if (PMap.tbImgSwap == 1) but.find('>img').imgSwap('_on', '_off');
        }
    }
}


function changeButtonClr(myObj, myAction) {
    switch (myAction) {
        case 'over':
            myObj.className = 'button_on';
            break;
            
        case 'out':
            myObj.className = 'button_off';
            break;
    }
}



/**************************************************
 * Set cursor symbol according to tool selection
 *************************************************/
/**
 * return root path of application
 */
function getRootPath() {
	var theLoc = document.location.href;
	var theLastPos = theLoc.lastIndexOf('/');
	var RootPath = theLoc.substr(0,theLastPos) + '/';
	
	return RootPath;
}

/** 
 * set the cursor to standard internal cursors
 * or special *.cur url (IE6+ only)
 */
function setCursor(rmc, ctype) {	
    if (!rmc) {
    	var varform = _$("varform");
        if (varform) {
            var toolType = varform.tool.value;
        } else {
            var toolType = 'zoomin';
        }
    } else {
        toolType = 'pan';
    }

    // take definition from ja_config.php 
    try {
        var iC = pmUseInternalCursors;
    } catch(e) {
        var iC = true;
    } 
    
    var rootPath = getRootPath();
    var usedCursor = (iC) ? toolType : 'url("' +rootPath + 'images/cursors/zoomin.cur"), default';
    
    _$('mapimgLayer').style.cursor = usedCursor;
    
    switch (toolType) {
		case "zoomin" :
			var usedCursor = (iC) ? 'crosshair' : 'url("' +rootPath + 'images/cursors/zoomin.cur"), default';	
            break;
        
        case "zoomout" :
			var usedCursor = (iC) ? 'e-resize' : 'url(' +rootPath + 'images/cursors/zoomout.cur), default';	
			break;
        
        case "identify" :
			//var usedCursor = (iC) ? 'help' : 'url(' +rootPath + 'images/cursors/identify.cur), default';	
			var usedCursor = 'help';	
            break;
        
        case "auto_identify" :	
			var usedCursor = 'pointer';	
            break;

        case "pan" :
			//var usedCursor = (iC) ? 'move' : 'url(' +rootPath + 'images/cursors/pan.cur), default';	
            var usedCursor = 'move';
			break;
            
        case "select" :
			//var usedCursor = (iC) ? 'help' : 'url(' +rootPath + 'images/cursors/select.cur), default';
            var usedCursor = (iC) ? 'help' : 'help';	            
			break;
            
        case "measure" :
			var usedCursor = (iC) ? 'crosshair' : 'url(' +rootPath + 'images/cursors/measure.cur), default';	
			break;
            
        case "digitize" :
			var usedCursor =  'crosshair';	
			break;
            
        default:
            var usedCursor = 'default';
    }

    if (ctype) usedCursor = ctype;
    _$('mapimgLayer').style.cursor = usedCursor;
    
}




/**
 * OPEN RESULT WINDOW FOR IDEBNTIFY AND SEARCH
 */
function openResultwin(winurl) {
    try {
        if (queryResultLayout == 'tree') {
            var winw = 300;
            var winh = 450;
        } else {
            var winw = 500;
            var winh = 200;
        }
    } catch(e) {
        var winw = 500;
        var winh = 200;
    }
    
    var w = window.open(winurl, 'resultwin', 'width=' + winw + ',height=' + winh + ',status=yes,resizable=yes,scrollbars=yes');
    w.focus();
    return w;
}



/**
 * OPEN HELP WINDOW 
 */
function openHelp() {
    createDnRDlg({w:350, h:500, l:100, t:50}, {resizeable:true, newsize:true}, 'pmDlgContainer', localeList['Help'], 'Gazetero/help/help.phtml?'+SID);
}

function openCreditos() {
    createDnRDlg({w:350, h:500, l:100, t:50}, {resizeable:true, newsize:true}, 'pmDlgContainer', localeList['Creditos'], 'Gazetero/php/Creditos.php?'+SID);
}

/************************************************************************************
 * DOWNLOAD FUNCTIONS
 * get image with higher resolution for paste in othet programs
 ****************************************************************/
function openDownloadDlg() {
    createDnRDlg({w:260, h:220, l:200, t:200}, {resizeable:false, newsize:true}, 'pmDlgContainer', localeList['Download'], 'downloaddlg.phtml?'+SID );
}

function openDownload() {
    window.open("download.phtml?"+SID, "download");
}


/**
 * Open popup dialaog for adding POI 
 */
function openPoiDlg(imgxy) {
    var coordsList = imgxy.split('+');
    var mpoint = getGeoCoords(coordsList[0], coordsList[1], false);
    
    // Round values (function 'roundN()' in 'measure.js')
    var rfactor = 5;
    var px = isNaN(mpoint.x) ? '' : roundN(mpoint.x, rfactor);
    var py = isNaN(mpoint.y) ? '' : roundN(mpoint.y, rfactor);
    
    var inserttxt = prompt(localeList['addLocation'], '');
    if (inserttxt) {
        var digitizeurl = PM_XAJAX_LOCATION + 'x_poi.php?' +SID + '&up=' + px + '@@' + py + '@@' + inserttxt; //escape(inserttxt);
        //alert(digitizeurl);
        addPOI(digitizeurl);
    }
}



/******************************************************
 * PRINT FUNCTIONS
 ******************************************************/
 
/**
 * Open the printing dialog
 */
function openPrintDlg() {
   createDnRDlg({w:350, h:280, l:200, t:200}, {resizeable:true, newsize:true}, 'pmDlgContainer', localeList['Print_Settings'], 'printdlg.phtml?'+SID);
}

/**
 * Show advanced settings in print dialog
 */
function printDlgShowAdvanced() {
    $('div.printdlg_advanced').show();
    $('#printdlg_button_advanced').hide();
    $('#printdlg_button_normal').show();
    $('#pmDlgContainer').height(parseInt($('#printdlg').innerHeight()) + 60);
    adaptDWin($('#pmDlgContainer'));
}

/**
 * Show advanced settings in print dialog
 */
function printDlgHideAdvanced() {
    $('div.printdlg_advanced').hide();
    $('#printdlg_button_normal').hide();
    $('#printdlg_button_advanced').show();
    $('#pmDlgContainer').height(parseInt($('#printdlg').innerHeight()) + 60);
    adaptDWin($('#pmDlgContainer'));
}



