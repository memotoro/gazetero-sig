/*****************************************************************************
 *
 * Purpose: set transparency of groups/layers
 * Author:  Armin Burger
 *
 *****************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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
 * Post the Transparency value to PHP GROUP object
 */
function setGroupTransparency(pos) {
    var groupname = $('#transpdlg_groupsel option:selected').val();
    if (typeof(groupname)=='undefined') {
        var groupname = $('#layerSliderCont').attr('name');
        var cmenu = 1;
    }
    if (groupname == '#') return false;
    if (cmenu) $('#layerSliderContTab').remove();
    
    var transparency = Math.round(pos  * 100);
    var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        success: function(response){
            PMap.groupTransparencies[groupname] = transparency;
            if (response.reload && (PMap.layerAutoRefresh == '1')) {
                showloading();
                reloadMap(false);
            }
        }
    });
}


/**
 * Initialize transparency values of groups and create a transparency object for PMap
 */
function initGroupTransparencies() {
    var url = PM_PLUGIN_LOCATION + '/transparency/x_get-transparencies.php?' + SID;
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        success: function(response){
            PMap.groupTransparencies = response.transparencies;
        }
    });
    
    //$('#toc').keypress(function (e) {
    $('#toc').keypress(function (e) {
        alert(e.which);
    });

}


/**
 * Set slider to transparency value of group
 */
function setTransprarencySlider(pgrp) {
    var groupname = pgrp ? pgrp : $('#transpdlg_groupsel option:selected').val();
    if (groupname == '#') return false;
    transpdlg_slider.setPosition(PMap.groupTransparencies[groupname]/100);
}


/**
 * Open the Transparency dialog
 */
var transpdlg_slider;
function openTransparencyDlg() {
    var dlgfile = PM_PLUGIN_LOCATION + '/transparency/transparencydlg.phtml?'+SID;
    createDnRDlg({w:200, h:100, l:250, t:250}, {resizeable:false, newsize:true}, 'pmDlgContainer', _p('Layer_transp'), dlgfile);
}


/**
 * Create slider for transparency setting
 */
function createTransparencySlider(sliderDiv, w) {
    transpdlg_slider = new slider(
        sliderDiv,  // id of DIV where slider is inserted
        8,        //height of track
        w,       //width of track
        '#eeeeee', //colour of track
        1,         //thickness of track border
        '#000000', //colour of track border
        2,         //thickness of runner (in the middle of the track)
        '#666666', //colour of runner
        20,        //height of button
        10,        //width of button
        '#999999', //colour of button
        1,         //thickness of button border (shaded to give 3D effect)
        //'<img src="images/slider_updown.gif" style="display:block; margin:auto;" />', //text of button (if any)
        '', //text of button (if any)
        true,      //direction of travel (true = horizontal, false = vertical)
        false, //the name of the function to execute as the slider moves
        'setGroupTransparency', //the name of the function to execute when the slider stops
        null          //the functions must have already been defined (or use null for none)
        );
}



function cmOpenTranspDlg(gid) {
    $('#layerSliderContTab').remove();
    var dlgx = ($('#jqContextMenu').ileft() - 60) + 'px';
    var dlgy = ($('#jqContextMenu').itop() + 10) + 'px';
    var groupname = gid.substr(5);
    
    var cont = $('<div id="layerSliderContTab"><table><tr><td style="padding:6px 3px"><img src="images/menus/transparency-bw.png"/></td><td><div id="layerSliderCont" name="'+groupname+'"></div></tr></table></div>')
                 .css({display:'inline',backgroundColor:'#fff',border:'1px solid #999', position:'absolute',zIndex:99, left:dlgx, top:dlgy, width:'140px', height:'auto'})
                 .dblclick( function () {$(this).remove() })
                 .appendTo('body')
                 .show();
    $().keydown(function (e) {if (e.which == 27) $('#layerSliderContTab').remove()});
    
    createTransparencySlider('layerSliderCont', 100);
    setTransprarencySlider(groupname);
}

