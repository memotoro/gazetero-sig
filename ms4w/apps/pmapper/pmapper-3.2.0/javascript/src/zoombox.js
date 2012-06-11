
/******************************************************************************
 *
 * Purpose: functions related to map navigation and mouse events  
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
 * GLOBAL VARIABLES
 */
var mouseDrag = false;    // TRUE when mouse is pressed
var maction;

var rightMouseButton = false;

var downX, downY;
var upX, upY;
var moveX, moveY;

//var offsX = 0; 	// horizontal image offset
//var offsY = 0;    // vertical image offset

var rBoxMinW = 8;   // Minimal width until to show refBox; below threshold switches to refCross
var rOffs = 13;     // Offset of refCross Image, adapt to Image size and refbox border

if (document.all) {
    var zBorder = 0;
} else {
    var zBorder = 4;
}

var refmapClick = false;
var mapcL, mapcT, mapcL, mapcR;
var mapElem; 

var isIE = (document.all) ? true : false;

var m_offsX = 0;
var m_offsY = 0;

var pmZB = new Object();



/**
 * DEFINE MOUSE ACTIONS, CALLED AS 'ONLOAD' SCRIPT
 ******************************************************/
/**
 * FOR MOUSE OVER MAP
 */
function startUp() {
    //pmZB = new Object();
    if (typeof(pmZB)!="undefined") {
        pmZB.theMapImg  = document.getElementById("mapimg");
        pmZB.theMapImgL = document.getElementById("mapimgLayer");
        pmZB.zb = document.getElementById("zoombox"); 
    }
    refmapClick = false;
    
    // ENABLES ACTIONS FOR KEYBOARD KEYS
    // comment out if not wanted
    if (document.all) document.onkeydown = kp;
    //document.onkeypress = kp;

    
    mapElem = document.getElementById('map');
    if (mapElem) {
        mapElem.onmousedown = doMouseDown; 
        mapElem.onmouseup   = doMouseUp;
        mapElem.onmousemove = doMouseMove; 
       
        // ENABLES ACTIONS FOR MOUSE WHEEL
        if (isIE) {
            mapElem.onmousewheel = omw;
        } else {
            mapElem.addEventListener('DOMMouseScroll', omw, false);
        }
        mapElem.oncontextmenu = disableContextMenu;
        
        setCursorMinMax('map');
    }
    
}



/**
 * FOR MOUSE OVER REFERENCE MAP
 */
function startUpRef() {
    clearTimeout(PMQuery.iquery_timer);  // necessary for iquery mode
    refmapClick = true;
    refElem = _$('refmap');
    if (refElem) {
        refElem.onmousedown = doMouseDown; 
        refElem.onmouseup   = doMouseUp;
        refElem.onmousemove = doMouseMove;   
    
        // ENABLES ACTIONS FOR MOUSE WHEEL ON MAP
        if (isIE) {
            refElem.onmousewheel = omw;
        } else {
            refElem.addEventListener('DOMMouseScroll', omw, false);
        }
        
        setCursorMinMax('refmap');
    }
}


/** 
 * MIN AND MAX VALUES FOR MOUSE
 */
function setCursorMinMax(elem) {
    // MAP
    if (elem == 'map') {
        var oMap = $('#map');
        mapcL = oMap.offset()['left'] + 1;
        mapcT = oMap.offset()['top'] + 1;
        mapcR = mapcL + mapW;
        mapcB = mapcT + mapH;
        var curelem = oMap;
    // REFERENCE MAP
    } else {
        var rMap = $('#refmap');
        mapcL = rMap.offset()['left'] ; 
        mapcT = rMap.offset()['top'];
        mapcR = mapcL + refW ;
        mapcB = mapcT + refH ;
        var curelem = rMap;
    }
    
    offsX = curelem.offset()['left'] + 1;
    offsY = curelem.offset()['top'] + 1;
    
}

/**
 * Check position of mouse
 */
function checkCursorPosition(cX, cY) {
    if (cX >= mapcL && cX <= mapcR && cY >= mapcT && cY <= mapcB) {
        return true;
    } else {
        return false;
    }
}



