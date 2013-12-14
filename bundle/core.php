<?php
namespace Beable\Kernel\Bundle;

use Beable\Kernel;

abstract class Core extends Kernel\Core
{
	abstract public function initialize();
}