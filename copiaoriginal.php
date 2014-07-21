<?php
#############################################
# NInguna
# Tarea de copia seguridad de todas las bases de datos en MySQL
#
# CopyLeft, puedes hacer con este script lo que te de la gana
#
#############################################
set_time_limit(0);
header("Content-type: text/plain; charset=UTF-8");
echo date("Y-m-d H:i", time()) . " Creando copia de seguridad MySQL...\r\n";

// Datos de acceso a MySQL
$myhost = "localhost";
$myuser = 'root';
$mypass = 'miclave';
$DB = mysql_connect($myhost, $myuser, $mypass) or die(date("Y-m-d H:i", time()) . " ERROR!! No se pudo conectar a MySQL.\r\n");

// Otros parámetros
$OUTDIR = "D:\\backup\\"; // Cambiar segun necesidades y tipo servidor
$now = date("YmdHi", time());
$outfile = "MySQL_$now.zip";
$periodo = time() - 259200; // Los archivos anteriores a este periodo (3 dias = 259200 segundos) serán borrados

// Crear nuevo archivo ZIP
# Más información sobre la clase PHP
# http://es.php.net/manual/en/class.ziparchive.php
echo date("Y-m-d H:i", time()) . " Creando '$OUTDIR$outfile.zip' ... ";
$zip = new ZipArchive;
if (!$zip->open("$OUTDIR$outfile", ZIPARCHIVE::CREATE)) die("ERROR!!\r\n");
echo "OK.\r\n";

// Tomar un listado de bases de datos
$q = mysql_query("SHOW DATABASES");

// Volcar todas las bases
while ($database = mysql_fetch_row($q))
    if ($database[0] != "information_schema" && $database[0] != "mysql")
        {
        // Nombrar archivo
        $filename = "{$database[0]}.sql";
        $tempfile = date("YmdHis", time()) . ".~swap";

        echo date("Y-m-d H:i", time()) . " Volcando '$filename' ... ";

        // Volcar datos
        system("mysqldump -h $myhost -u $myuser -p$mypass --opt {$database[0]} > $OUTDIR$tempfile");

        echo "OK.\r\n"
        . date("Y-m-d H:i", time()) . " Agregando '$filename' a '$outfile' ... ";

        //  Agregar archivo al ZIP
        $zip->addFile($OUTDIR.$tempfile, $filename);

        // Recordar los temporales utilizados
        $DUMPFILES[] = $OUTDIR.$tempfile;

        echo "OK.\r\n";
        }

// Desconectar de la base de datos
mysql_close($DB);

// Cerrar archivo ZIP
$zip->close();

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