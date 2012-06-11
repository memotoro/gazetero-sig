
/******************************************************************************
 *
 * Purpose: AJAX (XMLHTTP) requests
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
 * For loading/updating the MAP
 */
function updateMap(murl) {
    var loadObj = document.getElementById("loading");

    $.ajax({
        url: murl,
        dataType: "json",
        success: function(response){
            
            // Reload application when PHP session expired
            var sessionerror = response.sessionerror;
            if (sessionerror == 'true') {
               errormsg = localeList['sessionExpired']; 
               //alert(errormsg);
               window.location.reload();
               return false;
            }
            
            var rBxL = response.refBoxStr.split(',');
            //var refW = response.refW;
            //var refH = response.refH;
            minx_geo = parseFloat(response.minx_geo);
            maxy_geo = parseFloat(response.maxy_geo);
            xdelta_geo = parseFloat(response.xdelta_geo);
            ydelta_geo = parseFloat(response.ydelta_geo);
            var geo_scale = response.geo_scale;
            var urlPntStr = response.urlPntStr;
            
            // Load new map image
            loadMapImg(response.mapURL);
            
            
            // Check if TOC has to be updated
            var refreshToc = eval(response.refreshToc);
            //refreshToc = true;
            if (refreshToc) {
                //alert("refresh");
                var tocurl = PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
                updateTocScale(tocurl);
            }
            
            // Scale-related activities
            writescale(geo_scale);
            setSlider(geo_scale);
            PMap.scale = geo_scale;
            $("#pmMapRefreshImg").src("images/pixel.gif");
            
            // Reference image: set DHTML objects
            setRefBox(rBxL[0], rBxL[1], rBxL[2], rBxL[3]);
            
            // reset cursor
            //setCursor(false);
            
            // Update SELECT tool OPTIONs in case of 'select' mode
            var vMode = _$("varform").mode.value;
            var autoidentify = '';
            if (vMode == 'nquery' || vMode == 'iquery') {
                if (vMode == 'iquery'){
                    autoidentify = '&autoidentify=1';
                }
                var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+ SID + '&activegroup=' + getSelectLayer() + autoidentify;
                updateSelectTool(selurl);
            }
            
            // If measure was active, delete all masure elements
            if (vMode == 'measure') {
                resetMeasure();
            }

            
            //Update map link
            var dg = getLayers();
            var maxx_geo = xdelta_geo + minx_geo;
            var miny_geo = maxy_geo - ydelta_geo;
            var me = minx_geo + ',' + miny_geo + ',' + maxx_geo + ',' + maxy_geo;
            var confpar = PMap.config.length > 0 ? '&config=' + PMap.config : '';
            var urlPntStrPar = urlPntStr.length > 1 ? '&up=' + urlPntStr.replace(/\%5C\%27/g, '%27') : '';
            var loc = window.location;
            var port = loc.port > 0 ? ':' + loc.port : '';
            var linkhref = loc.protocol + '/' + '/' + loc.hostname + port + loc.pathname + '?dg=' + dg + '&me=' + me + '&language=' + PMap.gLanguage + confpar + urlPntStrPar; 

            if (_$('current_maplink')) _$('current_maplink').href = linkhref;
         
        }
    });   
}


/**
 * Update the TOC 
 */
function updateToc(tocurl) {
    $.ajax({
        url: tocurl,
        dataType: "json",
        success: function(response){   
            var tocHTML = response.tocHTML;
            setInnerHTML('toc',tocHTML);
            
            var tocButtons = response.tocButtons;  
            if (tocButtons.length > 0) {
                $('#autoRefreshButton').html(tocButtons);
            }                    
                      
            var tocurl = PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
            updateTocScale(tocurl);
        }
    });
}


/**
 * Update toc applying different styles to visible/not-visible layers
 */
function updateTocScale(tocurl) {
    $.ajax({
        url: tocurl,
        dataType: "json",
        success: function(response){
            var legStyle = response.legStyle;
            var layers = response.layers;
            
            if (legStyle == "swap" && $('#toclegend').css('display') == 'block') {
                swapToLegendView();
            } 
            
            for (var l in layers) {
                $('#spxg_' + l).removeClass('unvis').removeClass('vis').addClass(layers[l]);
            }
        }
    });  
}


