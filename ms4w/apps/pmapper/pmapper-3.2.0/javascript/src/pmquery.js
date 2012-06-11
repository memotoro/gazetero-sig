
/*****************************************************************************
 *
 * Purpose: Functions for queries
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

//var PMQuery = new PM_Query();

function PM_Query() {
    this.iquery_timer = null;
    try {this.follow = autoIdentifyFollowMouse;} catch(e) {this.follow = false;};
    this.timeW = -1;
    this.timeA = 2;
    this.timer_c = 0;
    this.timer_t = null;
    this.timer_to = null;
}

/**
 * Start identify (query) or select (nquery) 
 */
function showqueryresult(type, xy) {
    var pos = xy.split('+');
    if (type=='query') {
        var mx = pos[0]; 
        var my = pos[1];
    } else {
        var mx = pos[2]; 
        var my = pos[3];
    }
    pmIndicator_show(mx, my);
    
    var queryurl = PM_XAJAX_LOCATION + 'x_info.php';
    
    var varform = document.getElementById("varform");
    if (type == 'query') {
        var qparams = SID + '&mode='+type + '&imgxy='+xy; // + layerstring;
    } else {
        var qparams = SID + '&mode='+type + '&imgxy='+xy + '&groups=' + getSelectLayer();
        varform.zoomselected.value = '1';
    }

    if (PMap.infoWin == 'window') {
        openResultwin('blank.html');
    }
    
    getQueryResult(queryurl, qparams);
}


/**
 * Start attribute search
 */
function submitSearch() {
    pmIndicator_show(false, false);
    
    var searchForm = _$('searchForm');
    var skvp = getFormKVP('searchForm');
    //alert(skvp);
    
    if (PMap.infoWin != 'window') {
        searchForm.target='infoZone';
    } else {
        var resultwin = openResultwin('blank.html');
        searchForm.target='resultwin';
    }
    
    var queryurl = PM_XAJAX_LOCATION + 'x_info.php';
    var params = SID + '&' + skvp + '&mode=search';
    //alert(queryurl);
    getQueryResult(queryurl, params);
}

/**
 * Immediately launch search search from the suggest obj
 */
function launchSearchFromSuggest() {
    if (typeof(pmSuggestLaunchSearch)!="undefined") {
        if (pmSuggestLaunchSearch) {
            submitSearch();
        } 
    }
}


/*
 * RETURN LAYER/GROUP FOR SELECTION
 *************************************/
function getSelectLayer() {
    var selform = _$("selform");
    if (selform) {
        if (selform.selgroup) {
            var sellayer = selform.selgroup.options[selform.selgroup.selectedIndex].value;
            var layerstring = "&groups=" + sellayer;
            //alert(sellayer);
            return sellayer;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


/**
 * Start auto-identify (iquery)
 */
function applyIquery(mx, my) {
    var imgxy  = mx + "+" + my;
    var queryurl = PM_XAJAX_LOCATION + 'x_info.php?' +SID+ '&mode=iquery' + '&imgxy='+imgxy + '&groups=' + getSelectLayer();
    getQueryResult(queryurl, '');
}


/**
 * TIMER FOR OAUTO_IDENTIFY ACTION 
 * indicates for how much time the cursore remains firm on the map [by Natalia]
 */
function timedCount(moveX, moveY) {  
    if (PMQuery.timer_c == 0){
        X = moveX;
        Y = moveY;
    }
    if (PMQuery.timer_c == 1){
        PMQuery.iquery_timer = setTimeout("applyIquery(" + X + "," + Y + ")", 200);
    }
    PMQuery.timer_c += 1;
    PMQuery.timer_t = setTimeout("timedCount()",PMQuery.timeA);
}


/**
 * Display result in DIV and postion it correctly
 */
function showIQueryResults(queryResult) {
    var iQL = $('#iqueryLayer');
    var IQueryResult = parseJSON_IQuery(queryResult);
    var map = $('#mapImg');
    
    if (PMQuery.follow){
        // border limits
        var limitRG = map.iwidth() - iQL.iwidth() - 4; // Right
        var limitLF = 0;                // Left
        var limitTP = 0;                // Top
        var limitDN = map.iheight() - iQL.iheight() - 4;    // Down
        
         //gap between mouse pointer and iqueryLayer:
        var gap = 10;

        var mapElem = _$('map');//document.getElementById('map');
        if (mapElem) mapElem.onmouseover = getGeoCoords; 
        
        // right:
        if (moveX >= limitRG){
            iQL.left(moveX - iQL.iwidth() - gap + 'px');
        } else {
            iQL.left(moveX + gap +'px');
        }
        
        // down:
        if (moveY >= limitDN){
            iQL.top(moveY - iQL.iheight() - gap + 'px');
        } else {
            iQL.top(moveY + gap +'px');          
        }
    
        if (IQueryResult) {
            iQL.html(IQueryResult).showv();
            if (PMQuery.timeW != -1) PMQuery.timer_to = setTimeout("hideIQL()",PMQuery.timeW);
        } else {
            iQL.html('').hidev();
            clearTimeout(PMQuery.timer_t);
            clearTimeout(PMQuery.iquery_timer);
        }
    // no follow, display on fixed position
    } else {
        if (IQueryResult) {
            iQL.html(IQueryResult).showv();
        } else {
            iQL.html('').hidev();
        }
    }
}


function hideIQL() {
    clearTimeout(PMQuery.iquery_timer);
    $('#iqueryLayer').hidev();
}


function mapImgMouseOut() {
    //alert('out');
    var vMode = _$("varform").mode.value;
    if (vMode == 'iquery' || vMode == 'nquery') {
        $('#iqueryLayer').hidev();
    }
}
