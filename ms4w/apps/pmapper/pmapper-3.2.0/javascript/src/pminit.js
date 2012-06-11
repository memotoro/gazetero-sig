/******************************************************************************
 *
 * Purpose: initialize various p.mapper settings 
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
 * Global PMap object; 
 * stores main status variables set via incphp/js_init.php
 */
function PMap() {
    this.scale = null;
    this.resize_timer = null;
}

 /**
 * Initialize function; called by 'onload' event of map.phtml
 * initializes several parameters by calling other JS function
 */
function pm_init() {      
    // initialization of toolbar, menu, slider HOVER's (and others)
    pmToolbar_init();
    pmMenu_init();
    pmSlider_init();
    
    // Add Resize Event to main window   
    window.onresize = function(){pmLayout_init();};
    pmLayout_init();

    // Add properties to mapImg
    var imgTmpMap = _$("mapImg");
    imgTmpMap.onload = resetMapImgParams;
    imgTmpMap.onmouseover = startUp;
    //$("#mapImg").load(function(){resetMapImgParams();}).mouseover(function(){startUp();});
    
    // Initialize TOC/legend
    pmTabs_init('#tocTabs', 'tab_toc');
    pmToc_init();
    
    // Set zoombox class for Opera and Konqueror to non-semitransparent
    if (navigator.userAgent.match(/Opera|Konqueror/i)) {
        _$("zoombox").className = 'zoombox_nontransp';
    }   

    createZSlider('zslider');
    setSearchOptions();
    domouseclick('zoomin');
    setTbTDButton('zoomin');
    pmIndicator_init();
    
    // Add jQuery events
    $(document).keypress( function(event) { kp(); } );
    $('#mapimgLayer').mouseout( function() { setTimeout('mapImgMouseOut()', 800); });  
    $('#refMapImg').mouseover( function() {startUpRef();} );
} 


/**
 * HOVER effect for slider
 * initialized in pm_init()
 */
function pmSlider_init() {
    $('#sliderArea').hover(
        function(){ $(this).addClass("sliderAreaOver").removeClass("sliderAreaOut"); },
        function(){ $(this).addClass("sliderAreaOut").removeClass("sliderAreaOver"); }
    );
}

/**
 * DHTML jQuery menu
 * initialized in pm_init()
 */
function pmMenu_init() {
    $('ul.pm_menu > li').each(function() {            
        $(this).hover(
            function() { $(this).addClass('pm_menu_hover'); },
            function() { $(this).removeClass('pm_menu_hover'); }
        );

        $(this).click(function() {
            pmMenu_toggle($(this).parent().id());
            eval($(this).id().replace(/pmenu_/, '') + '()');
        });
    });
}

/**
 * Show/hide pm_menu
 */
function pmMenu_toggle(menu)
{
    var obj = $('#' + menu); 
    if (obj.css('display') == 'none') {
        obj.show('fast');
        $('#' + menu + '_start > img').src('images/menuup.gif');
    } else {
        obj.hide('fast');
        $('#' + menu + '_start > img').src('images/menudown.gif');
    }
}

/**
 * Initialize toolbar hover's
 */
function pmToolbar_init() {
    if (PMap.tbImgSwap != 1) {
        $('#mapZone .TOOLBARTD').each(function() {            
            $(this).hover(
                function(){ if (!$.className.has(this,"TOOLBARTD_ON")) $(this).addClass("TOOLBARTD_OVER"); },
                function(){ $(this).removeClass("TOOLBARTD_OVER"); }
            );
        });
    } else {
         $('#mapZone .TOOLBARTD').each(function() {            
            $(this).hover(
                function(){ if (!$(this).find('>img').src().match(/_on/)) $(this).find('>img').imgSwap('_off', '_over'); },
                function(){ $(this).find('>img').imgSwap('_over', '_off'); }
            );
        });
    }
}

/**
 * Initialize buttons
 */
function pmCButton_init(but) {
    $("#" + but).hover(
        function(){ $(this).addClass("button_on").removeClass("button_off"); },
        function(){ $(this).addClass("button_off").removeClass("button_on"); }
    );
}

function pmCButton_init_all() {
    $("[@name='custombutton']").each(function() {            
        $(this).hover(
            function(){ $(this).addClass("button_on").removeClass("button_off"); },
            function(){ $(this).addClass("button_off").removeClass("button_on"); }
        );
    });
}


/**
 * Initialize Tabs
 */
function pmTabs_init(tabdiv, activated) {   
    $(tabdiv + '>ul>li>a#'+activated).parent().addClass('tabs-selected');   
    var numTabs = $(tabdiv + '>ul>li').length;
    var tabW = parseInt(100 / numTabs) + '%';
    $(tabdiv + '>ul>li>a').each(function() {            
        $(this).click(function() {  
            $(tabdiv + '>ul>li').removeClass('tabs-selected');
            $(this).parent().addClass('tabs-selected');         
        });
        $(this).parent().css('width',tabW);
    });
}

/**
 * add div for wait indicator
 */
function pmIndicator_init() {
    $('body').append('<div id="pmIndicatorContainer" style="display:none; position:absolute; z-index:99"><img src="images/indicator.gif" alt="wait" /></div>');
}


/**
 * Initialize all context menus
 */
function contextMenus_init() {
    if (typeof(pmContextMenuList)!="undefined") {
        $.each(pmContextMenuList, function() {
            var cmdiv = '<div style="display:none" class="contextMenu" id="' + this.menuid + '">';
            var cmbindings = {};
            
            cmdiv += '<ul>';
            $.each(this.menulist, function() {
                cmdiv += '<li id="' + this.id + '">';
                if (this.imgsrc) cmdiv += '<img src="images/menus/' + this.imgsrc + '" />';
                cmdiv += _p(this.text) + '</li>';
                
                var run = this.run;
                cmbindings[this.id] = function(t) {eval(run + '("' + t.id + '")')};
            });
            
            $('body').append(cmdiv);
            $(this.bindto).contextMenu(this.menuid, {
                bindings: cmbindings, 
                menuStyle: this.styles.menuStyle,
                itemStyle: this.styles.itemStyle,
                itemHoverStyle: this.styles.itemHoverStyle
            });
        });
    }
}

function showGroup(gid) {
    //alert(gid);
    //alert($('#jqContextMenu').left());
}
