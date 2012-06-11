<?php

class Projection
{
    function Projection($inX, $inY, $fromPrj, $toPrj)
    {
        $fromPrjObj = ms_newprojectionobj($fromPrj);
        $toPrjObj = ms_newprojectionobj($toPrj);
        $poPoint = ms_newpointobj();
        $poPoint->setXY($inX, $inY);         
        $poPoint->project($fromPrjObj, $toPrjObj);
        
        
        $this->x = $poPoint->x;
        $this->y = $poPoint->y;
    }
    
    function getX()
    {
        return $this->x;
    }
    
    function getY()
    {
        return $this->y;
    }
    
}

?>