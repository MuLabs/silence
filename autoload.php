<?php

function autoLoader($name)
{
	$prefix = substr($name, 0, strpos($name, '\\', strpos($name, '\\')+1)+1);
	if ($prefix === 'Beable\\Kernel\\') {
		$name = str_replace(array('Beable\\Kernel\\', '\\'), array('/', '/'), $name);
		$path = KERNEL_PATH;
	} elseif ($prefix === 'Beable\\App\\') {
		$name = str_replace(array('Beable\\App\\', '\\'), array('/', '/'), $name);
		$path = APP_PATH;
	} elseif ($prefix === 'Beable\\Bundle\\') {
		$name = str_replace(array('Beable\\Bundle\\', '\\'), array('/', '/'), $name);
		$path = BUNDLE_PATH;
	}

	$file = $path . '/' . strtolower($name) . '.php';
	if (!file_exists($file)) {
		throw new Exception('Class not found : ' . $name);
	}
	require($file);
}

spl_autoload_register('autoLoader');