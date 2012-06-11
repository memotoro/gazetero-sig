<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de objeto de utilidades.
require_once ("./Utilidades.php");
// Recepción de la localidad.
$Local=$_POST["Local"];    
$Capa="localidad";
// Creación del objeto de utilidad.
$oLocalUtil=new Utilidades;
// Busqueda de coordenadas para la localidad dada.
$CoordBox=$oLocalUtil->BuscarCoordsGid($Capa,$Local);
// Validación de la respuesta de coordenadas.
if (!$CoordBox) { 
	echo "<b>ERROR DE BUSQUEDA</b>"; 
	exit; 
}
// Valida si retorno al menos una fila de coordenadas.
$filas=pg_numrows($CoordBox); 
if ($filas==0) { 
?>   
<script language="JavaScript">  
alert("NO EXISTE LOCALIDAD EN LA BASE DE DATOS GEOGRÁFICA");
</script> 
<?php    
} 
else {
	// Bucle para obtener los valores del objeto de consulta devuleto de la base de datos.
	for($cont=0;$cont<$filas;$cont++) { 
		$campo1=pg_result($CoordBox,$cont,0);
		$campo2=pg_result($CoordBox,$cont,1);
}}
pg_FreeResult($CoordBox);
$gid=$campo2;
// Adecuación de coordenadas para hacer el zoom extent con formato de pmapper.
$coords=$oLocalUtil->AdecuarCoordenadas($campo1);
?>
<form action="../../map.phtml" method="post" name="formMap" target="dinamico" class="None" id="formMap" style="visibility:hidden">
  <table width="761" border="0" align="center">
    <tr>
	  <td width="128"><input type="submit" name="Buscar" id="Buscar" value="Buscar"/> </td>
      <td width="128"><input type="hidden" name="coords" id="coords" value="<?php echo $coords ?>"/></td>
      <td width="217"><input type="hidden" name="gid" id="gid" value="<?php echo $gid ?>"/></td>
      <td width="217"><input type="hidden" name="capa" id="capa" value="LOCALIDAD"/></td>	  	  
    </tr>
  </table>
<script language="JavaScript">  
	document.formMap.submit();
</script>
</form>