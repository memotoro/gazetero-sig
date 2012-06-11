
Adds a select box to let user swap to another map file configuration
uses the function to load different config files via URL, like

http://www.myurl.org?config=xyz

requires the definition of pmMapSelectSettings in /config/common/js_config.php
 
var pmMapSelectSettings = { displayText:localeList['Select Theme'],
                            configList:{'default':"Map Default", 
                                        'dev':"Map Dev"
                                       }, 
                            divId:"north",
                            keepSession:true
                           };
                           
displayText: Text to show before checkbox 
configList:  contains key:value pairs, 
             key = definition of the config (corresponds to 'config_...ini' file), 
             value = the text displayed in the GUI
divId: Id of DOM element where to add the select box
keepSession: boolean,  
             true: keep session id, reload map with current extent