/*
 * FUNCTIONS TO GET MOUSE POSITIONS
 ******************************************************/
/**
 * For MouseDown
 */
function getDownXY(e) {
    if (document.all) {
		eX = event.clientX;
        eY = event.clientY;
	} else {
		eX = e.pageX;
		eY = e.pageY;
	}
	// subtract offsets    
    downX = eX - offsX;
    downY = eY - offsY;
    
    mapElem.onmouseup   = doMouseUp;
    mapElem.onmousemove = doMouseMove;
    mapElem.ondblclick  = doMouseDblClick;  // used for measure area, comment out if area measurement not wanted

    return false;	
}


/**
 * For MouseUp
 */
function getUpXY(e) {
    if (document.all) {
        eX = event.clientX;
        eY = event.clientY;
    } else {
        eX = e.pageX;
        eY = e.pageY;
    }
    
    if (!refmapClick) {
        upX = Math.min(eX - offsX, mapW);
        upY = Math.min(eY - offsY, mapH);
    } else {
        upX = eX - offsX;
        upY = eY - offsY;
    }

    return false;
}


/**
 * For MouseMove
 */
function getMoveXY(e) {
    if (document.all) {
        moveX = event.clientX;
        moveY = event.clientY;
    } else {
        moveX = e.pageX;
        moveY = e.pageY;
    }
    // subtract offsets from left and top
    moveX = moveX - offsX;
    moveY = moveY - offsY;             
}


/*
 * BASIC MOUSE FUNCTIONS: DOWN, UP, MOVE
 ******************************************************/
/**
 * Mouse DOWN
 */
function doMouseDown(e) {
    e = (e)?e:((event)?event:null);
    
    try {
        if (enableRightMousePan) {
            if (e.button == 2) {
                rightMouseButton = true;
                setCursor(true, false);
            } else {
                rightMouseButton = false;
            }
        }
    } catch(e) {
    
    }

    // ENABLES ACTIONS FOR KEYBOARD KEYS
    if (document.all) document.onkeydown = kp;
    document.onkeypress = kp;
    
    mouseDrag = true;
    getDownXY(e);
    
    if (refmapClick) {
        if (downX < 1 || downY < 1 || downX > refW || downY > refH) {        // Don't go ouside of map
            return false;
        } else {
            moveRefBox('shift');
        }
    }

    return false;
}

/**
 * Mouse UP
 */
function doMouseUp(e) {
    e = (e)?e:((event)?event:null);
    //alert (rightMouseButton);
    
    mouseDrag = false;
    getUpXY(e);

    var varform = _$("varform");

    // Click in main map
    if (!refmapClick) {

        maction = varform.maction.value;

        if (rightMouseButton) {
            maction = 'pan';
        }
        
        if (maction == 'measure') {
            //alert(upX + ' - ' + upY);
            measureDrawSymbols(e, upX, upY, 0);

        } else if (maction == 'pan'){
            var diffX = upX - downX;
            var diffY = upY - downY;
            // pan with click
            if (diffX == 0 && diffY == 0) {
                var newX = upX;
                var newY = upY;
            // pan with drag
            } else {
                var newX = (mapW / 2) - diffX ;
                var newY = (mapH / 2) - diffY;
            }
            
            zoombox_apply(newX, newY, newX, newY);
            
            //Reset after right-mouse pan
            maction = varform.maction.value;
            rightMouseButton = false;
            setCursor(false, false);
        
        } else if (maction == 'click'){
            zoombox_apply(downX, downY, downX, downY);
                
        } else if (maction == 'move'){
            // do nothing
            return false;

        } else {
            zoombox_apply(Math.min(downX,upX), Math.min(downY,upY), Math.max(downX,upX), Math.max(downY,upY));
        }

    // Click in reference map
    } else {
        
        if (upX < 1 || upY < 1 || upX > refW || upY > refH) {   // Don't go ouside of map
            alert(upX + ' ref out');
            return false;
        } else {
            //alert(upX +', '+ upY +', '+ upX +', '+ upY);
            zoombox_apply(upX, upY, upX, upY);
        }
    }
    
    return false;    
}

