
/******************************************************************************
 *
 * Purpose: drawing functions (measurements, digitizing)
 *          uses the geometry.js library
 * Authors: Armin Burger, Federico Nieri
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
 
 
/**********************************************************************************
  USES THE JAVASCRIPT LIBRARIES JSGRAPHICS FROM WALTER ZORN
  SEE FILE /JAVASCRIPT/WZ_JSGRAPHICS.JS FOR DETAILS OF COPYRIGHT
 **********************************************************************************/  


/** set default values if missing in js_config.php */
if (typeof(pmMeasureUnits)=="undefined") pmMeasureUnits = {distance:" [km]", area:" [km&sup2;]", factor:1000};
if (typeof(pmMeasureObjects)=="undefined") pmMeasureObjects = {line: {color:'#FF0000', width:2}};

var numSize;

var polyline = new Polygon();
var geoPolyline = new Polygon();

polyline.lineWidth = pmMeasureObjects.line.width;
polyline.lineColor = pmMeasureObjects.line.color;


    
/** 
 * Return a Point object with geo coordinate instead of px coordinate
 * @param pxPoint: Point object with px coordinate
 */
function toGeoPoint(pxPoint){
    var x_geo = minx_geo + ((pxPoint.x/mapW)  * xdelta_geo);
	var y_geo = maxy_geo - ((pxPoint.y/mapH) * ydelta_geo);
	return new Point(x_geo,y_geo);
}

/** 
 * Return a Polygon object with geo coordinate instead of px coordinate
 * @param pxPolygon: Polygon object with px coordinate
 */
function toGeoPolygon(pxPolygon){
    var pxPoints = pxPolygon.getPoints();
	var geoPolygon = new Polygon();
	for(var i = 0; i < pxPoints.length; i++){
		geoPolygon.addPoint(toGeoPoint(pxPoints[i]));
	}
	return geoPolygon;
}

function toPxPolygon(geoPolygon){
	var geoPoints = geoPolygon.getPoints();
	var pxPolygon = new Polygon();
	for(var i = 0; i < geoPoints.length; i++){
		pxPolygon.addPoint(toPxPoint(geoPoints[i]));
	}
	return pxPolygon;
}

function toPxPoint(geoPoint){
  var x_px = ((geoPoint.x - minx_geo) / xdelta_geo) * mapW;
  var y_px = ((maxy_geo - geoPoint.y) / ydelta_geo) * mapH;	
	return new Point(x_px,y_px);
}

/**
 * Return a geography measure unit instead of px
 * @param pxLength: length in px
 */
function toGeoLength(pxLength){
	return (pxLength/mapW) * xdelta_geo;
}


/**
 * Main function, draws symbol points between mouseclicks
 * @return void
 */
function measureDrawSymbols(e, clickX, clickY, dblClick) {
    // Polyline points number before to add the current click point
    if(polyline.isClosed()){
      polyline.reset();
    }
    
    var nPoints = polyline.getPointsNumber();   
    var clickPoint = new Point(clickX, clickY); 
    // Reset everything when last measure ended with double click
    if (nPoints == 0) resetMeasure();        
    // Don't go outside map
    if ((clickX < mapW) && (clickY < mapH)) { 
        
        // SINGLE CLICK
        if (dblClick != 1) { 
	        
	        polyline.addPoint(new Point(clickX,clickY));
	        	        
        	// First point for start click
        	if (nPoints < 1) {

        		drawLineSegment(jg,new Line(clickPoint, clickPoint));         			

      		// Fill distance between clicks with symbol points
      		}else{
      		
      		  // USE wz_jsgraphics.js TO DRAW LINE. lastSegment is of Line type                 
            var lastSegment = polyline.getLastSide();
            var sidesNumber = polyline.getSidesNumber();                              		
      		
      		  // check for the overlapping of the new side.
            // it will never overlap with the previous side  	    	  
            if (sidesNumber > 2){      		    
                for (var s = 1 ; s < (sidesNumber-1); s++){                 
                    var intersectionPoint = polyline.getSide(s).intersection(lastSegment);
                    if (intersectionPoint != null){                  
                        alert(localeList['digitize_over']);
                        polyline.delPoint(polyline.getPointsNumber()-1);
                        return;                  
                    }                
                }
            }
                                                                                                
            drawLineSegment(jg,lastSegment);
            // calls the handler of the side (segment) digitation and pass it the polyline in px coords
            onDigitizedSide(polyline);
                                                
        	}      	        	        	        	                                  
                        	
        // DOUBLE CLICK => CALCULATE AREA
        } else if (dblClick) {
                        	            	    		    	
  	    	// Removes the last duplicated point because of the last 2 single click	    	
  	    	polyline.delPoint(polyline.getPointsNumber()-1);
  	    	  	    	  	    	
  	    	// Closing the polyline to have a polygon  	    	
  	    	polyline.close();
  	    	
            // fix the last side
            var lastSegment = polyline.getLastSide();	   
  	    	var sidesNumber = polyline.getSidesNumber();
  	    	
  	    	// check for the overlapping of the closing side
  	    	// it will never overlap with the first and the last side
            for (var s = 2 ; s < (sidesNumber-1); s++){                 
                var intersectionPoint = polyline.getSide(s).intersection(lastSegment);
                if (intersectionPoint != null){                  
                    alert(localeList['digitize_over']);
                    polyline.delPoint(polyline.getPointsNumber()-1);
                    return false;                  
                }                
            }	    	
  	    		    	  	    	            		
  	    	if(lastSegment != null){    	
  	    		drawLineSegment(jg,lastSegment);
  	    	}
          
            // calls the handler of the polygon digitation before reset the polygon
            onDigitizedPolygon(polyline);
            
            // remove all points from the polygon          
      		//polyline.reset();
                        
        }                   
    }        
    geoPolyline = toGeoPolygon(polyline);
}


