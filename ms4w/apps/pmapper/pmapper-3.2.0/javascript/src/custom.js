

//
// HYPERLINK FUNCTION FOR RESULT WINDOW
//

function openHyperlink(layer, fldName, fldValue) {
    
    switch(layer) {
        case 'cities10000eu':
            //if (fldName == 'CITY_NAME') {
                window.open('http:/' + '/en.wikipedia.org/wiki/' + fldValue, 'wikiquery');
            //}
            break;
            
        default:
            alert ('See function openHyperlink in custom.js: ' + layer + ' - ' + fldName + ' - ' + fldValue);
    }
}


function showCategoryInfo(cat) {
    alert('Info about category: ' + cat);
}


function showGroupInfo(group) {
    alert('Info about group: ' + group);
}



