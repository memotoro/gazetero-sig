
/**
 * Return integer values of top, left, width, height
 */
 
jQuery.fn.itop = function() {
    return parseInt(this.top());
};

jQuery.fn.ileft = function() {
    return parseInt(this.left());
};

jQuery.fn.iwidth = function() {
    return parseInt(this.width());
};

jQuery.fn.iheight = function() {
    return parseInt(this.height());
};


/**
 * Set visibility of jQuery object to 'hidden' or 'visible'
 */
jQuery.fn.showv = function() {
    this.css('visibility', 'visible');
};

jQuery.fn.hidev = function() {
    this.css('visibility', 'hidden');
};


/**
 * Set checked status of checkboxes globablly
 */
jQuery.fn.check = function(mode) {
    var mode = mode || 'on'; // if mode is undefined, use 'on' as default
    return this.each(function() {
        switch(mode) {
        case 'on':
            this.checked = true;
            break;
        case 'off':
            this.checked = false;
            break;
        case 'toggle':
            this.checked = !this.checked;
            break;
        }
    });
};


/**
 * Swap image src
 */
jQuery.fn.imgSwap = function(from, to) {
    this.src(this.src().replace(from,to));
    return this;
};


/**
 * Default method for AJAX requests 
 */
$.ajaxSetup( { type: "POST", data:"dummy=dummy" } )
//$.ajaxSetup( { type: "POST" } )


/**
 * Create jqDnR Dialog (jquery.jqmodal_full.js)
 */
function createDnRDlg(win, size, container, title, url) {
    var dlg = '<div style="height: 100%">';
    dlg += '<div id="' + container + '_TC" class="jqmdTC dragHandle">' + title + '</div>';
    dlg += '<div id="' + container + '_MSG" class="jqmdMSG"></div>';
    dlg += '<div id="' + container + '_BC" class="jqmdBC" ';
    if (size.resizeable) {
        dlg += '><img src="templates/dialog/resize.gif" alt="resize" class="resizeHandle" />';
    } else {
        dlg += 'style="height:0px; border:none">';
    }
    dlg += '</div>';
    dlg += '<input type="image" src="templates/dialog/close.gif" onclick="$(this).parent().parent().hide(); $(\'#' + container + '_MSG\').html(\'\')" class="jqmdClose jqmClose" />';
    dlg += '</div>';

    var dynwin = $('#' + container);
    dynwin.html(dlg)
          .jqm({autofire: false, overlay: 0})
          .jqDrag('div.dragHandle');
          
        
    if (size.newsize) dynwin.height(win.h).width(win.w);
    if (win.l) dynwin.css({left:win.l, top:win.t});
    if (size.resizeable) dynwin.jqResize('img.resizeHandle');
    if (url) $('#' + container + '_MSG').load(url);
    
    dynwin.show();
    adaptDWin(dynwin);
}


function adaptDWin(container) {
    var cn = container.id();
    var newMSGh = parseInt($('#' + cn).css('height')) - parseInt($('#' + cn +'_TC').css('height')) - parseInt($('#' + cn + '_BC').css('height')); 
    $('#' + cn + '_MSG').css({height: newMSGh});
}
