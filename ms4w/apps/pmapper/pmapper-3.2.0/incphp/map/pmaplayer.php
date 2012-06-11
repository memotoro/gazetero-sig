<?php

class PMapLayer
{
    protected $map;
    protected $layername;
    protected $layer;
    protected $layertype;
    
   /**
    * Class constructor
    * @param object $map map object
    * @param string $layername name of layer
    * @return void
    */ 
    public function __construct($map, $layername)
    {
        $this->map = $map;
        $this->layername = $layername;
        $this->layer = $map->getLayerByName($layername);
        $this->layertype = $this->layer->type;
        
        //pm_logDebug(3, $map->getAllLayerNames(), "all layers");
    }
    
   /**
    * Return extent of layer as array
    * @param bool $inMapProjection define if extent shall be returned in map projection 
    * @return object extent with minx, miny, maxx, maxy properties 
    */
    public function getLayerExtent($inMapProjection)
    {
        if ($this->layertype != 3) {
            // PostgIS layers
            if ($this->layer->connectiontype == 6) {
                $data = trim($this->layer->data);
                $dataList1 = preg_split("/\s/", $data);
                $dataList2 = preg_split("/using/i", $data);
                $geomFld = array_shift($dataList1);
                $sql = "select xmin(extent) as minx, ymin(extent) as miny, xmax(extent) as maxx, ymax(extent) as maxy  
                        from (SELECT extent($geomFld) " . substr($dataList2[0], strlen($geomFld)) . ") as bar";
                pm_logDebug(3, $sql, "P.MAPPER-DEBUG: pmaplayer.php/getLayerExtent() - SQL for PG layer extent");
                
                // load DLL on Win if required
                if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
                    if (! extension_loaded('pgsql')) {
                        dl('php_pgsql.dll');
                    }
                }
                
                $connString = $this->layer->connection;
                if (!($connection = pg_Connect($connString))){
                   error_log ("P.MAPPER: Could not connect to database");
                   error_log ("P.MAPPER: PG Connection error: " . pg_last_error($connection));
                   exit();
                }
                
                $qresult = pg_query ($connection, $sql);
                if (!$qresult) error_log("P.MAPPER: PG Query error for : $query" . pg_result_error($qresult));
                
                $pgE = pg_fetch_object($qresult);
                $layerExt = ms_newRectObj();
                $layerExt->setextent($pgE->minx, $pgE->miny, $pgE->maxx, $pgE->maxy); 
                       
            } else {
                $layerExt = $this->layer->getExtent();
                pm_logDebug(3, $this->layer->type, "pmap layerInfo");
            }
        
        // Raster layers (no extent function available, so take map extent) 
        } else {
            $layerExt = $this->map->extent;
        }
        
        // if layer projection != map projection, reproject layer extent
        if ($inMapProjection) {
            $mapProjStr = $this->map->getProjection();
            $layerProjStr = $this->layer->getProjection();
        
            if ($mapProjStr && $layerProjStr && $mapProjStr != $layerProjStr) {
                $mapProjObj = ms_newprojectionobj($mapProjStr);
                $layerProjObj = ms_newprojectionobj($layerProjStr);
                $layerExt->project($layerProjObj, $mapProjObj);
            } 
        }
        pm_logDebug(3, $layerExt, "pmap layerExt");
        
        return $layerExt;
    }

}


?>