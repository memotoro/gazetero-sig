<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de Clase de consulta.
require_once ("./DeptoMpioLocalDAO.php");
// Creación del objeto de consulta.
$oDepto= new DeptoMpioLocalDAO;
$varExecDepto=$oDepto->ConsultarDepto();
// Validación si la consulta arrojo resultados.
if($varExecDepto!=false){
?>
<link rel="stylesheet" href="../../templates/default.css" type="text/css"/>
<form id="form" name="form" method="post" action="./ConsultaMpios.php" target="navegacion2">
	<table width="190px" border="0" align="center">
    <tr>
		<td width="170" align="center">Seleccione Departamento</td>
	</tr>  
	<tr>
		<td width="175">
			<select name="Depto" id="Depto" class="select" style="width:150px;">
			<?php
			// Bucle para desplegar los diferentes nombres de los departamentos.
			while($fetchArrayBNPD = pg_fetch_array($varExecDepto)){?>
				<option value="<?php echo $fetchArrayBNPD['gid'];?>"><?php echo $fetchArrayBNPD['nombre'];?></option>
			<?php		   
			}	  
			?>
			</select>
		</td>
	</tr>
    <tr>
	<td colspan="2">
        <div align="center">
          <input type="submit" name="Buscar" id="Buscar" value="Buscar Municipios"/>
        </div>
    </td>
	</tr> 
  </table>
</form>
<?php		   
}		  
?>
<form action="./LimpiarFrames" method="post" name="formLimpiar2" id="formLimpiar2" style="visibility:hidden" target="navegacion2">
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar2.submit();
	document.formLimpiar3.submit();		
</script>