/** 
 * Handler of the digitized polygon action. It is called when a double click
 * close tha drawing polygon
 * @param poly: Polygon object passed to the handler
 */
function onDigitizedPolygon(poly){
	
	var polyGEO = toGeoPolygon(poly);
    var perimGEO = polyGEO.getPerimeter()/pmMeasureUnits.factor;	
	
    var cntPerLen = Math.round(perimGEO).toString().length;
    numSize = Math.max(0, (4 - cntPerLen));
    
    perimGEO = roundN(perimGEO, numSize); 
    
    var areaGEO = Math.abs(roundN (polyGEO.getArea() / (pmMeasureUnits.factor * pmMeasureUnits.factor), numSize-1)) ;
                
    // Change input text box to 'Area'
	document.measureForm.sumLen.value = perimGEO;
    $("#mSegTxt").html(localeList['Area'] + pmMeasureUnits.area); 
    document.measureForm.segLen.value = areaGEO;
    
}

/** 
 * Handler of the digitized line action. It is called when a new click cause draw a new line
 * @param poly: Polygon object passed to the handler
 */
function onDigitizedSide(poly){
    // Polygon in map coordinates
	 var polyGEO = toGeoPolygon(poly);
        
    // Segment length in  map coordinates,  write values to input boxes
    var segLenGEO_0 = polyGEO.getSideLength(polyGEO.getSidesNumber()) / pmMeasureUnits.factor ;
    var perimGEO_0  = polyGEO.getPerimeter() / pmMeasureUnits.factor ;
    
    var cntSegLen = Math.round(segLenGEO_0).toString().length;
    numSize = Math.max(0, (4 - cntSegLen));
    var segLenGEO = roundN(segLenGEO_0, numSize); 
    var perimGEO  = roundN(perimGEO_0, numSize);     

    var measureSegment = false;
    if (measureSegment){
        document.measureForm.segLen.value = segLenGEO;
        if (polyGEO.getPointsNumber() >= 2){
            poly.reset();
        }
    } else {
        document.measureForm.sumLen.value = perimGEO;
        document.measureForm.segLen.value = segLenGEO;
    }        
}

/**
 * REDRAW THE LAST AND THE CLOSING SIDE OF THE POLYGON
 */
function redrawAll(currX, currY) {

    if(polyline.isClosed())
      return;

    if (polyline.getPointsNumber()>0) {    	

        var mousePoint = new Point(currX,currY);
        jg_tmp.clear();
        jg_tmp.setColor(pmMeasureObjects.line.color); 
    	jg_tmp.setStroke(pmMeasureObjects.line.width);
	    // Drawing last side	    
	    var lastPoint = polyline.getPoint(polyline.getPointsNumber()-1);
	    	    
        drawLineSegment(jg_tmp,new Line(lastPoint,mousePoint));
           	      	    
	    jg_tmp.setStroke(Stroke.DOTTED); 
	    var firstPoint = polyline.getPoint(0);
	          
        drawLineSegment(jg_tmp,new Line(firstPoint,mousePoint));
     
    }		    
	  
}