/**
 * Show legend over MAP
 */
function showMapLegend(tocurl) {
    $.ajax({
        url: tocurl,
        dataType: "json",
        success: function(response){
            var tocHTML = response.tocHTML;
            // alert(tocHTML);       
            var legDiv = _$('maplegend');                
            setInnerHTML('maplegend',tocHTML);
            legDiv.style.visibility = 'visible';
        } 
    });   
}


/**
 * Swap from TOC to LEGEND view
 */
function swapLegend(tocurl) {
    $.ajax({
        url: tocurl,
        dataType: "json",
        success: function(response){
            var tocHTML = response.tocHTML;
            $('#toclegend').html(tocHTML);
            $('#toc').hide();
            $('#toclegend').show(); //fadeIn('normal');
        } 
    });   
}


/** 
 * For SELECT tool 
 */
function updateSelectTool(selurl) {
    $.ajax({
        url: selurl,
        dataType: "json",
        success: function(response){     
            var selStr = response.selStr;
            $('#mapToolArea').html(selStr);
        }
    });   
}


/**
 * Update layer options list for selection/iquery
 */
function updateSelLayers(selurl) {
    $.ajax({
        url: selurl,
        dataType: "json",
        success: function(response){
            var sellayers = response.sellayers;
        
            // Update SELECT tool OPTIONs in case of 'select' mode
            var vMode = _$("varform").mode.value;
            if (vMode == 'nquery' || vMode == 'iquery') {
                var selurl = PM_XAJAX_LOCATION + 'x_select.php?'+ SID + '&activegroup=' + getSelectLayer() ;
                updateSelectTool(selurl);
            }
        }
    });
}


/**
 * Add point of interest to map
 */
function addPOI(digitizeurl) {
    $.ajax({
        type: "POST",
        url: digitizeurl,
        success: function(response){
            changeLayersDraw();
        }
    });
}


/**
 * Get query results and display them by parsing the JSON result string 
 */
function getQueryResult(qurl, params) {
    $.ajax({
        type: "POST",
        url: qurl,
        data: params,
        dataType: "json",
        success: function(response){
            var mode = response.mode;
            var queryResult = response.queryResult;
        
            if (mode != 'iquery') {
                if (PMap.infoWin == 'window') {
                    openResultwin('info.phtml?'+SID);
                } else {
                    //if (PMap.infoWin == 'frame') 
                    $('#infoFrame').showv();
                    writeQResult(queryResult, PMap.infoWin);
                }
                pmIndicator_hide();
            } else {
                // Display result in DIV and postion it correctly
                showIQueryResults(queryResult);
            }
        }
    });
}


/**
 * Export query result 
 */
function exportQueryResults(expurl) {
    $.ajax({
        url: expurl,
        success: function(response){
            //var mode = response.mode;
        }
    });   
}


/**
 * Export query result 
 */
function addWMS(url) {
    $.ajax({
        url: url,
        success: function(response){
            pmToc_init();
        }
    });  
}


/**
 * Attribute search: create items for search definitions 
 */
function createSearchItems(url) {
    $.ajax({
        url: url,
        dataType: "json",
        success: function(response){
            var searchJson = response.searchJson;
            var action = response.action;
            var divelem = response.divelem; 
            
            if (action == 'searchitem') {
                var searchHtml = createSearchInput(searchJson);
                //alert(searchHtml);
                //$('#searchitems').html(searchHtml);
            } else {
                var searchHtml = json2Select(searchJson, "0");
                $('#searchoptions').html(searchHtml);
            }
        }
    });
}


function getSuggest(suggesturl) {
    $.ajax({
        type: "POST",
        url: suggesturl,
        dataType: "json",
        success: function(response){
            var searchGet   = response.searchGet;
            var suggestList = response.retvalue;
            var fldname     = response.fldname;
            
            // add result to suggest cache
            pmSuggest.suggestCache[searchGet] = suggestList;
            
            // write out suggest options
            writeSuggestList(suggestList, fldname);
        }
    });
}

 
