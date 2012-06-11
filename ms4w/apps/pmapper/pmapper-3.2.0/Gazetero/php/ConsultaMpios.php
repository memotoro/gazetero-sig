<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de Objetos de consulta y utilidades.
require_once ("./DeptoMpioLocalDAO.php");
require_once ("./Utilidades.php");
// Recepción de datos de departamento.
$Depto=$_POST["Depto"]; 
$Capa="departamento";
// Creación de objetos.
$oMpio=new DeptoMpioLocalDAO;
$oDeptoUtil=new Utilidades;
// Obtención de coordenadas para el departamento.
$CoordBox=$oDeptoUtil->BuscarCoordsGid($Capa,$Depto);
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
alert("NO EXISTE DEPARTAMENTO EN LA BASE DE DATOS GEOGRAFICA");
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
$coords=$oDeptoUtil->AdecuarCoordenadas($campo1);
// Consultar los municipios dado un departamento.
$varExecMpio=$oMpio->ConsultarMpio($Depto);
if($varExecMpio!=false){
?>
<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="form" name="form" method="post" action="./ConsultaLocal.php" target="navegacion3">
	<table width="180" border="0" align="center">  
    <tr>
		<td width="180">Selecicone el Municipio</td>
	</tr>
	<tr>
		<td width="180">
			<select name="Mpio" id="Mpio" class="select" style="width:150px;">
			<?php  
			// Despliega el nombre de los diferentes municipios del departamento.
			while($fetchArrayBM = pg_fetch_array($varExecMpio)){?>
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
			<input type="submit" name="Buscar" id="Buscar" value="Buscar Localidades">
        </div>
		</td>
    </tr>
	<tr>
		<td><input type="hidden" name="Depto" id="Depto" value="<?php echo $Depto;?>"/></td>			
	</tr>	
  </table>  
</form>
<?php		   
}		  
?>
<form action="../../map.phtml" method="post" name="formMap" target="dinamico" class="None" id="formMap" style="visibility:hidden">
	<table width="761" border="0" align="center">
    <tr>
		<td width="128"><input type="submit" name="Buscar" id="Buscar" value="Buscar"/> </td>
		<td width="128"><input type="hidden" name="coords" id="coords" value="<?php echo $coords ?>"/></td>
		<td width="217"><input type="hidden" name="gid" id="gid" value="<?php echo $gid ?>"/></td>
		<td width="217"><input type="hidden" name="capa" id="capa" value="DEPARTAMENTO"/></td>		
    </tr>
	</table>
</form>
<script language="JavaScript">  
	document.formMap.submit();		
</script>
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar3.submit();		
</script>