/**
 * Mouse MOVE
 */
function doMouseMove(e) {
    e = (e)?e:((event)?event:null);
    
    getMoveXY(e);
    /* * Draw a zoombox when mouse is pressed and zoom-in or select function are active
       * move map layer when pan function is active
       * do nothing for all others                                                      */

    // Actions in MAIN MAP
    if (!refmapClick) {
    	var varform = _$("varform");
        if (varform) {
            maction = varform.maction.value;
        }
        
        if (rightMouseButton) {
            maction = 'pan';
        }
        
        // Display coordinates of current cursor position
        displayCoordinates();        
        	
        switch (maction) {
            //# zoom-in, select
            case 'box':
                if (mouseDrag == true) { 
                    startZoomBox(e, moveX, moveY);
                } else if (varform.mode.value == 'nquery') {
                    try {
                        if (combinedSelectIquery) {
                            clearTimeout(PMQuery.iquery_timer);
                            PMQuery.iquery_timer = setTimeout("applyIquery(" + moveX + "," + moveY + ")", 300);
                        }
                    } catch(e) {
                        return false;
                    }
                }
                break;
    
            //# zoom-out, identify
            case 'click':
                hideObj(_$('zoombox'));
                break;
    
            //# pan with drag
            case 'pan':
                hideObj(_$('zoombox'));
                startPan(e, moveX, moveY);
                break;
    
            //# measure & digitize
            case 'measure':
            case 'digitize':
                showObj(_$('measureLayer'));
                showObj(_$('measureLayerTmp'));
                redrawAll(moveX , moveY);                
                break;
                
            //# move
            case 'move':
                if (varform.mode.value == 'iquery') {    //# iquery
                    if(PMQuery.follow){
                        PMQuery.timer_c = 0;
                        clearTimeout(PMQuery.timer_t); // 
                        clearTimeout(PMQuery.iquery_timer);
                        hideObj(_$('iqueryLayer'));
                        timedCount(moveX, moveY);
                    } else{
                        clearTimeout(PMQuery.iquery_timer);
                        PMQuery.iquery_timer = setTimeout("applyIquery(" + moveX + "," + moveY + ")", 300);
                    }
                }    
                break;
                
            default:
                try {
                    eval(maction + '_mmove(e, moveX, moveY)');
                } catch(e) {
                
                }
                break;
        }
        
    // Actions in REFERENCE MAP
    } else {
        hideObj(_$('zoombox'));
        if (mouseDrag) {
            moveRefBox('move');
        }
    }
    
    return false;    
}


/**
 * For DOUBLE CLICK 
 * currently only used for measure function: end measure, calculate polygon area
 */
function doMouseDblClick(e) {
    getUpXY(e);
    var varform = _$("varform");
    maction = varform.maction.value;
    if (maction == 'measure' || maction == 'digitize') {
        measureDrawSymbols(e, upX, upY, 1);
    } else {
        try {
            eval(maction + '_mdblclick()');
            return false;
        } catch(e) {
        
        } 
    }
}  



/*
 * FUNCTIONS FOR ZOOM BOX && PAN MOVING MAP
 ******************************************************/

/**
 * DRAG ZOOM BOX (ZOOM IN, SELECT)
 */
function startZoomBox(e, moveX, moveY) {
    if (mouseDrag == true) {
        if (checkCursorPosition(moveX + offsX, moveY + offsY)) {
            var boxL = Math.min(moveX, downX);
            var boxT = Math.min(moveY, downY);
            var boxW = Math.abs(moveX - downX);
            var boxH = Math.abs(moveY - downY);

            pmZB.zb.style.visibility = "visible";
            pmZB.zb.style.left   = boxL+"px";
            pmZB.zb.style.top    = boxT+"px";
            pmZB.zb.style.width  = boxW+"px";
            pmZB.zb.style.height = boxH+"px";
        }
    }
    return false;
}

/**
 * PAN
 */
