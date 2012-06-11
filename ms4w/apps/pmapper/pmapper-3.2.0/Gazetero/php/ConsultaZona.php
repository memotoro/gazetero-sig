<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="formCoord" name="formCoord" method="post" action="./DespliegueZona.php" target="navegacion2">
	<table width="150px" border="0" align="center">
	<tr>
		<td></td>
		<td width="150" align="center">Grados</td>
		<td width="150" align="center">Minutos</td>
		<td width="150" align="center">Segundos</td>
	</tr>	
	<?php // A continuación se presenta todo una serie de bucles que permiten generar los rangos de entrada de datos de latitud y longitudes validas ?>
    <tr>
		<td width="150" align="center">Latitud Mínima</td>
		<td width="150"><select name="latMinGrado" id="latMinGrado" class="select" style="align:center;width:50px;">
						<?php for($i=5;$i>=1;$i--){?>
							<option>-<?php echo $i; ?></option>
						<?php } ?>
						<?php for($i=0;$i<=12;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>	
						</select></td>
		<td width="150"><select name="latMinMmin" id="latMinMmin" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="latMinSeg" id="latMinSeg" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
	</tr>  
    <tr>
		<td width="150" align="center">Longitud Mínima</td>
		<td width="150"><select name="lonMinGrado" id="lonMinGrado" class="select" style="align:center;width:50px;">
						<?php for($i=65;$i<=85;$i++){?>
							<option>-<?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="lonMinMmin" id="lonMinMmin" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="lonMinSeg" id="lonMinSeg" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
	</tr> 	
    <tr>
		<td width="150" align="center">Latitud Máxima</td>
		<td width="150"><select name="latMaxGrado" id="latMaxGrado" class="select" style="align:center;width:50px;">
						<?php for($i=5;$i>=1;$i--){?>
							<option>-<?php echo $i; ?></option>
						<?php } ?>
						<?php for($i=0;$i<=12;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>						
						</select></td>
		<td width="150"><select name="latMaxMmin" id="latMaxMmin" class="select" style="align:center;width:50px;">
						<?php for($i=0;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="latMaxSeg" id="latMaxSeg" class="select" style="align:center;width:50px;">
						<?php for($i=0;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
	</tr>  
    <tr>
		<td width="150" align="center">Longitud Máxima</td>
		<td width="150"><select name="lonMaxGrado" id="lonMaxGrado" class="select" style="align:center;width:50px;">
						<?php for($i=65;$i<=85;$i++){?>
							<option>-<?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="lonMaxMmin" id="lonMaxMmin" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
		<td width="150"><select name="lonMaxSeg" id="lonMaxSeg" class="select" style="align:center;width:50px;">
						<?php for($i=00;$i<=59;$i++){?>
							<option><?php echo $i; ?></option>
						<?php } ?>
						</select></td>
	</tr>       
	<tr>
	<td colspan="4">
        <div align="center">
          <input type="submit" name="Buscar" id="Buscar" value="Buscar Localidades"/>
        </div>
    </td>	
	</tr>
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar2" id="formLimpiar2" style="visibility:hidden" target="navegacion2">
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar2.submit();
	document.formLimpiar3.submit();		
</script>