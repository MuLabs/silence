<?php

function autoLoader($name)
{
	$prefix = substr($name, 0, strpos($name, '\\', strpos($name, '\\')+1)+1);
	if ($prefix === 'Beable\\Kernel\\') {
		$name = str_replace(array('Beable\\Kernel\\', '\\'), array('/', '/'), $name);
		require(KERNEL_PATH . strtolower($name) . '.php');
	} elseif ($prefix === 'Beable\\App\\') {
		$name = str_replace(array('Beable\\App\\', '\\'), array('/', '/'), $name);
		require(APP_PATH . '/' . strtolower($name) . '.php');
	} elseif ($prefix === 'Beable\\Bundle\\') {
		$name = str_replace(array('Beable\\Bundle\\', '\\'), array('/', '/'), $name);
		require(BUNDLE_PATH . '/' . strtolower($name) . '.php');
	}
}

spl_autoload_register('autoLoader');