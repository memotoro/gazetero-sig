<?php

class PMapGroup
{
    protected $map;
    protected $groupname;
    protected $groups;
    protected $grouplist;
    
   /**
    * Class constructor
    * @param object $map map object
    * @param string $groupname name of group
    * @return void
    */ 
    public function __construct($map, $groupname)
    {
        $this->map = $map;
        $this->groupname = $groupname;
        $this->groups = $_SESSION['groups'];
        $this->grouplist = $_SESSION['grouplist'];
        $this->group = $this->grouplist[$groupname];
    }
    
   /**
    * Return extent of a group as object
    * @param bool $restrictToMapExt define if extent shall be restricted to map projection 
    * @return object extent with minx, miny, maxx, maxy properties 
    */ 
    public function getGroupExtent($restrictToMapExt)
    {
        require_once("pmaplayer.php");
        $glayerList = $this->group->layerList;
        
        $mExtMinx = 999999999;
        $mExtMiny = 999999999;
        $mExtMaxx = -999999999;
        $mExtMaxy = -999999999;
                
        foreach ($glayerList as $glayer) {
            $pmapLayer = new PMapLayer($this->map, $glayer->glayerName);
            $layerExt = $pmapLayer->getLayerExtent(true);
            $mExtMinx = min($mExtMinx, $layerExt->minx);
            $mExtMiny = min($mExtMiny, $layerExt->miny);
            $mExtMaxx = max($mExtMaxx, $layerExt->maxx);
            $mExtMaxy = max($mExtMaxy, $layerExt->maxy);
        }

        if ($restrictToMapExt) {
            $mapExt = $this->map->extent;
            $mExtMinx = max($mExtMinx, $mapExt->minx);
            $mExtMiny = max($mExtMiny, $mapExt->miny);
            $mExtMaxx = min($mExtMaxx, $mapExt->maxx);
            $mExtMaxy = min($mExtMaxy, $mapExt->maxy);
        }
        
        $groupExt['minx'] = $mExtMinx;
        $groupExt['miny'] = $mExtMiny;
        $groupExt['maxx'] = $mExtMaxx;
        $groupExt['maxy'] = $mExtMaxy;

        return $groupExt;
    }

}


?>