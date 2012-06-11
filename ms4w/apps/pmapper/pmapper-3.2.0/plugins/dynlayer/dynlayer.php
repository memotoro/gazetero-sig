<?php

class DynLayer 
{
    protected $map;
    protected $json;
    protected $dynLayerList;
     
    public function __construct($map, $jsonString)
    {
        $this->map = $map;
        $this->json = json_decode($jsonString);
    }
    
    
    public function initDynLayers()
    { 
        $this->createDynLayers();
        require_once($_SESSION['PM_INCPHP'] . "/initgroups.php");
        $iG = new Init_groups($this->map, $_SESSION['allGroups'], $_SESSION['language']); 
    }
    
    
   /**
    * Create dynamic layers based on JSON definition
    */
    public function createDynLayers()
    {
        foreach ($this->json as $dObj) {
            $require = $dObj->require;
            
            foreach ($dObj->layerlist as $dl) {
                $templateList = $dl->TEMPLATE;
                $this->dynLayerList[] = $dl->name;
                if ($templateList) {
                    $template = $templateList[0];
                    $newLayer = ms_newLayerObj($this->map, $this->map->getLayerByName($template)); 
                    if ($templateList[1]) {
                        $numclasses = $newLayer->numclasses;
                        if ($numclasses > 0) {
                            for ($cl=0; $cl < $numclasses; $cl++) {
                                $newLayer->removeClass($cl);
                            }
                        }
                    }
                } else {
                    $newLayer = ms_newLayerObj($this->map);
                }
                $this->setLayerProperties($dl, $newLayer);
            }
        }
    }
    
   /**
    * Set properties of new layer (class, style, etc.)
    */ 
    protected function setLayerProperties($p, $obj)
    {
        if (is_object($p)) {
            //print_r((array)$p);
            $oList = (array)$p;
            
            foreach ($oList as $k=>$v) {
                if ($k == "TEMPLATE") {
                
                // METADATA tag
                } elseif ($k == "METADATA") {
                    foreach ((array)$v as $mk=>$mv) {
                        $obj->setMetadata($mk, $mv);
                    }
                    
                // class labels    
                } elseif ($k == "label") {
                    $this->createClsLabel($obj, (array)$v);
                
                // new MS Object
                } elseif (is_object($v)) {
                    if ($k == "class" || $k == "style") {
                        $newObj = $this->createMSObj($k, $obj);
                        $this->setLayerProperties($v, $newObj);
                    } else {
                        $this->setLayerProperties($v, $k);
                    }
                } else {
                    // lower case: $layer->set(x,y) 
                    if (preg_match("/color/", $k)) {
                        $obj->$k->setRGB($v[0],$v[1],$v[2]);
                    // normal property
                    } elseif (ctype_lower($k)) {
                        $obj->set($k, $v);
                    // UPPER case tags, set with function setXYZTag()
                    } elseif (ctype_upper($k{0})) {
                        $this->setMSTag($obj, $k, $v);
                    } 
                }
            }
        }
    }
    
   /**
    * Create class and style object
    */ 
    private function createMSObj($type, $pObj)
    {
        switch($type) {
            case "class":
                return ms_newClassObj($pObj);
            case "style":
                return ms_newStyleObj($pObj);
        }
    }
    
   /**
    * Set MS tages that require specific "setXYZ()" function instead of "set(x, y)"
    */ 
    private function setMSTag($obj, $k, $v)
    {
        switch($k) {
            case "PROJECTION":
                $obj->setProjection($v);
                break;
            case "FILTER":
                $obj->setFilter($v);
                break;
            case "EXPRESSION":
                $obj->setExpression($v);
                break;
        }
    }
   
   /**
    * create label with all properties
    */   
    private function createClsLabel($pObj, $lblList)
    {
        foreach ($lblList as $p=>$v) {
            if (preg_match("/color/", $p)) {
                $pObj->label->$p->setRGB($v[0],$v[1],$v[2]);
            } else {
                $pObj->label->set($p, $v);
            }
        }
    }
    
   /**
    * return parsed JSON string for debug reasons
    */ 
    public function returnJson()
    {
        return $this->json;
    }

}



?>