<?php

function autoLoader($name)
{
	$path = '';
	$prefix = substr($name, 0, strpos($name, '\\', strpos($name, '\\') + 1) + 1);
	if ($prefix === 'Mu\\Kernel\\') {
		$name = str_replace(array('Mu\\Kernel\\', '\\'), array('/', '/'), $name);
		$path = KERNEL_PATH;
	} elseif ($prefix === 'Mu\\App\\') {
		$name = str_replace(array('Mu\\App\\', '\\'), array('/', '/'), $name);
		$path = APP_PATH;
	} elseif ($prefix === 'Mu\\Bundle\\') {
		$name = str_replace(array('Mu\\Bundle\\', '\\'), array('/', '/'), $name);
		$path = BUNDLE_PATH;
	}

	$file = $path . '/' . strtolower($name) . '.php';
	require($file);
}

spl_autoload_register('autoLoader');