<?php
	/*
	Copyright (C) 2013  John F. Arroyave Gutiérrez
						unix4you2@gmail.com

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/

			/*
				Title: Modulo tablas
				Ubicacion *[/core/tablas.php]*.  Archivo de funciones relacionadas con la administracion de tablas de la aplicacion.
			*/
?>
<?php
			/*
				Section: Operaciones basicas de administracion
				Funciones asociadas al mantenimiento de tablas en el sistema.
			*/
?>



<?php
/* ################################################################## */
/* ################################################################## */
/*
	Function: eliminar_campo
	Elimina un campo de una tabla de datos durante su proceso de edicion

	Variables de entrada:

		nombre_tabla - Nombre de la tabla sobre la cual sera eliminado el campo
		nombre_campo - Nombre del campo a eliminar

		(start code)
			ALTER TABLE $nombre_tabla DROP COLUMN $nombre_campo
		(end)

	Salida:
		Campo eliminado de la tabla.
		
	Ver tambien:
		<editar_tabla> | <guardar_crear_campo>
*/
	if ($accion=="eliminar_campo")
		{ 
			$mensaje_error="";
			
			// Busca si existen formularios utilizando el campo de la tabla antes de ser eliminado
			//$consulta = "SELECT * FROM ".$TablasCore."formulario_objeto WHERE id='$formulario'";
			//$resultado = mysql_query($consulta,$mysql_enlace);
			//$registro = mysql_fetch_array($resultado);

			if ($mensaje_error=="")
				{
					// Realiza la operacion
					ejecutar_sql_unaria("ALTER TABLE $nombre_tabla DROP COLUMN $nombre_campo");
					auditar("Elimina campo $nombre_campo de tabla $nombre_tabla");
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
					<input type="Hidden" name="accion" value="editar_tabla">
					<input type="Hidden" name="nombre_tabla" value="'.$nombre_tabla.'">
					</form>
							<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
			else
				{
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
						<input type="Hidden" name="accion" value="editar_tabla">
						<input type="Hidden" name="nombre_tabla" value="'.$nombre_tabla.'">
						<input type="Hidden" name="error_titulo" value="Problema de integridad en dise&ntilde;o">
						<input type="Hidden" name="error_descripcion" value="'.$mensaje_error.'">
						</form>
						<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
		}


/* ################################################################## */
/* ################################################################## */
/*
	Function: guardar_crear_campo
	Agrega un nuevo campo a una tabla de datos durante su proceso de edicion

	Variables de entrada:

		nombre_tabla - Nombre de la tabla sobre la cual sera agregado el campo
		nombre_campo - Nombre del nuevo campo
		tipo - Tipo de dato asociado al campo

		(start code)
			ALTER TABLE $nombre_tabla ADD COLUMN $nombre_campo $tipo
		(end)

	Salida:
		Nuevo campo creado en la tabla.  Algunos parametros adicionales pueden ser usados durante el proceso de creacion del campo.
		
	Ver tambien:
		<editar_tabla>
*/
	if ($accion=="guardar_crear_campo")
		{
			// Construye la consulta para la creacion del campo (sintaxis mysql por ahora)
			$consulta = "ALTER TABLE $nombre_tabla ADD COLUMN $nombre_campo $tipo";
			if ($longitud!="")
				$consulta .= "($longitud) ";
			$consulta .= " $valores_nulos ";
			if ($predeterminado!="")
				if ($predeterminado!="USER_DEFINED")
					$consulta .= " DEFAULT $predeterminado ";
				else
					$consulta .= " DEFAULT $predeterminado_valor ";
			$consulta .= "$autoincremento ";
			if ($despues_campo!="")
				$consulta .= " AFTER $despues_campo ";

			// Realiza la operacion
			ejecutar_sql_unaria($consulta);

			$ultimo_error=$ConexionPDO->errorInfo();
			$descripcion_ultimo_error=$ultimo_error[2];
			if ($descripcion_ultimo_error!="")
				{
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
						<input type="Hidden" name="accion" value="editar_tabla">
						<input type="Hidden" name="nombre_tabla" value="'.$nombre_tabla.'">
						<input type="Hidden" name="error_titulo" value="ERROR DE BASE DE DATOS">
						<input type="Hidden" name="error_descripcion" value="Durante la ejecucion el motor ha retornado lo siguiente: <i>'.$descripcion_ultimo_error.'</i>">
						</form>
							<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
			else
				{
					auditar("Agrega campo $nombre_campo tipo $tipo a tabla $nombre_tabla");
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
						<input type="Hidden" name="accion" value="editar_tabla">
						<input type="Hidden" name="nombre_tabla" value="'.$nombre_tabla.'">
						</form>
							<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
		}


/* ################################################################## */
/* ################################################################## */
/*
	Function: editar_tabla
	Permite agregar o eliminar campos en una tabla de datos de aplicacion mediante sentencias ALTER despues de haber consultado su estructura

	Variables de entrada:

		nombre_tabla - Nombre de la tabla a ser editada

		(start code)
			DESCRIBE $nombre_tabla
		(end)

	Salida:
		Tabla con sus campos editados

	Ver tambien:
		<asistente_tablas>
*/
if ($accion=="editar_tabla")
	{
		 ?>

		<table class="TextosVentana"><tr><td valign=top>
						
			<?php abrir_ventana('Agregar campos en la tabla de datos','f2f2f2',''); ?>
			<form name="datos" id="datos" action="<?php echo $ArchivoCORE; ?>" method="POST">
			<input type="Hidden" name="accion" value="guardar_crear_campo">
			<input type="Hidden" name="nombre_tabla" value="<?php echo $nombre_tabla; ?>">
			<div align=center>
						
			<br>Agregar un campo a la tabla <b><?php echo $nombre_tabla; ?></b>:
				<table class="TextosVentana">
					<tr>
						<td align="right">Nombre:</td>
						<td><input type="text" name="nombre_campo" size="20" class="CampoTexto">
						<a href="#" title="Campo obligatorio" name=""><img src="img/icn_12.gif" border=0></a>
						<a href="#" title="Ayuda de formato para nombre del campo:" name="Nombre del campo sin guiones, puntos, espacios o caracteres especiales."><img src="img/icn_10.gif" border=0></a>	</td>
					</tr>
					<tr>
						<td align="right">Tipo:</td>
						<td>
							<select  name="tipo" class="Combos" >
								<option value="INT">Entero</option>
								<option value="VARCHAR">Cadena (longitud Hasta 255)</option>
								<option value="TEXT">Texto (Ilimitado)</option>
								<option value="DATE">Fecha (sin hora)</option>
							<!--
							 OPCIONES PREVIAS: SOLO MYSQL
								<option value="INT">Entero</option><option value="VARCHAR">Cadena (Hasta 255)</option><option value="TEXT">Texto (Ilimitado)</option><option value="DATE">Fecha (sin hora)</option><optgroup label="Tipos numericos comunes"><option value="TINYINT">TINYINT</option><option value="SMALLINT">SMALLINT</option><option value="MEDIUMINT">MEDIUMINT</option><option value="INT">INT</option><option value="BIGINT">BIGINT</option><option value="-">-</option><option value="DECIMAL">DECIMAL</option><option value="FLOAT">FLOAT</option><option value="DOUBLE">DOUBLE</option><option value="REAL">REAL</option><option value="-">-</option><option value="BIT">BIT</option><option value="BOOLEAN">BOOLEAN</option><option value="SERIAL">SERIAL</option></optgroup><optgroup label="Tipos de Fecha y Hora"><option value="DATE">DATE</option><option value="DATETIME">DATETIME</option><option value="TIMESTAMP">TIMESTAMP</option><option value="TIME">TIME</option><option value="YEAR">YEAR</option></optgroup><optgroup label="Tipos de cadenas de caracteres"><option value="CHAR">CHAR</option><option value="VARCHAR">VARCHAR</option><option value="-">-</option><option value="TINYTEXT">TINYTEXT</option><option value="TEXT">TEXT</option><option value="MEDIUMTEXT">MEDIUMTEXT</option><option value="LONGTEXT">LONGTEXT</option><option value="-">-</option><option value="BINARY">BINARY</option><option value="VARBINARY">VARBINARY</option><option value="-">-</option><option value="TINYBLOB">TINYBLOB</option><option value="MEDIUMBLOB">MEDIUMBLOB</option><option value="BLOB">BLOB</option><option value="LONGBLOB">LONGBLOB</option><option value="-">-</option><option value="ENUM">ENUM</option><option value="SET">SET</option></optgroup><optgroup label="Tipos espaciales"><option value="GEOMETRY">GEOMETRY</option><option value="POINT">POINT</option><option value="LINESTRING">LINESTRING</option><option value="POLYGON">POLYGON</option><option value="MULTIPOINT">MULTIPOINT</option><option value="MULTILINESTRING">MULTILINESTRING</option><option value="MULTIPOLYGON">MULTIPOLYGON</option><option value="GEOMETRYCOLLECTION">GEOMETRYCOLLECTION</option></optgroup>
							-->
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Longitud (Si aplica):</td>
						<td><input type="text" name="longitud" size="10" class="CampoTexto">
						<a href="#" title="Cuidado" name="Este campo puede ser de car&aacute;cter obligatorio dependiendo del tipo de dato a ser almacenado, ejemplo campos tipo Cadena"><img src="img/icn_12.gif" border=0></a>
						<a href="#" title="Ayuda de formato:" name="Si alguna vez necesita poner una barra invertida (backslash) o una comilla simple entre esos valores, siempre ponga una barra invertida adicional (backslash).  Para campos enum o set, use el formato: 'a','b','c'..."><img src="img/icn_10.gif" border=0></a>	
						</td>
					</tr>
					<tr>
						<td align="right">Autoincremento:</td>
						<td>
							<select  name="autoincremento" class="Combos" >
								<option value="AUTO_INCREMENT">Si</option>
								<option value="" selected>No</option>
							</select>
							<a href="#" title="Alerta de clave primaria" name="Este valor puede ser definido solamente por administradores avanzados que han suprimido por alg&uacute;n motivo el autoincremento del campo Id predeterminado."><img src="img/icn_12.gif" border=0></a>
						</td>
					</tr>
					<tr>
						<td align="right">Permitir valores nulos?:</td>
						<td>
							<select  name="valores_nulos" class="Combos" >
								<option value="">Si</option>
								<option value="NOT NULL">No</option>
							</select>
						</td>
					</tr>
					<tr>
						<td valign=top align="right">Valor predeterminado:</td>
						<td>
							<select name="predeterminado">
								<option value="" >Ninguno</option>
								<option value="USER_DEFINED" >Definido por el usuario:</option>
								<option value="NULL" >Nulo</option>
								<option value="CURRENT_TIMESTAMP" >Fecha y hora actual</option>
							</select><br>
							<input type="text" name="predeterminado_valor" size="20" class="CampoTexto">
							<a href="#" title="Ayuda de formato:" name="S&oacute;lo un valor, sin caracteres de escape.  Para cadenas de caracteres utilice comillas simples al principio y al final"><img src="img/icn_10.gif" border=0></a>	
						</td>
					</tr>
					<tr>
						<td align="right">Agregar el campo:</td>
						<td>
							<select name="despues_campo">
								<option value="" >Al final de todo</option>
								<?php
									$resultado=ejecutar_sql("DESCRIBE $nombre_tabla ");
									while($registro = $resultado->fetch())
										{
											echo '<option value="'.$registro["Field"].'" >Despu&eacute;s de '.$registro["Field"].'</option>';
										}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							</form>
						</td>
						<td>
							<input type="Button"  class="Botones" value="Agregar campo" onClick="document.datos.submit()">
							&nbsp;&nbsp;<input type="Button" onclick="document.core_ver_menu.submit()" value="Volver al menu" class="Botones">
						</td>
					</tr>
				</table>


		<?php
		cerrar_ventana();
		echo '</td><td valign=top>';  // Inicia segunda columna del diseñador
		abrir_ventana('Campos ya definidos en la tabla','f2f2f2','');
		?>
				<table width="100%" border="0" cellspacing="5" align="CENTER" style="color: black; font-size: 9px; font-family: Verdana, Tahoma, Arial;">
					<tr>
						<td bgcolor="#d6d6d6"><b>Campo</b></td>
						<td bgcolor="#D6D6D6"><b>Tipo</b></td>
						<td bgcolor="#d6d6d6"><b>Permite Nulos</b></td>
						<td bgcolor="#d6d6d6"><b>Tipo clave</b></td>
						<td bgcolor="#d6d6d6"><b>Predeterminado</b></td>
						<td bgcolor="#d6d6d6"><b>Extras</b></td>
						<td></td>
						<td></td>
					</tr>
		 <?php
				$resultado=ejecutar_sql("DESCRIBE $nombre_tabla ");
				while($registro = $resultado->fetch())
					{
						$imagen="";
						if ($registro["Key"] != "") $imagen=" <img src='img/key.gif' border=0 align=absmiddle>";
						echo '<tr>
								<td><b>'.$registro["Field"].$imagen.'</b></td>
								<td>'.$registro["Type"].'</td>
								<td>'.$registro["Null"].'</td>
								<td>'.$registro["Key"].'</td>
								<td>'.$registro["Default"].'</td>
								<td>'.$registro["Extra"].'</td>';
						// Permite eliminar aquellos campos diferentes al Id
						if ($registro["Field"] != "id")
							echo '
								<td align="center">
										<form action="'.$ArchivoCORE.'" method="POST" name="f'.$registro["Field"].'" id="f'.$registro["Field"].'">
												<input type="hidden" name="accion" value="eliminar_campo">
												<input type="hidden" name="nombre_tabla" value="'.$nombre_tabla.'">
												<input type="hidden" name="nombre_campo" value="'.$registro["Field"].'">
												<input type="button" value="Eliminar"  class="BotonesCuidado" onClick="confirmar_evento(\'IMPORTANTE:  Al eliminar la columna '.$registro["Field"].' de la tabla se eliminar&aacute;n tambi&eacute;n todos los datos en ella almacenados y luego no podr&aacute; deshacer esta operaci&oacute;n.\nEst&aacute; seguro que desea continuar ?\',f'.$registro["Field"].');">
												&nbsp;&nbsp;
										</form>
								</td>
								<td align="center">
										<form action="'.$ArchivoCORE.'" method="POST">
												<input type="hidden" name="accion" value="editar_tabla">
												<input type="hidden" name="nombre_tabla" value="'.@$registro["Name"].'">
												<input type="Button" value="Editar (deshabilitado)"  class="Botones">
												&nbsp;&nbsp;
										</form>
								</td>';
						else
							echo '
								<td align="center">
								</td>
								<td align="center">
								No Puede Eliminarse
								</td>';

						echo '	</tr>';

					}
				echo '</table>';
		?>
				
			</div>
<?php
			cerrar_ventana();
		echo '</td></tr></table>'; // Cierra la tabla de dos columnas
					
	}



/* ################################################################## */
/* ################################################################## */
/*
	Function: eliminar_tabla
	Elimina una tabla de aplicacion

	Variables de entrada:

		nombre_tabla - Nombre de la tabla a ser eliminada

		(start code)
			DROP TABLE $nombre_tabla
		(end)

	Salida:
		Tabla eliminada

	Ver tambien:
		<administrar_tablas>
*/
	if ($accion=="eliminar_tabla")
		{
			$mensaje_error="";
			if ($mensaje_error=="")
				{
					// Realiza la operacion
					ejecutar_sql_unaria("DROP TABLE $nombre_tabla");
					auditar("Elimina tabla $nombre_tabla");
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST"><input type="Hidden" name="accion" value="administrar_tablas"></form>
							<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
			else
				{
					mensaje('<blink>Error eliminando tabla de datos!</blink>','La tabla especificada no se puede eliminar.  Algunas causas comunes son:<br><li>La es utilizada por alguno de los formularios o informes autom&aacute;ticos, en ese caso puede intentar editarla.<br><li>La tabla cuenta con relaciones definidas por el dise&ntilde;ador hacia otras tablas de datos.<br><li>El rol de usuario definido para el usuario con sesi&oacute;n activa no permite eliminar objetos en DynApps.','60%','icono_error.png','TextosEscritorio');
					echo '<form action="'.$ArchivoCORE.'" method="POST" name="cancelar"><input type="Hidden" name="accion" value="administrar_tablas"></form>
						<br /><input type="Button" onclick="document.cancelar.submit()" name="" value="Cerrar" class="Botones">';
				}
		}



/* ################################################################## */
/* ################################################################## */
/*
	Function: guardar_crear_tabla
	Crea una nueva tabla de aplicacion

	Variables de entrada:

		nombre_tabla - Nombre de la tabla a ser creada

		(start code)
			CREATE TABLE ".$TablasApp."$nombre_tabla (id int(11) AUTO_INCREMENT,PRIMARY KEY  (id))
		(end)

	Salida:
		Nueva tabla creada con el prefijo de aplicacion y nombre especificado

	Ver tambien:
		<administrar_tablas>
*/
	if ($accion=="guardar_crear_tabla")
		{
			$mensaje_error="";
			if ($nombre_tabla=="") $mensaje_error="Debe indicar un nombre v&aacute;lido para la tabla.  Este no debe contener guiones, puntos, espacios o caracteres especiales.";
			if ($mensaje_error=="")
				{
					// Crea la tabla temporal
					$error_consulta=ejecutar_sql_unaria("CREATE TABLE ".$TablasApp."$nombre_tabla (id int(11) AUTO_INCREMENT,PRIMARY KEY  (id))");
					if ($error_consulta!="")
						{
							echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
								<input type="Hidden" name="accion" value="administrar_tablas">
								<input type="Hidden" name="error_titulo" value="ERROR DE BASE DE DATOS">
								<input type="Hidden" name="error_descripcion" value="Durante la ejecucion el motor ha retornado lo siguiente: <i>'.$error_mysql.'</i>">
								</form>
									<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
						}
					else
						{
							auditar("Crea tabla $nombre_tabla");
							echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST"><input type="Hidden" name="accion" value="administrar_tablas"></form>
									<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
						}
				}
			else
				{
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
						<input type="Hidden" name="accion" value="administrar_tablas">
						<input type="Hidden" name="error_titulo" value="Problema de integridad de consultas">
						<input type="Hidden" name="error_descripcion" value="'.$mensaje_error.'">
						</form>
						<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
		}



/* ################################################################## */
/* ################################################################## */
/*
	Function: administrar_tablas
	Detecta las tablas existentes en la base de datos enlazada y permite realizar operaciones basicas con ellas, asi como la creacion de nuevas tablas de aplicacion.  Esta funciona hace uso de la funcion generalizada <consultar_tablas>

	Ver tambien:
		<asistente_tablas> | <consultar_tablas>
*/
	if ($accion=="administrar_tablas")
		{
			echo "<a href='javascript:abrir_ventana_popup(\"http://www.youtube.com/embed/otODPESW0k0\",\"VideoTutorial\",\"toolbar=no, location=no, directories=no, status=no, menubar=no ,scrollbars=no, resizable=yes, fullscreen=no, width=640, height=480\");'><img src='img/icono_screencast.png' alt='ScreenCast-VideoTutorial'></a>";

			abrir_ventana('Crear/Listar tablas de datos definidias en el sistema','f2f2f2',''); ?>
			<form name="datos" id="datos" action="<?php echo $ArchivoCORE; ?>" method="POST">
			<input type="Hidden" name="accion" value="guardar_crear_tabla">
			<div align=center>
			<br>Crear una nueva tabla de datos en <b><?php echo $BaseDatos; ?></b>:
			<table class="TextosVentana" cellspacing=10>
			</tr>
			<td>
				<table class="TextosVentana">
					<tr>
						<td align="center">Nombre:</td>
						<td><?php echo $TablasApp; ?><input type="text" name="nombre_tabla" size="20" class="CampoTexto">
						<a href="#" title="Campo obligatorio" name=""><img src="img/icn_12.gif" border=0></a>
						<a href="#" title="Ayuda general de tablas" name="Una tabla de datos es una estrctura que le permite almacenar informaci&oacute;n. Ingrese en este espacio el nombre de la tabla sin guiones, puntos, espacios o caracteres especiales. SENSIBLE A MAYUSCULAS"><img src="img/icn_10.gif" border=0></a></td>
					</tr>
					<tr>
						<td>
							</form>
						</td>
						<td>
							<input type="Button"  class="Botones" value="Crear tabla y definir campos" onClick="document.datos.submit()">
							&nbsp;&nbsp;<input type="Button" onclick="document.core_ver_menu.submit()" value="Volver al menu" class="Botones">
						</td>
					</tr>
				</table>
			</td>
			<td width=50>
			</td>
			<td align=center>
				<form name="datosasis" id="datosasis" action="<?php echo $ArchivoCORE; ?>" method="POST">
				<input type="Hidden" name="accion" value="asistente_tablas">
				Asistente<br>
				<a href="#" title="Utilizar asistente?" name="Permite seleccionar desde algunas tablas comunes predefinidas"><input type="image" src="img/asistente.png"></a>
				</form>
			</td>
			<tr>
			</table>
			<hr>

		<font face="" size="3" color="Navy"><b>Tablas definidas en la base de datos</b></font><br><br>
				<table width="100%" border="0" cellspacing="5" align="CENTER"  class="TextosVentana" >
					<tr>
						<td bgcolor="#d6d6d6"><b>Nombre</b></td>
						<td bgcolor="#d6d6d6"><b>Registros</b></td>
						<td></td>
						<td></td>
					</tr>


		<?php
			$resultado=consultar_tablas();

			while ($registro = $resultado->fetch())
				{
					$total_registros=ContarRegistros($registro["0"]);

					if (strpos($registro[0],$TablasCore)!==FALSE) // Booleana requiere === o !==
						{
							$PrefijoRegistro=$TablasCore;
						}
					else
						{
							$PrefijoRegistro=$TablasApp;
						}

						echo '<tr>
								<td><font color=gray>'.$PrefijoRegistro.'</font><b><font size=2>'.str_replace($PrefijoRegistro,'',$registro[0]).'</font></b></td>
								<td>'.$total_registros.'</td>
								<td align="center">';

					if ($PrefijoRegistro!=$TablasCore && $total_registros==0)							
						echo '
										<form action="'.$ArchivoCORE.'" method="POST" name="f'.$registro["0"].'" id="f'.$registro["0"].'">
												<input type="hidden" name="accion" value="eliminar_tabla">
												<input type="hidden" name="nombre_tabla" value="'.$registro["0"].'">
												<input type="button" value="Eliminar"  class="BotonesCuidado" onClick="confirmar_evento(\'IMPORTANTE:  Al eliminar la tabla de datos '.$registro["0"].' se eliminar&aacute;n tambi&eacute;n todos los registros en ella almacenados y luego no podr&aacute; deshacer esta operaci&oacute;n.\nEst&aacute; seguro que desea continuar ?\',f'.$registro["0"].');">
												&nbsp;&nbsp;
										</form>';
						echo '
								</td>
								<td align="center">';
					if ($PrefijoRegistro!=$TablasCore)
						echo '
										<form action="'.$ArchivoCORE.'" method="POST">
												<input type="hidden" name="accion" value="editar_tabla">
												<input type="hidden" name="nombre_tabla" value="'.$registro["0"].'">
												<input type="Submit" value="Editar"  class="Botones">
												&nbsp;&nbsp;
										</form>';
						echo '
								</td>
							</tr>';
				}
				echo '</table>';	
			cerrar_ventana();
	}



/* ################################################################## */
/* ################################################################## */
/*
	Function: guardar_crear_tabla_asistente
	Genera una nueva tabla a partir de la plantilla seleccionada en el asistente

	Variables de entrada:

		nombre_tabla - Nombre de la tabla a ser creada

		(start code)
			CREATE TABLE ".$TablasApp."$nombre_tabla (id int(11) AUTO_INCREMENT,PRIMARY KEY  (id))
			Repetir por cada campo en la definicion
				ALTER TABLE ".$TablasApp."$nombre_tabla ADD COLUMN $nombre_campo $tipo
		(end)

	Salida:
		Nueva tabla creada con el prefijo de aplicacion y nombre especificado

	Ver tambien:
		<asistente_tablas>
*/
	if ($accion=="guardar_crear_tabla_asistente")
		{
			$mensaje_error="";
			if ($nombre_tabla=="") $mensaje_error="Debe indicar un nombre v&aacute;lido para la tabla.  Este no debe contener guiones, puntos, espacios o caracteres especiales.";
			if ($plantilla_tabla=="") $mensaje_error.="<br>Debe seleccionar una plantilla desde la cual desea crear su nueva tabla.";
			if ($mensaje_error=="")
				{
					// Crea la tabla temporal
					$error_consulta=ejecutar_sql_unaria("CREATE TABLE ".$TablasApp."$nombre_tabla (id int(11) AUTO_INCREMENT,PRIMARY KEY  (id))");
					if ($error_consulta!="")
						{
							echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
								<input type="Hidden" name="accion" value="asistente_tablas">
								<input type="Hidden" name="error_titulo" value="ERROR DE BASE DE DATOS">
								<input type="Hidden" name="error_descripcion" value="Durante la ejecucion el motor ha retornado: <i>'.$error_mysql.'</i>">
								</form>
									<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
						}
					else
						{
							//Busca los campos en la plantilla
							$directorio="inc/practico/asistentes/";
							//Carga el archivo de plantilla
							$archivo_origen=$directorio.$plantilla_tabla;
							$archivo = fopen($archivo_origen, "r");
							if ($archivo)
								{
									$evitar_linea = fgets($archivo, 8192); //nombre
									$NombreTabla= fgets($archivo, 8192);
									$evitar_linea = fgets($archivo, 8192); //descripcion tabla
									$DescripcionTabla= fgets($archivo, 8192);
									$evitar_linea = fgets($archivo, 8192); //campos
									while (!feof($archivo))
										{
											$linea = fgets($archivo, 8192);
											$campos = explode("|", $linea);
											// Verifica si el campo de texto no es vacio y lo agrega
											if (strlen($linea)>5)
												{
													$nombre_campo=$campos[1];
													$tipo=$campos[2];
													$longitud=$campos[3];
													// Construye la consulta para la creacion del campo (sintaxis mysql por ahora)
													$consulta = "ALTER TABLE ".$TablasApp."$nombre_tabla ADD COLUMN $nombre_campo $tipo";
													if ($longitud!="")
														$consulta .= "($longitud) ";
													// Realiza la operacion
													ejecutar_sql_unaria($consulta);
												}
										}
									fclose($archivo);
								}
							auditar("Crea tabla $nombre_tabla");
							echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
							<input type="Hidden" name="accion" value="editar_tabla">
							<input type="hidden" name="nombre_tabla" value="'.$TablasApp.''.$nombre_tabla.'">
							</form>
									<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
						}
				}
			else
				{
					echo '<form name="cancelar" action="'.$ArchivoCORE.'" method="POST">
						<input type="Hidden" name="accion" value="asistente_tablas">
						<input type="Hidden" name="error_titulo" value="Problema de integridad de consultas">
						<input type="Hidden" name="error_descripcion" value="'.$mensaje_error.'">
						</form>
						<script type="" language="JavaScript"> document.cancelar.submit();  </script>';
				}
		}



/* ################################################################## */
/* ################################################################## */
/*
	Function: asistente_tablas
	Despliega el asistente para creacion de tablas basado en los archivos inc/practico/asistentes/*

	Variables de entrada:

		archivos en inc/practico/asistentes/*.txt - Contienen la definicion de tablas que pueden ser creadas con el asistente

	Salida:
		Tabla seleccionada para su creacion
		
	Ver tambien:
		<guardar_crear_tabla_asistente>
*/
	if ($accion=="asistente_tablas")
		{
			abrir_ventana('Asistente para generaci&oacute;n de tablas','f2f2f2',''); ?>
			<form name="datos" id="datos" action="<?php echo $ArchivoCORE; ?>" method="POST">
			<input type="Hidden" name="accion" value="guardar_crear_tabla_asistente">
			<div align=center>
			<br>Crear una nueva tabla de datos en <b><?php echo $BaseDatos; ?></b>:
			<table class="TextosVentana" cellspacing=10>
			</tr>
			<td>
				<table class="TextosVentana">
					<tr>
						<td align="right">Nombre para la nueva tabla:</td>
						<td><?php echo $TablasApp; ?><input type="text" name="nombre_tabla" size="20" class="CampoTexto">
						<a href="#" title="Campo obligatorio" name=""><img src="img/icn_12.gif" border=0></a>
						<a href="#" title="Ayuda general de tablas" name="Una tabla de datos es una estrctura que le permite almacenar informaci&oacute;n. Ingrese en este espacio el nombre de la tabla sin guiones, puntos, espacios o caracteres especiales. SENSIBLE A MAYUSCULAS"><img src="img/icn_10.gif" border=0></a></td>
					</tr>
					<tr>
						<td align="right"><b>Plantilla de tabla seleccionada:</b></td>
						<td>
							<select name="plantilla_tabla" onChange="datos.descripciontabla.value=document.datos.plantilla_tabla.options[document.datos.plantilla_tabla.selectedIndex].label; datos.listacampos.value=document.datos.plantilla_tabla.options[document.datos.plantilla_tabla.selectedIndex].title; datos.totalcampos.value=document.datos.plantilla_tabla.options[document.datos.plantilla_tabla.selectedIndex].lang;">
								<option value="" label="" dir="" lang="">Seleccione una</option>
								<?php
									$directorio="inc/practico/asistentes/";
									$dh = opendir($directorio);
									
									while (($file = readdir($dh)) !== false)
										{
											if (($file != ".") && ($file != "..") && stristr($file,"tbl_")  && !stristr($file,"~"))
												{
													//Carga el archivo de plantilla
													$archivo_origen=$directorio.$file;
													$archivo = fopen($archivo_origen, "r");
													if ($archivo)
														{
															$evitar_linea = fgets($archivo, 8192); //nombre
															$NombreTabla= fgets($archivo, 8192);
															$evitar_linea = fgets($archivo, 8192); //descripcion tabla
															$DescripcionTabla= fgets($archivo, 8192);
															$evitar_linea = fgets($archivo, 8192); //campos
															$conteocampo=0;
															$DescripcionCampos="";
															while (!feof($archivo))
																{
																	$linea = fgets($archivo, 8192);
																	$campos = explode("|", $linea);
																	// Verifica si el campo de texto no es vacio
																	if (strlen($linea)>5)
																		{
																			$conteocampo++;
																			$DescripcionCampos.=$campos[0]."\n";
																		}
																}
															fclose($archivo);
														}
													//Presenta la opcion del combo con los datos del archivo
													echo '<option value="'.$file.'" label="'.$DescripcionTabla.'" title="'.$DescripcionCampos.'" lang="'.$conteocampo.'">'.$NombreTabla.'</option>';
												}
										}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">Descripci&oacute;n:</td>
						<td>
							<textarea name="descripciontabla" id="descripciontabla" cols="78" rows="2" readonly></textarea>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">Campos que contiene:</td>
						<td>
							<textarea name="listacampos" id="listacampos" cols="38" rows="10" readonly></textarea>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">Total campos:</td>
						<td>
							<input type="text" name="totalcampos" size="4" class="CampoTexto">
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">IMPORTANTE:</td>
						<td>
							Todas las tablas y sus campos podr&aacute;n ser personalizados en el siguiente paso,<br> agregando, eliminando o cambiando las propiedades de aquellos que desee.
						</td>
					</tr>
					<tr>
						<td>
							</form>
						</td>
						<td>
							<input type="Button"  class="Botones" value="Crear tabla y revisar campos" onClick="document.datos.submit()">
							&nbsp;&nbsp;<input type="Button" onclick="document.core_ver_menu.submit()" value="Volver al menu" class="Botones">
						</td>
					</tr>
				</table>
			</td>
			<tr>
			</table>
			<hr>

<?php
			cerrar_ventana();
	}
?>



<?php
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* ################################################################## */
/* AQUI EMPIEZA CODIGO DE VERSIONES ANTERIORES ESPECIFICAS PARA MYSQL ------ EN DESUSO-----   */
 if ($accion=="administrar_tablas_solo_mysql")
	{
			abrir_ventana('Crear/Listar tablas de datos definidias en el sistema','f2f2f2','95%'); ?>
			<form name="datos" id="datos" action="<?php echo $ArchivoCORE; ?>" method="POST">
			<input type="Hidden" name="accion" value="guardar_crear_tabla">
			<div align=center>
			<br>Crear una nueva tabla de datos en <b><?php echo $BaseDatos; ?></b>:
				<table class="TextosVentana">
					<tr>
						<td align="center">Nombre:</td>
						<td><?php echo $TablasApp; ?><input type="text" name="nombre_tabla" size="20" class="CampoTexto">
						<a href="#" title="Campo obligatorio" name=""><img src="img/icn_12.gif" border=0></a>
						<a href="#" title="Ayuda general de tablas" name="Una tabla de datos es una estrctura que le permite almacenar informaci&oacute;n. Ingrese en este espacio el nombre de la tabla sin guiones, puntos, espacios o caracteres especiales. SENSIBLE A MAYUSCULAS"><img src="img/icn_10.gif" border=0></a></td>
					</tr>
					<tr>
						<td>
							</form>
						</td>
						<td>
							<input type="Button"  class="Botones" value="Crear tabla y definir campos" onClick="document.datos.submit()">
							&nbsp;&nbsp;<input type="Button" onclick="document.core_ver_menu.submit()" value="Volver al menu" class="Botones">
						</td>
					</tr>
				</table>
		<br><hr>
		<font face="" size="3" color="Navy"><b>Tablas de datos definidas por el dise&ntilde;ador para la aplicaci&oacute;n</b></font><br><br>
				<table width="100%" border="0" cellspacing="5" align="CENTER"  class="TextosVentana" >
					<tr>
						<td bgcolor="#d6d6d6"><b>Nombre</b></td>
						<td bgcolor="#D6D6D6"><b>Motor</b></td>
						<td bgcolor="#d6d6d6"><b>Formato filas</b></td>
						<td bgcolor="#d6d6d6"><b>Registros</b></td>
						<td bgcolor="#d6d6d6"><b>Media de registro</b></td>
						<td bgcolor="#d6d6d6"><b>Tama&ntilde;o</b></td>
						<td bgcolor="#d6d6d6"><b>Auto Incremento</b></td>
						<td bgcolor="#d6d6d6"><b>Creada</b></td>
						<td bgcolor="#d6d6d6"><b>Actualizada</b></td>
						<td bgcolor="#d6d6d6"><b>Set Caracteres</b></td>
						<td></td>
						<td></td>
					</tr>
		 <?php
				// Llamar aqui una equivalencia
		 
				$mysql_enlace = mysql_connect($Servidor, $UsuarioBD, $PasswordBD);
				mysql_select_db($BaseDatos, $mysql_enlace);
				$consulta = "SHOW TABLE STATUS FROM $BaseDatos WHERE Name LIKE '$TablasApp%'";
				$resultado = mysql_query($consulta,$mysql_enlace);
				while($registro = mysql_fetch_array($resultado))
					{
						echo '<tr>
								<td><font color=gray>'.$TablasApp.'</font><b><font size=2>'.str_replace($TablasApp,'',$registro[0]).'</font></b></td>
								<td>'.$registro[1].'</td>
								<td>'.$registro[3].'</td>
								<td>'.$registro[4].'</td>
								<td>'.$registro[5].'</td>
								<td>'.$registro[6].'</td>
								<td>'.$registro[10].'</td>
								<td>'.$registro[11].'</td>
								<td>'.$registro[12].'</td>
								<td>'.$registro[14].'</td>
								<td align="center">
										<form action="'.$ArchivoCORE.'" method="POST" name="f'.$registro["Name"].'" id="f'.$registro["Name"].'">
												<input type="hidden" name="accion" value="eliminar_tabla">
												<input type="hidden" name="nombre_tabla" value="'.$registro["Name"].'">
												<input type="button" value="Eliminar"  class="BotonesCuidado" onClick="confirmar_evento(\'IMPORTANTE:  Al eliminar la tabla de datos '.$registro["Name"].' se eliminar&aacute;n tambi&eacute;n todos los registros en ella almacenados y luego no podr&aacute; deshacer esta operaci&oacute;n.\nEst&aacute; seguro que desea continuar ?\',f'.$registro["Name"].');">
												&nbsp;&nbsp;
										</form>
								</td>
								<td align="center">
										<form action="'.$ArchivoCORE.'" method="POST">
												<input type="hidden" name="accion" value="editar_tabla">
												<input type="hidden" name="nombre_tabla" value="'.$registro["Name"].'">
												<input type="Submit" value="Editar"  class="Botones">
												&nbsp;&nbsp;
										</form>
								</td>
							</tr>';

					}
				echo '</table>';			
		?>

		<br><hr>
		<font face="" size="3" color="darkgray"><b>Tablas de sistema usadas por Practico (solo lectura definido por prefijo)</b></font><br><br>
				<table width="100%" border="0" cellspacing="5" align="CENTER" style="font-size: 9px; font-family: Verdana, Tahoma, Arial; color: gray;">
					<thead>
					<tr>
						<td bgcolor="#d6d6d6" title="Nombre de la tabla"><b>Nombre</b></td>
						<td bgcolor="#D6D6D6"><b>Motor</b></td>
						<td bgcolor="#d6d6d6"><b>Formato filas</b></td>
						<td bgcolor="#d6d6d6"><b>Registros</b></td>
						<td bgcolor="#d6d6d6"><b>Media de registro</b></td>
						<td bgcolor="#d6d6d6"><b>Tama&ntilde;o</b></td>
						<td bgcolor="#d6d6d6"><b>Auto Incremento</b></td>
						<td bgcolor="#d6d6d6"><b>Creada</b></td>
						<td bgcolor="#d6d6d6"><b>Actualizada</b></td>
						<td bgcolor="#d6d6d6"><b>Set Caracteres</b></td>
					</tr>
					</thead>
					<tbody>
		 <?php
				$mysql_enlace = mysql_connect($Servidor, $UsuarioBD, $PasswordBD);
				mysql_select_db($BaseDatos, $mysql_enlace);
				$consulta = "SHOW TABLE STATUS FROM $BaseDatos WHERE Name LIKE '$TablasCore%'";
				$resultado = mysql_query($consulta,$mysql_enlace);
				while($registro = mysql_fetch_array($resultado))
					{
						echo '<tr>
								<td>'.$registro[0].'</td>
								<td>'.$registro[1].'</td>
								<td>'.$registro[3].'</td>
								<td>'.$registro[4].'</td>
								<td>'.$registro[5].'</td>
								<td>'.$registro[6].'</td>
								<td>'.$registro[10].'</td>
								<td>'.$registro[11].'</td>
								<td>'.$registro[12].'</td>
								<td>'.$registro[14].'</td>
							</tr>';

					}
				echo '</tbody></table>
				';
		?>		
				
			</div>
<?php
			cerrar_ventana();
	}
?>







<?php
    function createBackup()
          {
            $key = "Tables_in_".$this->bdconfig["database"];
            $cont = 0;
            $query = "SHOW TABLES";
           
            $peticion = $this->conexion->prepare($query);
           
            $peticion->execute();
         
            while($tables = $peticion->fetch())
            {          
              $table = $tables[$key];
                 
              $backup .= "/***************************
                           Script de la tabla '".$table."'
                          ***************************/
                          CREATE TABLE `".$table."` (";
               
              $query = "SHOW COLUMNS FROM ".$table;
             
              $peticion2 = $this->conexion->prepare($query);
             
              $peticion2->execute();
             
              while($column = $peticion2->fetch())
              {      
                $primary_key;
                             
                if ($column["Null"] == "NO")
                {
                  $null = "NOT NULL";
                }
                else
                {
                  $null = "NULL";
                }
               
                if ($column["Key"] == "PRI")
                {
                  $primary_key = $column["Field"];
                }
               
                $backup .= "`".$column["Field"]."` ".$column["Type"]." DEFAULT '".$column["Default"]."' ".$null." ".$columns["Extra"]." ,
               ";              
              }        
             
              $backup .= "PRIMARY KEY ( `".$primary_key."` ) )
               
             ";
               
              $backup = str_replace("DEFAULT ''", "", $backup);  
               
              // El resto del código para el backup
            }
     
            return $backup;
    }
    
    // REVISAR ADEMAS: http://www.sitepoint.com/forums/php-application-design-147/pdo-getcolumnmeta-bug-497257.html
    
?>

