<?php
#############################################
#
# Tarea de copia seguridad de todas las bases de datos en MySQL
#
# CopyLeft, puedes hacer con este script lo que te de la gana
#
#############################################
date_default_timezone_set('America/La_Paz');
set_time_limit(0);
header("Content-type: text/plain; charset=UTF-8");
//echo date("Y-m-d H:i", time()) . " Creando copia de seguridad MySQL...\r\n";

// Datos de acceso a MySQL
$myhost = "localhost";
$myuser = 'rony';
$mypass = 'ryno';
$DB = mysql_connect($myhost, $myuser, $mypass) or die(date("Y-m-d H:i", time()) . " ERROR!! No se pudo conectar a MySQL.\r\n");

// Otros parámetros
$OUTDIR = "D:\\backup\\"; // Cambiar segun necesidades y tipo servidor
$DirectorioMysqlDump="D:\\xampp\\mysql\\bin\\";
/*
Windows:
$DirectorioMysqlDump = "c:\\mysql\\bin\\mysqldump.exe -u $usuario --password=$passwd --opt $bd";  
Linux:
$DirectorioMysqlDump = "/mysql/bin/mysqldump -u $usuario --password=$passwd --opt $bd";  
*/
$DirectorioRaiz=$OUTDIR;
$now = date("Y_m_d_H_i_s", time());
$outfile = "Backup_$now.zip";
$periodo = time() - 259200; // Los archivos anteriores a este periodo (3 dias = 259200 segundos) serán borrados
$DirectorioAdicional="Backup_".$now;
// Crear nuevo archivo ZIP
# Más información sobre la clase PHP
# http://es.php.net/manual/en/class.ziparchive.php
//echo date("Y-m-d H:i", time()) . " Creando '$OUTDIR$outfile.zip' ... ";
//$zip = new ZipArchive;
//if (!$zip->open("$OUTDIR$outfile", ZIPARCHIVE::CREATE)) die("ERROR!!\r\n");
//echo "OK.\r\n";

// Tomar un listado de bases de datos
$q = mysql_query("SHOW DATABASES");


$OUTDIR=$OUTDIR.$DirectorioAdicional."\\";

if(!file_exists($ruta))
{
mkdir ($OUTDIR);
} 
$BaseDatosNoTomarEncuenta=array("mysql","information_schema","cdcol","phpmyadmin","test","webauth");
// Volcar todas las bases
while ($database = mysql_fetch_row($q)){
    if (!in_array($database[0],$BaseDatosNoTomarEncuenta))
        {
        // Nombrar archivo
        $filename = "{$database[0]}.sql";
        $tempfile = date("YmdHis", time()) . ".~swap";

        //echo date("Y-m-d H:i", time()) . " Volcando '$filename' ... ";
		$NombreArchivo=$filename;
        // Volcar datos
		$nombreBD=$database[0];
		
		
		$comando=$DirectorioMysqlDump ."mysqldump  -u $myuser -p$mypass  $nombreBD > \"$OUTDIR\"$NombreArchivo";
		
		//echo system($comando);
		echo shell_exec($comando);
		echo $comando."\n";
        //system("mysqldump -h $myhost -u $myuser -p$mypass --opt {$database[0]} > $OUTDIR$tempfile");

       /* echo "OK.\r\n"
        . date("Y-m-d H:i", time()) . " Agregando '$filename' a '$outfile' ... ";
*/
        //  Agregar archivo al ZIP
        //$zip->addFile($OUTDIR.$tempfile, $filename);
		

        // Recordar los temporales utilizados
        $DUMPFILES[] = $OUTDIR.$tempfile;

       // echo "OK.\r\n";
        }
}
echo $OUTDIR;
//$zip->addFile($OUTDIR, $filename);
// Desconectar de la base de datos
mysql_close($DB);
include("comprimir.php");
comprimir($OUTDIR,$OUTDIR."../".$DirectorioAdicional.".zip");
$Destino="E:\\Sincronizacion\\Drive\\BackupMySQL\\";
$DestinoDrive="C:\\Users\\Rony\\Google Drive\\";

$NombreArchivoDirectorio=$DirectorioRaiz.$DirectorioAdicional.".zip";
echo $DestinoDrive;
shell_exec("copy ".$NombreArchivoDirectorio."  ".$Destino);
//shell_exec("copy ".$NombreArchivoDirectorio."  ".$DestinoDrive);
//copy($OUTDIR."../".$DirectorioAdicional.".zip",$Destino);
// Cerrar archivo ZIP
//$zip->close();
exit();
// Eliminar temporales. Importante hacerlo DESPUÉS de cerrar el ZIP
foreach($DUMPFILES as $file)
    unlink($file);

// Elminar archivos antiguos
echo date("Y-m-d H:i", time()) . " Eliminando copias antiguas...\r\n";
$D = opendir($OUTDIR);
while ($F = readdir($D))
    if ($F != "." && $F != "..")
        if (filectime($OUTDIR.$F) < $periodo)
            if (!unlink($OUTDIR.$F))
                echo date("Y-m-d H:i", time()) . " No se pudo eliminar el archivo $F.\r\n";        
closedir($D);

echo date("Y-m-d H:i", time()) . " Tarea finalizada.\r\n";
?>