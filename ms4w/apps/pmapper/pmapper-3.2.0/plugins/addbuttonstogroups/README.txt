This plugin add buttons for each groups / layer in TOC.

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- addbuttonstogroups.js.php: the init function call after TOC loading.

Dependancies :
No dependancy

How to use:
Add the string "addbuttonstogroups" to the plugins list in the pmapper config file.

Parameters:
"abtgList" : specify the buttons to add.
For instance, with 'abtgList = "info|showGroupInfo|Layer Info|images/infolink.gif","zoom|zoom2group|Zoom To Layer|images/zoomtiny.gif"', the group with the following HTML code :
<th id="tgrp_countries" class="grp">
  <span id="spxg_countries" class="vis">Countries</span>
</th>
will become :
<th id="tgrp_countries" class="grp">
  <span id="spxg_countries" class="vis">Countries</span>
  <a title="Layer Info" href="javascript:showGroupInfo('tgrp_countries')">
    <img src="images/infolink.gif" alt="Layer Info"/>
  </a>
  <a title="Zoom To Layer" href="javascript:zoom2group('tgrp_countries')">
    <img src="images/zoomtiny.gif" alt="Zoom To Layer"/>
  </a>
</th>

TO DO :
maybe add buttons to legend

