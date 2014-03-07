<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

/**
 * @namespace Mu\Kernel\Db;
 */
class Query extends Kernel\Core
{

	private $values = array();
	private $query = array();
	private $defaultRequestable;
	private $isShortMode = false;
	private $comment;
	private $type;

	const TYPE_SELECT = 1;
	const TYPE_UPDATE = 2;
	const TYPE_INSERT = 3;
	const TYPE_UNKNOWN = 4;

	/**
	 * @param string $query
	 * @param array $values
	 * @param Kernel\Db\Interfaces\Requestable $defaultRequestable
	 * @return Query
	 */
	public function __construct(
		$query,
		array $values = array(),
		Kernel\Db\Interfaces\Requestable $defaultRequestable = null
	) {
		$this->setValues($values);
		$this->setQuery($query);
		$this->setDefaultRequestable($defaultRequestable);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return Kernel\Db\Interfaces\Requestable
	 */
	public function getDefaultRequestable()
	{
		return $this->defaultRequestable;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		if (!$this->type) {
			$strType = substr($this->getQuery(), 0, 6);

			switch ($strType) {
				case 'SELECT':
					$this->type = self::TYPE_SELECT;
					break;
				case 'UPDATE':
					$this->type = self::TYPE_UPDATE;
					break;
				case 'INSERT':
				case 'REPLAC':
					$this->type = self::TYPE_INSERT;
					break;
				default:
					$this->type = self::TYPE_UNKNOWN;
					break;
			}
		}

		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function isShortMode()
	{
		return $this->isShortMode;
	}

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @param string $query
	 */
	public function setQuery($query)
	{
		$this->query = trim($query);
	}

	/**
	 * @param string[] $values
	 * @throws Exception
	 */
	public function setValues(array $values)
	{
		$this->values = $values;
	}

	/**
	 * @param bool $value
	 */
	public function setShortMode($value)
	{
		$this->isShortMode = (bool)$value;
	}

	/**
	 * @param Kernel\Db\Interfaces\Requestable $requestable
	 */
	public function setDefaultRequestable(Kernel\Db\Interfaces\Requestable $requestable = null)
	{
		$this->defaultRequestable = $requestable;
	}
}
