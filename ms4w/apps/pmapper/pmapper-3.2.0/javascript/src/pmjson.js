
/*****************************************************************************
 *
 * Purpose: parse JSON strings to HTML (query result outputs)
 * Author:  Armin Burger
 *
 *****************************************************************************
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

if (typeof(queryResultLayout)=="undefined") {
    if (typeof(opener.queryResultLayout)=="undefined") {
        var queryResultLayout = 'table';
    } else {
        queryResultLayout = opener.queryResultLayout;
    }
} 


/**
 * Parse JSON query result string
 * create table view
 */
function parseJSON(jsonstr, infoWin) {
    var rhtml = '';

    // Nothing found with query, return only header 
    if (jsonstr == 0) return returnNoResultHtml(infoWin);

    // Features found, parse JSON string
    var groups = jsonstr[0];    
    
    var jlen = groups.length;
    
    
    for (var i=0; i<jlen; i++) {
        var layObj = groups[i];
        
        rhtml += '<div class="LAYERHEADER">' + layObj.description + '</div>';
        rhtml += '<table class="sortable" border="0" cellspacing="0" cellpadding="0">';
        
        //*** Header line ***/
        var hL = layObj.header;
        if (hL[0] == '@') {
            var withShpLink = true;
        } else {
            var withShpLink = false;
            //var withShpLink = true;
        }
        
        var hLlen = hL.length;
        rhtml += '<tr>';
        var startcol = withShpLink ? 0 : 1;
        for (var hi=startcol; hi<hLlen; hi++) {
            rhtml += '<th>' + hL[hi] + '</th>';
        }
        rhtml += '</tr>';
        
        
        //*** Values of the layer ***/
        var vL = layObj.values;
        var vLlen = vL.length;
        
        for (var vi=0; vi<vLlen; vi++) {
            
            //--- Rows ---//
            var rowL = vL[vi];
            var rowLlen = rowL.length;
            
            rhtml += '<tr>';
            for (var ri=startcol; ri<rowLlen; ri++) {
                // Write out shape link for zoom
                if (withShpLink && ri < 1) {
                    var shplinkL = rowL[ri].shplink;
                    rhtml += '<td class=\"zoomlink\"><a href="javascript:' + (infoWin != 'window' ? '' : 'opener.') + 'zoom2extent(\'' + shplinkL[0] + '\', \'' + shplinkL[1] + '\', \'' + shplinkL[2] + '\')">';
                    rhtml += '<img src="images/zoomto.gif" alt="zoomto"></a></td>';
                } else {
                    // Check for Hyperlinks
                    if (isObject(rowL[ri])) {
                        var hypLinkL = rowL[ri].hyperlink;
                        rhtml += '<td><a href="javascript:openHyperlink(\'' + hypLinkL[0] + '\', \'' + hypLinkL[1] + '\', \'' + hypLinkL[2].replace(/"|'/, '\\\'') + '\')">' + hypLinkL[3] + '</a></td>';
                    } else {
                        rhtml += '<td>' + rowL[ri] + '</td>';
                    }
                }
            }
            rhtml += '</tr>';
        }
        rhtml += '</table>';
    }
    
    /*** Zoom parameters ***/
    var zp = jsonstr[1];
    rhtml += returnZoomParamsHtml(zp, infoWin);
    
    try {
        rhtml += returnCustomQueryHtml(zp, infoWin);
    } catch(e) {}
    
    return rhtml;
}



/**
 * Parse JSON query result string
 * create TREE view
 */
function parseJSON_treeview(jsonstr, infoWin) {
    var rhtml = '';

    // Nothing found with query, return only header 
    if (jsonstr == 0) return returnNoResultHtml(infoWin);


    // Features found, parse JSON string   
    
    // Style for tree
    try {
        if (infoWin == 'window') {
            var treeCss = 'id="' + opener.queryTreeStyle['css'] + '"';
        } else {
            var treeCss = 'id="' + queryTreeStyle['css'] + '"';
        }
        
    } catch(e) {
        var treeCss = "";
    }
    
    var t = localeList['Layer'] + '<ul ' + treeCss + '>';
            
    var groups = jsonstr[0];      
    var jlen = groups.length;
    
    for (var i=0; i<jlen; i++) {
        var layObj = groups[i];
        t += '<li>' + layObj.description;
        t += '<ul>';
        
        //*** Header line ***/
        var hL = layObj.header;
        if (hL[0] == '@') {
            var withShpLink = true;
        } else {
            var withShpLink = false;
        }
        
        var hLlen = hL.length;
        var startcol = withShpLink ? 0 : 1;
        var n4node = withShpLink ? 1 : 0;

        //*** Values of the layer ***/
        var vL = layObj.values;
        var vLlen = vL.length;
        
        for (var vi=0; vi<vLlen; vi++) {
            
            //--- Rows ---//
            var rowL = vL[vi];
            var rowLlen = rowL.length;
            
            if (isObject(rowL[n4node])) {
                var nodeAnnot = rowL[n4node].hyperlink[3];
            } else {
                var nodeAnnot = rowL[n4node];
            }
            
            t += '<li>' + nodeAnnot + '<ul>';

            for (var ri=startcol; ri<rowLlen; ri++) {
                // Write out shape link for zoom
                if (withShpLink && ri < 1) {
                    var shplinkL = rowL[ri].shplink;
                    var zoomlink = '<a href="javascript:' + (infoWin != 'window' ? '' : 'opener.') + 'zoom2extent(\'' + shplinkL[0] + '\', \'' + shplinkL[1] + '\', \'' + shplinkL[2] + '\')">';
                    t += '<li>' + zoomlink + '<img src="images/zoomtiny.gif" alt="" /> Zoom</a></li>';

                } else {
                    // Check for Hyperlinks
                    t += '<li><span class="qcname">' + hL[ri] + '</span>: &nbsp;';
                    if (isObject(rowL[ri])) {
                        var hypLinkL = rowL[ri].hyperlink;
                        var hlink = '<a href="javascript:openHyperlink(\'' + hypLinkL[0] + '\', \'' + hypLinkL[1] + '\', \'' + hypLinkL[2] + '\')">' ;
                        //var resrow =  '<span class="qcname">' + hL[ri] + '</span>: &nbsp;' +  hypLinkL[3];
                        t += hlink + hypLinkL[3] +'</a>';
                    } else {
                        var hlink = '';
                        //var resrow =  '<span class="qcname">' + hL[ri] + '</span>: &nbsp;' + rowL[ri];
                        t += rowL[ri];
                    }
                    t += '</li>';
                }
            }
            t += '</ul>';
            t += '</li>';
        }
        t += '</ul>';
        t += '</li>';
    }
    t += '</ul>';
    
    rhtml += t;
    
    /*** Zoom parameters ***/
    var zp = jsonstr[1];
    rhtml += returnZoomParamsHtml(zp, infoWin);
    
    try {
        rhtml += returnCustomQueryHtml(zp, infoWin);
    } catch(e) {}
    
    return rhtml;
}


/**
 * Return HTML for no results found in query
 */
function returnNoResultHtml(infoWin) {
    var h = '<table class="restable" cellspacing="0" cellpadding="0">';
    h += '<td>' + localeList['noRecords'] + '</td>'; 
    if (infoWin == 'window') h += '<td><a href="javascript:this.close();"><img align="right" src="images/close.gif" border=0 ></a></td>';
    h += '</tr></table>';
    return h;
}

/**
 * Return HTML for zoom parameters (zoomall, autozoom)
 */
function returnZoomParamsHtml(zp, infoWin) {
    var infoWin = zp.infoWin;
    var allextent = zp.allextent;
    var autozoom = zp.autozoom;
    var zoomall = zp.zoomall;
    var ref2opener = (infoWin != 'window' ? '' : 'opener.');
    if (allextent) PMap.extentSelectedFeatures = allextent;
    
    var html = '';
    if (zoomall) {
        var zStr = '<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr><td class=\"zoomlink\">';
        zStr += "<a href=\"javascript:";
        zStr += ref2opener + 'zoom2extent(0,0,\'' + allextent + '\')';
        zStr += '\"><img src=\"images/zoomtoall.gif\"alt=\"za\"></a></td><td class=\"TDAL\">' + localeList['zoomSelected'] + '</td></tr></table>';
        
        html += zStr;
    }
    
    // Add image for onload event
    var azStr = '<img src=\"images/blank.gif\" onload=\"';  
    if (autozoom) {
        if (autozoom == 'auto') {
            azStr += ref2opener + 'zoom2extent(0,0,\'' + allextent + '\');';
        } else if (autozoom == 'highlight') {
            azStr += ref2opener + 'updateMap(' + ref2opener + 'PM_XAJAX_LOCATION + \'x_load.php?' + SID +  '&mode=map&zoom_type=zoompoint\', \'\')';
        }
    } else {
        azStr += ref2opener + '$(\'#zoombox\').hidev();';
    }
    
    azStr += '\" />';
    html += azStr;
    
    return html;
}



/**
 * parse IQuery (auto-identify) result
 */
function parseJSON_IQuery(jsonstr) {

    // Nothing found with query 
    if (jsonstr == 0) {
        return false;
    }

    // Features found, parse JSON string
    var groups = jsonstr[0];    
    
    // Only take the first layer from group
    var layObj = groups[0];
    
    var startcol = 1; 
    
    //*** Header line ***/
    var hL = layObj.header;    
    var hLlen = hL.length;
    
    //*** Values of the layer ***/
    var vL = layObj.values;  
        
    //--- Rows ---//
    var rowL = vL[0];  // <====== Only take the first from result
     
    // Loop through records and create HTML
    var rhtml = '';
    rhtml += '<table class="iquery" border="0" cellspacing="0" cellpadding="0">';
    rhtml += '<tr><th colspan="2" class="header">' + layObj.description + '</td></tr>';
    
    for (var hi=startcol; hi<hLlen; hi++) {
        rhtml += '<tr>';
        rhtml += '<th>' + hL[hi] + '</th>';
        
        // Check for Hyperlinks
        if (isObject(rowL[hi])) {
            var hypLinkL = rowL[hi].hyperlink;
            rhtml += '<td>' + hypLinkL[3] + '</td>';
        } else {
            rhtml += '<td>' + rowL[hi] + '</td>';
        }
        
        rhtml += '</tr>';
    }
    rhtml += '</table>';
    
    return rhtml;
}




/**
 * Parse JSON result string with parseJSON()
 * and insert resulting HTML into queryresult DIV
 */
function writeQResult(resultJSON, infoWin) {
    //alert(infoWin);
    var queryResultDiv = infoWin == 'window' ? 'queryResult' : 'infoFrame';
    
    if (queryResultLayout == 'table') {
        var resstr = parseJSON(resultJSON, infoWin);
        if (infoWin == 'dynwin') {
            createDnRDlg({w:400, h:200}, {resizeable:true, newsize:false}, 'pmQueryContainer', 'Result', false);
            $('#pmQueryContainer_MSG').html(resstr).addClass('pmInfo').addClass('jqmdQueryMSG');
        } else {
            $('#' + queryResultDiv).html(resstr);
        }
        sortables_init();
    } else {
        var restree = parseJSON_treeview(resultJSON, infoWin);
        if (infoWin == 'dynwin') {
            createDnRDlg({w:600, h:200}, {resizeable:true, newsize:false}, 'pmQueryContainer', localeList['Result'], false);
            $('#pmQueryContainer_MSG').html(restree).addClass('jqmdQueryMSG');
            queryResultDiv = 'pmQueryContainer_MSG';
        } else {
            $('#' + queryResultDiv).html(restree);
        }
        
        try {
            if (infoWin == 'window') {
                var treeStyle = opener.queryTreeStyle['treeview'];
            } else {
                var treeStyle = queryTreeStyle['treeview'];
            }
        } catch(e) {
            var treeStyle = {collapsed: true, unique: true};
        }
        
        $('#' + queryResultDiv  + ' > ul').Treeview(treeStyle);          
    }
}



/**
 * Check if var is an object
 */
function isObject(a) {
    return (a && typeof a == 'object') || typeof a == 'function';
} 