function startPan(e, moveX, moveY) {
    if (mouseDrag == true) {  
        if (checkCursorPosition(moveX + offsX, moveY + offsY)) {
            var mapL = moveX - downX;
            var mapT = moveY - downY;
            
            var clipT = 0;
            var clipR = mapW;
            var clipB = mapH;
            var clipL = 0;
            
            pmZB.theMapImgL.style.top  = mapT+"px";
            pmZB.theMapImgL.style.left = mapL+"px";
        }
    }
    return false;
}



/**
 * FUNCTIONS FOR REFERENCE MAP RECTANGLE
 */
function setRefBox(boxL, boxT, boxW, boxH) {
    //showLayer('refbox');
    var rBox   = $("#refbox");
    var sBox   = $("#sliderbox");
    var rCross = $("#refcross");
    
    rBox.left(boxL + "px");
    rBox.top(boxT + "px");
    rBox.width(boxW + "px"); //Math.max(4, boxW);
    rBox.height(boxH + "px"); //Math.max(4, boxH);

    if (boxW < rBoxMinW) {
        rBox.hidev();
        rCross.showv();
        setRefCross(rCross, boxL, boxT, boxW, boxH);
    } else {
        rCross.hidev();
        rBox.showv();
    }

    sBox.hidev();

}

/**
 * MOVE RECTANGLE WITH MOUSE PAN
 */
function moveRefBox(moveAction) {
    var rBox   = $("#refbox");
    var rCross = $("#refcross");

    var boxL = rBox.ileft();
    var boxT = rBox.itop();
    var boxW = rBox.iwidth();
    var boxH = rBox.iheight();
    
    if (moveAction == 'shift') {
        var newX = downX; 
        var newY = downY;        
    } else {
        var newX = moveX; 
        var newY = moveY; 
    }
    
    boxLnew = newX - (boxW / 2) - 1; 
    boxTnew = newY - (boxH / 2) - 1;
    
    if (boxLnew < 0 || boxTnew < 0 || (boxLnew + boxW) > refW || (boxTnew + boxH) > refH) {
        return false;
    } else {
        rBox.left(boxLnew+"px");
        rBox.top(boxTnew+"px");
        window.status = (boxLnew + boxW + ' - ' + refW);
        
        if (boxW < rBoxMinW) {
            setRefCross(rCross, boxLnew, boxTnew, boxW, boxH);
        }
    }
}


/**
 * Change position of reference cross
 * => symbol used when refbox below threshold
 */
function setRefCross(rCross, boxL, boxT, boxW, boxH) {	
    boxcX = parseInt(boxL) + parseInt((boxW / 2));
    boxcY = parseInt(boxT) + parseInt((boxH / 2));
    rCross.left(Math.round((boxcX - rOffs))+"px");
    rCross.top(Math.round((boxcY - rOffs))+"px");    
}


/*******************************************************************
 * Resize map image while zooming with slider
 * called from sliderMove() in slider.js
 ********************************************/
/**
 * resize MAP
 */
function resizeMap(sizeFactor) {
    //alert(sizeFactor);
    var theMapImg = $('#mapImg');
    var theMapLay = $('#mapimgLayer');
    
    var oldW = mapW;
    var oldH = mapH;
    var newW = oldW * sizeFactor;
    var newH = oldH * sizeFactor;
    
    var newLeft = (oldW - newW) / 2;
    var newTop  = (oldH - newH) / 2;

    theMapImg.width(newW+"px");
    theMapImg.height(newH+"px");
    theMapLay.left(newLeft+"px"); 
    theMapLay.top(newTop+"px");
    
    if (sizeFactor > 1) {
        var diffW = parseInt((newW - oldW) / 2);
        var diffH = parseInt((newH - oldH) / 2);
        clipT = diffH;
        clipR = diffW + oldW;
        clipB = diffH + oldH;
        clipL = diffW;

        var clipRect = 'rect(' + clipT + 'px ' 
                               + clipR + 'px '
                               + clipB + 'px ' 
                               + clipL + 'px)'; 
        //window.status = clipRect;
        theMapLay.css('clip', clipRect);
        
        theMapLay.width(newW+"px");
        theMapLay.height(newH+"px");
    } 
}

/**
 * resize REFBOX
 */
