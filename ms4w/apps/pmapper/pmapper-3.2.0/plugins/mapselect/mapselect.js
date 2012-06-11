
/**
 * Initialize select box and add to specified DOM element
 */
function mapselect_init() {
    var select =  pmMapSelectSettings.displayText + ' <select id="mapSelector" onchange="mapSelectChange()">';
    $.each(pmMapSelectSettings.configList, function(key, val) {
        var presel = key == PMap.config ? 'selected="selected"' : '';
        select += '<option value="' + key + '" ' + presel + '>' + val + '</option>';
    });
    select += '</select>';
       
    $('#' + pmMapSelectSettings.divId).append('<div>' + select + '</div>');
}

/**
 * OnChange function for select box; 
 * reloads application with selected config parameter
 */
function mapSelectChange() {
    $("#mapSelector option:selected").each(function () {
        var baseLoc = location.href.split(/\?/)[0];
        var searchLoc = location.search;
        var sessId = pmMapSelectSettings.keepSession ? '&' + SID : '';
        var configUrl = baseLoc;
        
        if (searchLoc.length > 0) {
            if (searchLoc.match(/config=[a-zA-Z0-9\_]+/)) {
                configUrl += searchLoc.replace(/config=[a-zA-Z0-9\_]+/,'config=' + $(this).val());
            } else {
                configUrl += searchLoc + '&config=' + $(this).val();
            }
            
            if (!searchLoc.match(/PHPSESS/))  configUrl += sessId;
        } else {
            configUrl += '?config=' + $(this).val() + sessId;
        }
        //alert(configUrl);
        window.location = configUrl;
    });
}

