<?php

// Register an autoloader
$loader = new Phalcon\Loader();
$loader->registerDirs(
array(
CONTROLLER_PATH,
MODEL_PATH,
)
)->register();

$autoloadFile = ROOT_PATH.'/autoload.php';
if (!file_exists($autoloadFile)) {
$psr = require(VENDOR_PATH.'/composer/autoload_namespaces.php');

$fullPsr = array();
foreach ($psr as $namespace=>$dirPath) {
$realPath = reset($dirPath);
$fullPsr[$namespace] = $realPath;
$fullPsr = readDirNamespace($realPath, $namespace, $fullPsr);
}

$file = fopen($autoloadFile, 'w');
fwrite($file, '<?php'."\n");
    fwrite($file, 'return array('."\n");
    foreach ($fullPsr as $namespace => $dirPath) {
        if (strrpos($namespace, '\\') == strlen($namespace)-1) {
            $namespace = substr($namespace, 0, -1);
        }
        fwrite($file, "\t".'"'.str_replace('\\', '\\\\', $namespace).'" => "'.str_replace(array('\\', '/'), '\\\\', $dirPath).'",'."\n");
    }
    fwrite($file, ');'."\n");
    fclose($file);
}

$realPsr = require($autoloadFile);
$loader->registerNamespaces(
    $realPsr
);

function readDirNamespace($dirPath, $currentNamespace, $fullPsr) {
    $dir = opendir($dirPath);
    while($file = readdir($dir)) {
        if (is_dir($dirPath.'/'.$file) && substr($file, 0, 1) != '.') {
            $newNamespace = $currentNamespace.ucfirst($file).'\\';
            $newPath = $dirPath.'/'.$file;
            $fullPsr[$newNamespace] = $newPath;

            $fullPsr = array_merge($fullPsr, readDirNamespace($newPath, $newNamespace, $fullPsr));
        }
    }
    closedir($dir);

    return $fullPsr;
}