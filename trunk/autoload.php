<?php

function autoLoader($name)
{
    $path = '';
    $prefix = substr($name, 0, 7);
    if ($prefix === 'Mu\\Kern') {
        $name = str_replace(array('Mu\\Kernel\\', '\\'), array('/', '/'), $name);
        $path = KERNEL_PATH;
    } elseif ($prefix === 'Mu\\App\\') {
        $name = str_replace(array('Mu\\App\\', '\\'), array('/', '/'), $name);
        $path = APP_PATH;
    } elseif ($prefix === 'Mu\\Bund') {
        $name = str_replace(array('Mu\\Bundle\\', '\\'), array('/', '/'), $name);
        $path = BUNDLE_PATH;
    }

    if ($path) {
        $file = $path . '/' . strtolower($name) . '.php';

        if (file_exists($file)) {
            require($file);
        }
    }
}

spl_autoload_register('autoLoader');