function resizeRefBox(sizeFactor) { 
    var refZoomBox = $('#refbox');
    var refSliderBox = $('#refsliderbox');
    
    refSliderBox.showv();
    
    if (refZoomBox.ileft() > 0) {
        var refBoxBorderW = 1; //refZoomBox.css('border-width');  // adapt to border width in CSS

        var oldRefW    = refZoomBox.iwidth();
        var oldRefH    = refZoomBox.iheight();
        var oldRefLeft = refZoomBox.ileft();
        var oldRefTop  = refZoomBox.itop();
        
        var newRefW = Math.round(oldRefW / sizeFactor);
        var newRefH = Math.round(oldRefH / sizeFactor);
        
        var newRefLeft = parseInt(oldRefLeft + ((oldRefW - newRefW) / 2) + refBoxBorderW);
        var newRefTop  = parseInt(oldRefTop + ((oldRefH - newRefH) / 2) + refBoxBorderW);
        
        refSliderBox.left(newRefLeft+"px");
        refSliderBox.top(newRefTop+"px");
        refSliderBox.width(newRefW+"px");
        refSliderBox.height(newRefH+"px");
    }
}



/**
 * KEYBOARD FUNCTIONS
 * original script taken from http://ka-map.maptools.org/
 */
function kp(e) {
    try {
        e = (e)? e : ((event) ? event : null);
    } catch(e) {};
    if(e) {
        var charCode=(e.charCode)?e.charCode:e.keyCode;
        //alert(charCode);
        var b=true;
        var nStep = 16;
        switch(charCode){        
          case 63232://safari up arrow
          case 38://up arrow
            arrowpan('n');
            break;
          case 63233://safari down arrow
          case 40://down arrow
            arrowpan('s');
            break;
          case 63234://safari left arrow
          case 37:// left arrow
            arrowpan('w');
            break;
          case 63235://safari right arrow
          case 39://right arrow
            arrowpan('e');
            break;
          case 63276://safari pageup
          case 33://pageup
            gofwd();
            break;
          case 63277://safari pagedown
          case 34://pagedown
            goback();
            break;
          case 63273://safari home (left)
          case 36://home
            zoomfullext();
            break;
          case 63275://safari end (right)
          case 35://end
            break;
          case 43: // +
            //if (!navigator.userAgent.match(/Opera|Konqueror/i))  
            zoompoint(2, '');
            break;
          case 45: // -
            zoompoint(-2, '');
            break;
          case 46:// DEL: delete last point in editing mode
            delLastPoint();  
            break;
          case 27:// ESC: clear measure/digitize
            if (_$("varform").maction.value == 'measure') {
                resetMeasure();
            }  
            break;
          default:
            b=false;
        }
    }
}



/**
 * MOUSEWHEEL FUNCTIONS (zoom in/out)
 * only works with IE
 */
function omw(e) {
    e = (e)?e:((event)?event:null);
    if(e) {
        try { 
            var imgxy = (refmapClick ? '' : (wheelZoomPointerPosition ? moveX + "+" + moveY : ''));
            var wInv = wheelZoomGoogleStyle ? -1 : 1;
        } catch(e) {
            var imgxy = '';
            var wInv = 1;
        }
        var wD = (e.wheelDelta ? e.wheelDelta : e.detail*-1) * wInv;
        
        clearTimeout(PMap.resize_timer);
        if (wD < 0) {
            PMap.resize_timer = setTimeout("zoompoint(2,'" + imgxy + "')",300);  
            return false;
        } else if (wD > 0) {
            PMap.resize_timer = setTimeout("zoompoint(-2,'" + imgxy + "')",300);  
            return false;
        }
    }
}


/**
 * Disable right mouse context menu
 */
function disableContextMenu(e) {
    e = (e)?e:((event)?event:null);
    return false;
}




/* 
 * FUNCTIONS FOR COODINATE DIPLAY FUNCTIONS
 ***********************************************/
/**
 * GET MAP COORDINATES FOR MOUSE MOVE
 */
