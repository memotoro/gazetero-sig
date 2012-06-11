
/******************************************************************************
 *
 * Purpose: functions for formatting TOC and legend output
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
 * initialize and write TOC by calling XMLHttp function 'updateToc()' 
 * 
 */
function pmToc_init() {
    var tocurl = PM_XAJAX_LOCATION + 'x_toc.php?' + SID;
    updateToc(tocurl); 
}


/**
 * Initialize TOC tree
 */
function treeInit(catStyle, grpStyle) { 
    var catcList = $('div.catc');
    for (var i=0; i<catcList.length; i++) {  
        catcList[i].style.display='none';
        tg(catcList[i].id);
    }
    // collapse all groups
    if (PMap.grpStyle == 'tree') {
        $('div.grpc').hide();
    } 
    
    // check/enable default groups
    setDefGroups();
    
    // check/enable all categories
    $("#toc input[@name='catscbx']").check('on');
    
    // run all scripts after init of toc
    tocPostLoading();
}


/**
 *
 */
function tocPostLoading() {
    // enable all context menus
    contextMenus_init();
    
    // execute all init scripts after TOC full loading
    for (var i=0; i<PMap.pluginTocInit.length; i++) {
        eval(PMap.pluginTocInit[i]);
    }
}

/**
 * Toggle groups and categories (and related images)
 * attached as onClick script to plus/minus icons and links
 */
function tg(group) {
    var divobj = $('#' + group);
    if (divobj.css('display') == 'none') {
        $('#' + group + '_timg').src('images/tree/minus.gif');
        divobj.show();
    } else {
        $('#' + group + '_timg').src('images/tree/plus.gif');
        divobj.hide();
    }
}

 

/**
 * Sets popup legedn over map visible
 * attached as onClick script to button
 */
function showPopupLegend() {
    var tocurl = PM_XAJAX_LOCATION + 'x_toc.php?' + SID + '&legendonly=1';
    showMapLegend(tocurl); 
}


/**
 * for legStyle 'swap': swap from LAYER view to LEGEND view
 * attached as onClick script to button
 */
function swapToLegendView() {
    var tocurl = PM_XAJAX_LOCATION + 'x_toc.php?' + SID + '&legendonly=1&swaplegend=1';
    swapLegend(tocurl); 
}

/**
 * for legStyle 'swap': swap from LEGEND view to LAYER view
 * attached as onClick script to button
 */
function swapToLayerView() {
    $('#toclegend').hide();
    $('#toc').fadeIn('normal');
    // update TOC CSS depending on scale
    var tocurl = PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
    updateTocScale(tocurl);
}


/**
 * Set default groups from config.ini to 'ON'
 */
function setDefGroups() {
    var defGroupListL = PMap.defGroupList.length;
    if (defGroupListL > 0) {
        for (var x=0; x<defGroupListL; x++) {
            var chkGrp = _$(PMap.defGroupList[x]);
            if (chkGrp) {
                chkGrp.checked = true;
            }
        }
    }
}


/**
 * Change layers, called from
 * - onclick event of group checkbox)
 * - and setcategories()
 */
function setlayers(selelem, noreload) {
    // if request comes from group checkbox
    if (selelem) {
        // Check if layer is not visible at current scale
        if ((_$('spxg_' + selelem).className == 'unvis') && (!noreload)) {
            noreload = true;
        }
        
        // Check if layers should be mutually disabled
        if (PMap.mutualDisableList) {
            var mdl = PMap.mutualDisableList;
            if (mdl.inArray(selelem)) {
                for (var i=0; i<mdl.length; i++) {
                    if (mdl[i] != selelem) {
                        _$('ginput_' + mdl[i]).checked = false;
                    }
                }
            }
        }
    }
    
    var layerstring = '&groups=' + getLayers();    
    
    // reload whole map
    if ((PMap.layerAutoRefresh == '1') && (!noreload)) {     
        showloading();
        var mapurl = PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=zoompoint'+ layerstring;
        updateMap(mapurl);
    // just update 'groups' array of session, no map reload
    } else {
        var passurl = PM_XAJAX_LOCATION + 'x_layer_update.php?'+SID+layerstring;
        updateSelLayers(passurl);
    }
}


/**
 * Return layers/groups
 */
function getLayers() {
    var laystr = '';
    $("#layerform :input[@name='groupscbx'][@checked]").not(':disabled').each(function() { 
        laystr += $(this).val() + ','; 
    });
    laystr = laystr.substr(0, laystr.length - 1);
    return laystr;
}


/**
 * Set categories (called from onclick event of categories checkbox)
 */
function setcategories(cat) {
    var cat_activated = $('#cinput_' + cat).is(':checked');
    var grpList = $('#' + cat).find('input[@type="checkbox"]');
    var checkedLayers = false;
    var visLayers = false;
    for (var i=0; i<grpList.length; i++) {
        var grp = grpList[i];
        //alert(grp.id);
        if (cat_activated) {
            grp.disabled = false;
        } else {
            grp.disabled = true;
        }
        if (grp.checked) {
            checkedLayers = true;
            if (_$('spxg_' + (grp.id.replace(/ginput_/, ''))).className == 'vis') {
                visLayers = true;
            }
        }
    }
    //alert('checkedLayers ' + checkedLayers + '  -- visLayers ' + visLayers);
    
    if (checkedLayers && visLayers) {
        setlayers(false, false);
    } else {    
        setlayers(false, true);
    }
}


/**
 * Resize TOC container depending on window resize
 * called by pmLayout_init()
 */
function tocResizeUpdate() {
    var tocCont = $('#tocContainer');
    var parentH = tocCont.parent().iheight();
    var tDelta = 0;
    $('#tocContainer').parent().find(' >div').not('#tocContainer').each( function() {
        tDelta += parseInt($(this).height());
    });    
  
    var tocDeltaH = (tDelta > 0 ? tDelta : $('#tocContainer').itop());   
    $('#tocContainer').top(tocDeltaH + 'px').height((parentH-tocDeltaH-5) + 'px');
}


