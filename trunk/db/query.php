<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

/**
 * @namespace Mu\Kernel\Db;
 */
class Query extends Kernel\Core
{

    protected $values = array();
    protected $query = array();
    protected $requestableList = array();
    protected $isShortMode = false;
    protected $type;

    const TYPE_SELECT = 1;
    const TYPE_UPDATE = 2;
    const TYPE_INSERT = 3;
    const TYPE_UNKNOWN = 4;

    /**
     * @param string $query
     * @param array $values
     * @param array|Kernel\Db\Interfaces\Requestable $requestableList
     * @return Query
     */
    public function __construct(
        $query,
        array $values = array(),
        $requestableList = null
    ) {
        $this->setValues($values);
        $this->setQuery($query);
        $this->setRequestableList($requestableList);

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
     * @return Kernel\Db\Interfaces\Requestable[]
     */
    public function  getRequestableList()
    {
        return $this->requestableList;
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
     * @param array $requestableList
     * @throws Exception
     */
    public function setRequestableList($requestableList = array())
    {
        if (is_array($requestableList)) {
            $this->requestableList = $requestableList;
        } else {
            if ($requestableList instanceof Kernel\Db\Interfaces\Requestable) {
                $this->requestableList['default'] = $requestableList;
            } else {
                throw new Exception(Exception::INVALID_MANAGER, $requestableList);
            }
        }
    }
}