function getGeoCoords(mouseX, mouseY, convert2latlon) {
    var x_geo = minx_geo + ((mouseX/mapW) * xdelta_geo);
    var y_geo = maxy_geo - ((mouseY/mapH) * ydelta_geo);
    
    if (convert2latlon) {
        // Just for ETRS-LAEA projection: convert from LAEA to latlon coordinates
        // create your own custom function for other CRS
        var mpoint = laea2latlon(x_geo, y_geo);
    
    } else {
        // Display mouse position in MAP coordinates 
        var mpoint = new Object();
        mpoint.x = x_geo;
        mpoint.y = y_geo;
    }
    
    return  mpoint;
}




/**
 * DISPLAY MAP COORDINATES FOR MOUSE MOVE
 */
function displayCoordinates() {
    var mpoint = getGeoCoords(moveX, moveY, false);
    //var mpoint = getGeoCoords(moveX, moveY, true);
    
    // Round values (function 'roundN()' in 'measure.js')
    var rfactor = 0;
    var px = isNaN(mpoint.x) ? '' : roundN(mpoint.x, rfactor);
    var py = isNaN(mpoint.y) ? '' : roundN(mpoint.y, rfactor);
    
    // Display in status bar
    /*
    var mapCoords = 'X: ' + px + '  Y: ' + py;
    window.status = mapCoords;
    */
    
    // Display in DIV over MAP 
    $('#xcoord').html('X: ' + px); // + ' &deg;';
    $('#ycoord').html('Y: ' + py); // + ' &deg;';
}



/**
 * Convert XY coordinates from ETRS-LAEA (3035) to lat/lon (4326)
 */
function laea2latlon(X, Y) {
    var a   = 6378137;
    var f   = 1 / 298.257222101;
    var e2  = (2*f) - (f*f);
    var e   = Math.sqrt(e2);
    var ph0 = 52 / 180 * Math.PI ;
    var la0 = 10 / 180 * Math.PI;
    var X0  = 4321000.0;
    var Y0  = 3210000.0;
     
    var q0 = (1-e2) *  ((Math.sin(ph0) / (1 - (e2 * Math.pow(Math.sin(ph0), 2)))) - ((1/(2*e)) * Math.log((1 - (e * Math.sin(ph0))) / (1 + (e * Math.sin(ph0))))));    
    var qp = (1-e2) *  ( (1 / (1-e2)) - ((1/(2*e)) * Math.log((1-e)/(1+e)) ) ) ;   
    var beta0 = Math.asin(q0/qp);  
    var Rq = a * Math.sqrt(qp/2);    
    var D  =  (a * Math.cos(ph0)) / (Math.sqrt(1 - (e2 * Math.pow(Math.sin(ph0), 2))) * (Rq * Math.cos(beta0))  );    
    var p = Math.sqrt(Math.pow((X-X0)/D, 2) + Math.pow(D * (Y-Y0), 2));    
    var C = 2 * Math.asin(p / (2*Rq));    
    var beta_ = Math.asin((Math.cos(C) * Math.sin(beta0))  +  (((D * (Y-Y0)) * Math.sin(C) * Math.cos(beta0)) / p));
     
    // Latitude
    var lat1 = ((e2/3) + ((31*Math.pow(e2, 2)) / 180) + ((517*Math.pow(e2, 3)) / 5040)) * Math.sin(2*beta_);
    var lat2 = (((23*Math.pow(e2, 2)) / 360) + ((251*Math.pow(e2, 3)) / 3780)) * Math.sin(4*beta_);
    var lat3 = ((761*Math.pow(e2, 3)) / 45360) * Math.sin(6*beta_);
    var lat = (beta_ + lat1 + lat2 + lat3) * (180/Math.PI);
    
    //Longitude
    var lon = (la0 + Math.atan( ((X-X0) * Math.sin(C)) / ((D * p * Math.cos(beta0) * Math.cos(C)) - (D*D * (Y-Y0) * Math.sin(beta0) * Math.sin(C))))) * (180/Math.PI);  
    
    // Return Point
    var mpoint = new Object();
    mpoint.x = lon;
    mpoint.y = lat;
    
    return mpoint;
}