function drawPolyline(jg,poly) {  
    var n = poly.getSidesNumber();
    for (var i=1;i<=n;i++) {    
        drawLineSegment(jg,poly.getSide(i));
    }
}


/**
 * DRAW LINE USING JSGRAPHICS
 */
function drawLineSegment(jg,line) {

    var xfrom = line.getFirstPoint().x;
    var yfrom = line.getFirstPoint().y;
    var xto = line.getSecondPoint().x;
    var yto = line.getSecondPoint().y;
    
    var limitSides = getLimitSides();
    var xList = limitSides.getXList();
    var yList = limitSides.getYList();
    
    var xMin = Math.min.apply({},xList);
    var yMin = Math.min.apply({},yList);        
    var xMax = Math.max.apply({},xList);
    var yMax = Math.max.apply({},yList);    
    
    var points = new Array();
    
    if  (xfrom >= xMin && xfrom <= xMax && yfrom >= yMin && yfrom <= yMax) {
        points.push(line.getFirstPoint());       
    }
  
    if  (xto >= xMin && xto <= xMax && yto >= yMin && yto <= yMax) {
        points.push(line.getSecondPoint());      
    }
    
    var s = 1;
    
    while(points.length < 2 && s <= limitSides.getSidesNumber()){    
        var intersectionPoint = limitSides.getSide(s).intersection(line);
        if (intersectionPoint != null) {
            points.push(intersectionPoint);
        }
        s++;
    }
                          
    if(points.length == 2){    
        jg.drawLine(points[0].x, points[0].y, points[1].x,points[1].y);                 
        jg.paint();      
    }
                        
}

/**
 * GET THE RECTANGLE OF THE DRAWING AREA
 */
function getLimitSides(){

    var mapimgLayer     = _$('mapimgLayer');
    var mapimgLayerL    = objL(mapimgLayer);
    var mapimgLayerH    = objT(mapimgLayer);
    var mapW = mapimgLayer.style.width;
    var mapH = mapimgLayer.style.height;
    
    var xMin = mapimgLayerL;
    var xMax = mapimgLayerL + parseInt(mapW);
    var yMin = mapimgLayerH;
    var yMax = mapimgLayerH + parseInt(mapH);        
    
    var limitSides = new Polygon();
    
    limitSides.addPoint( new Point(xMin,yMin) );
    limitSides.addPoint( new Point(xMax,yMin) );
    limitSides.addPoint( new Point(xMax,yMax) );
    limitSides.addPoint( new Point(xMin,yMax) );
    limitSides.close();
    
    return limitSides;
}

/**
 * Remove all measure settings
 */
function resetMeasure() {
    // remove lines
    polyline.reset();
    jg.clear();    
    jg_tmp.clear();
    
    reloadData();
}

function clearMeasure(){
  resetMeasure();
  geoPolyline.reset();
}

function reloadData(){
    
    if (polyline.getSidesNumber() == 0) {
        // Reset form fields 
        if (document.measureForm) {
            document.measureForm.sumLen.value = '';
            document.measureForm.segLen.value = '';
            document.getElementById("mSegTxt").innerHTML = localeList['Segment'] + pmMeasureUnits.distance; 
        }  
    } else if(polyline.isClosed()) {
        onDigitizedPolygon(polyline);
    } else {
        onDigitizedSide(polyline);
    }
}

function reloadDrawing(){
    var varformMode = _$("varform").mode.value;
    if (varformMode == 'measure') {
        resetMeasure();
        polyline = toPxPolygon(geoPolyline);
        if (polyline.getPointsNumber()>0) {
            drawPolyline(jg,polyline);
        }
        reloadData();
    }
}

function delLastPoint(){
    var nPoints = polyline.getPointsNumber();
    if (nPoints > 0) {
        polyline.delPoint(nPoints - 1);
        geoPolyline.delPoint(nPoints - 1);
        reloadDrawing();
    }
}


/**
 * Round to a specified decimal
 */
function roundN(numin, rf) {
    return ( Math.round(numin * Math.pow(10, rf)) / Math.pow(10, rf) );
} 
