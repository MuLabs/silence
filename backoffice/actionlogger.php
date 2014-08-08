<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

abstract class ActionLogger extends Kernel\Core
{
	const ACTION_CREATE = 1;
	const ACTION_DELETE = 2;
	const ACTION_UPDATE = 3;
	const ACTION_ADD = 4;
	const RELATION_TYPE_COLUMN = 'column';
	const RELATION_TYPE_ENTITY_TYPE = 'entityType';

	/**
	 * @param Kernel\Model\Entity $object
	 * @param int $action
	 * @param array $oldValue
	 * @param array $newValue
	 * @return bool
	 */
	abstract public function create(Kernel\Model\Entity $object, $action, array $oldValue, array $newValue);

	/**
	 * @static
	 * @param Kernel\Model\Entity $object
	 * @return array
	 */
	abstract public function getLogsFromObject(Kernel\Model\Entity $object);
}