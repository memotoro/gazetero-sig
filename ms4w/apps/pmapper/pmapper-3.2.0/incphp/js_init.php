<script type="text/javascript">

 var SID = '<?php echo SID ?>';
 var PM_XAJAX_LOCATION = '<?php echo $PM_XAJAX_LOCATION ?>';
 var PM_INCPHP_LOCATION = '<?php echo $PM_INCPHP_LOCATION ?>';
 var PM_PLUGIN_LOCATION = '<?php echo $PM_PLUGIN_LOCATION ?>';
     
 var mapW = <?php echo $mapW ?>;
 var mapH = <?php echo $mapH ?>;
 var refW = <?php echo $refW ?>;
 var refH = <?php echo $refH ?>;
 var minx_geo, maxy_geo;
 var xdelta_geo, ydelta_geo;

 var PMQuery = new PM_Query();

 var PMap = new PMap();
 //PMap.sid = '<?php echo session_id() ?>';
 //PMap.sname = '<?php echo session_name() ?>';
 PMap.gLanguage = '<?php echo $gLanguage ?>';
 PMap.config = '<?php echo trim($config) ?>';
 PMap.grpStyle = '<?php echo $_SESSION["grpStyle"] ?>';
 PMap.legStyle = '<?php echo $_SESSION["legStyle"] ?>';
 PMap.infoWin = '<?php echo $_SESSION["infoWin"] ?>';
 PMap.s1 = <?php echo $maxScale ?>;
 PMap.s2 = <?php echo $minScale ?>;
 PMap.dgeo_x = <?php echo $dgeo['x'] ?>;
 PMap.dgeo_y = <?php echo $dgeo['y'] ?>;
 PMap.dgeo_c = <?php echo $dgeo['c'] ?>;
 PMap.layerAutoRefresh = <?php echo ($_SESSION['layerAutoRefresh']) ?>;
 PMap.tbThm = '<?php echo $toolbarTheme ?>';
 PMap.tbImgSwap = <?php echo isset($toolbarImgSwap) ? $toolbarImgSwap : 0?>;
 
 PMap.pluginTocInit = [<?php if (count($plugin_jsTocInitList) > 0) echo ("'" . implode("','", $plugin_jsTocInitList) . "'"); ?>];
 
 <?php echo $jsArrays ?>

</script>
