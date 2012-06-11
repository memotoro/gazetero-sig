<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de objeto de utilidades.
require_once ("./Utilidades.php");
// Recibiendo el nombre del municipio y departamento.
$Mpio=$_POST["Mpio"];
$Depto=$_POST["Depto"];
$Capa="municipio";
// Creación de objetos de utilidades.
$oMpioUtil=new Utilidades;
$oLocalidad=new Utilidades;
// Obtener las coordenadas por consulta espcial del municipio dado.
$CoordBox=$oMpioUtil->BuscarCoordsGid($Capa,$Mpio);
// Validación de la respuesta de coordenadas.
if (!$CoordBox) { 
	echo "<b>ERROR DE BUSQUEDA</b>"; 
	exit; 
}
// Valida si retorno al menos una fila de coordenadas.
$filas=pg_numrows($CoordBox); 
if ($filas==0) { 
?>   
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar3.submit();	
	alert("NO EXISTE MUNICIPIO SELECCIONADO");		
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
$coords=$oMpioUtil->AdecuarCoordenadas($campo1);
// Obtener las localidades que estan contenidas en el municipio seleccionado.
$varExecLocalidad=$oLocalidad->ObtenerCruceLocalidades($Mpio);
if($varExecLocalidad!=false){
	$filas=pg_numrows($varExecLocalidad); 
	if ($filas==0) { 
?>   
<script language="JavaScript">  
alert("NO EXISTEN LOCALIDADES EN EL MUNICIPIO SELECCIONADO");
</script> 
<?php		
	}else{   
?>
<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="form" name="form" method="post" action="./DespliegueLocalidad.php" target="navegacion4">
	<table width="180" border="0" align="center">   
    <tr>
		<td width="180">Selecicone la Localidad</td>
	</tr>
	<tr>
		<td width="144">
			<select name="Local" id="Local" class="select" style="width:200px;">
			<?php  
			// Bucle para desplegar el nombre de las localidades del municipio.
			while($fetchArrayBM = pg_fetch_array($varExecLocalidad)){?>
				<option value="<?php echo $fetchArrayBM['gid'];?>"><?php echo $fetchArrayBM['nombre'];?></option>
			<?php
			}		   
			?>  
			</select>
		</td>
    </tr>
    <tr>
		<td colspan="2">
        <div align="center">
			<input type="submit" name="Visualizar" id="Visualizar" value="Visualizar">
        </div>
		</td>
    </tr>
  </table>  
</form>
<?php		
	}   
}		  
?>
<form action="../../map.phtml" method="post" name="formMap" target="dinamico" class="None" id="formMap" style="visibility:hidden">
	<table width="761" border="0" align="center">
    <tr>
		<td width="128"><input type="submit" name="Buscar" id="Buscar" value="Buscar"/> </td>
		<td width="128"><input type="hidden" name="coords" id="coords" value="<?php echo $coords ?>"/></td>
		<td width="217"><input type="hidden" name="gid" id="gid" value="<?php echo $gid ?>"/></td>
		<td width="217"><input type="hidden" name="capa" id="capa" value="MUNICIPIO"/></td>		
    </tr>
	</table>
</form>
<script language="JavaScript">  
	document.formMap.submit();
</script>