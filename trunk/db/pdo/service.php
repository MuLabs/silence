<?php
namespace Mu\Kernel\Db\PDO;

use Mu\Kernel;

class Service extends Kernel\Db\Service
{
	/**
	 * @param $contextName
	 * @return Handler
	 */
	protected function generateHandler($contextName)
	{
		return $this->connect($this->getContext($contextName));
	}

	/**
	 * @param \mysqli_result $query
	 * @return array
	 */
	public function fetchRow(\mysqli_result $query)
	{
		if ($query) {
			return $query->fetch_row();
		} else {
			return false;
		}
	}

	/**
	 * @param \mysqli_result $query
	 * @return bool
	 */
	public function freeResult(\mysqli_result $query)
	{
		$query->free_result();
	}

	/**
	 * @param \mysqli_result $query
	 * @return int
	 */
	public function numRows(\mysqli_result $query)
	{
		if ($query) {
			return $query->num_rows;
		} else {
			return 0;
		}
	}

	/**
	 * @param Kernel\Db\Context $context
	 * @return Handler
	 */
	private function connect(Kernel\Db\Context $context)
	{
		@$handler = new Handler($context->getParameter('dsn'), $context->getParameter(
			'username'
		), $context->getParameter('password'));

		if ($handler && $handler->hasError()) {
			echo 'Failed to connect to DBR : ' . $handler->getErrors();
			exit;
		}

		$handler->setApp($this->getApp());

		if ($context->isReadOnly()) {
			$handler->query('SET GLOBAL read_only=true');
		}
		$handler->query('SET NAMES UTF8');

		return $handler;
	}
}
