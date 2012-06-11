
/*****************************************************************************
 *
 * Purpose: Functions for forms and scale selection list
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


var scale_timeout;

function initScaleSelect() {
    try {
        writeScaleList(scaleSelectList);
    } catch(e) {
        return false;
    }
}

function writeScaleList(scaleList) {
    var scaleListLen = scaleList.length;
    var sobj = $('#scale_suggest');
    
    // If no scales defined don't use select function
    if (scaleListLen < 1) {
        return false;
    } else {
        $('#scaleArea >input').attr("autocomplete", "off");
        //var $input = $(input).attr("autocomplete", "off");;
    }
    var sobj = $('#scale_suggest');
    sobj.showv();
    sobj.html('');

    var suggest_all = '';
    for(i=0; i < scaleListLen ; i++) {
        var sclink = i<1?'scale_link_over':'scale_link';
        var suggest = '<div onmouseover="javascript:scaleOver(this);" ';
        suggest += 'onmouseout="javascript:scaleOut(this);" ';
        suggest += 'onclick="insertScaleTxt(this.innerHTML);" ';
        suggest += 'class="' + sclink + '">' + scaleList[i] + '</div>';
        suggest_all += suggest;
    }
    sobj.html(suggest_all);
}

function insertScaleTxt(value) {
    var newScale = value.replace(/,|'|\.|\s/g, '');
    $('#scaleinput').val(newScale);
    $('#scale_suggest').html('');
    hideScaleSuggest();
    zoom2scale(newScale);
}

function scaleOver(div_value) {
    div_value.className = 'scale_link_over';
}


function scaleOut(div_value) {
    div_value.className = 'scale_link';
}

function scaleMouseOut(force) {
    var sobj = _$('scale_suggest');
    var scaleDivList = sobj.getElementsByTagName('DIV');
    var hlStyle = false;

    for (var i=0; i<scaleDivList.length; i++) {
        if (scaleDivList[i].className == 'scale_link_over') {
            hlStyle = true;
        }
    }
    
    if (force) {
        setTimeout("hideScaleSuggest()", 500);
        //return false;
    } else {
    
        clearTimeout(scale_timeout);
        if (hlStyle) {
            
        } else {
            scale_timeout = setTimeout("hideScaleSuggest()", 500);
        }
    }
}


function hideScaleSuggest() { 
    $('#scale_suggest').hidev();
}

function setScaleMO() {
    scale_mouseover = true;
}
