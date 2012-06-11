
/**
 * Sample script for custom actions/modes
 * called from mapserver.js/zoombox_apply()
 * must be named '*_start(imgxy)'
 */
function coords_start(imgxy) {
    openCoordsDlg(imgxy);
    //alert(imgxy);
}

/**
 * custom sample script for extending tool functions
 * called from mapserver.js/domouseclick()
 * must be named '*_click()'
 */
function coords_click() {
    var varform = _$("varform");
    varform.mode.value = 'coords';
    varform.maction.value = 'click';
    varform.tool.value = 'coords'; 
    
    // define the cursor
    if (useCustomCursor) {
        setCursor(false, 'crosshair');
    }
}

/**
 * Custom function what to do with mouse click pixel coordinates
 */
function openCoordsDlg(imgxy) {
    var pixccoords = imgxy.split('+');
    var pixX = pixccoords[0];
    var pixY = pixccoords[1];
    
    var mpoint = getGeoCoords(pixX, pixY, false);
    
    $.ajax({
        url: PM_PLUGIN_LOCATION + '/coordinates/x_coords.php?' + SID + '&x=' + mpoint.x + '&y=' + mpoint.y,
        dataType: "json",
        success: function(response){
            var res = response.prjJson;
            //alert(prjJson);
            var rStr = "";
            $.each(res, function(i, n){
                rStr += n.prjName + ' -  x: ' + n.x + ' -  y: ' + n.y + '\nl'; 
            });
            alert(rStr);
        } 
    });  

}