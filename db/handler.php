<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

abstract class Handler extends Kernel\Core
{
	const PARAM_INT = 1;
	const PARAM_STR = 2;
	const PARAM_BOOL = 3;
	const PARAM_FLOAT = 4;

	protected $knowActions = array(
		'tinyint' => self::PARAM_INT,
		'smallint' => self::PARAM_INT,
		'mediumint' => self::PARAM_INT,
		'int' => self::PARAM_INT,
		'bigint' => self::PARAM_INT,
		'timestamp' => self::PARAM_STR,
		'float' => self::PARAM_FLOAT,
		'bool' => self::PARAM_BOOL,
		'string' => self::PARAM_STR,
		'varchar' => self::PARAM_STR,
		'char' => self::PARAM_STR,
		'blob' => self::PARAM_STR,
		'text' => self::PARAM_STR,
		'long_blob' => self::PARAM_STR,
		'long_text' => self::PARAM_STR,
		'date' => self::PARAM_STR,
		'ip' => self::PARAM_INT,
	);
	protected $typeCheckValues = array(
		Query::TYPE_SELECT,
		Query::TYPE_UPDATE,
	);

    protected $cacheQuery = array();
    protected $link;

	/**
	 * @return mixed
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param mixed $link
	 */
	public function setLink($link)
	{
        $this->link = $link;
    }

	/**
	 * @param string $string
	 * @return string
	 */
	abstract protected function protectString($string);

	/**
	 * Check if query is valid
	 *
	 * @param Query $query
	 * @throws Exception
	 * @return bool
	 */
	protected function checkQuery(Query $query)
	{
		$strQuery = $query->getQuery();
        $isShortMode = $query->isShortMode();
        $hashQuery = sha1($strQuery) . '|' . ((int)$isShortMode);
        $checkValue = in_array($query->getType(), $this->typeCheckValues);

        #region value check analysis
        $values = $query->getValues();
        $valuesOffset = count($values) - 1;
        $lastFound = strrpos($strQuery, '?');
        $subQuery = $strQuery;
        $requestableList = $query->getRequestableList();
        $defaultRequestable = $requestableList['default'];
        $defaultGroup = $defaultRequestable->getDefaultGroup();

        while ($lastFound !== false) {
            $subQuery = substr($subQuery, 0, $lastFound);
            $subPropQuery = substr($subQuery, strrpos($subQuery, ':') + 1);
            $posT = strpos($subPropQuery, "\t");
            $pos = strlen($subPropQuery);
            if ($posT !== false) {
                $pos = $posT;
            }
            $posS = strpos($subPropQuery, ' ');
            if ($posS !== false) {
                $pos = min($pos, $posS);
            }
            $posN = strpos($subPropQuery, "\n");
            if ($posN !== false) {
                $pos = min($pos, $posN);
            }

            $subPropQuery = substr($subPropQuery, 0, $pos);
            if (!$subPropQuery) {
                throw new exception($subQuery, Exception::INVALID_SUB_PROP_QUERY);
            }
            $subPropQuery = str_replace(
                array(')', ',', ';', '(', '/', '\\'),
                '',
                $subPropQuery
            );

            $property = explode('.', $subPropQuery);
            $propertyCount = count($property);
            if ($propertyCount === 3) {
                $property = array(
                    'manager' => $requestableList[$property[0]],
                    'group' => $property[1],
                    'property' => $property[2],
                );
            } elseif ($propertyCount === 2) {
                $property['manager'] = $defaultRequestable;
                $property['group'] = reset($property);
                $property['property'] = next($property);
            } elseif ($propertyCount === 1) {
                $property['manager'] = $defaultRequestable;
                $property['group'] = $defaultGroup;
                $property['property'] = reset($property);
            } else {
                throw new exception(array(
                    'query' => $strQuery,
                    'property' => $property
                ), exception::INVALID_PROPERTY_COUNT);
            }
            $property = $property['manager']->getProperty($property['group'], $property['property']);
            $propertyType = $this->knowActions[$property['type']];
            $values[$valuesOffset] = array(
                'type' => $propertyType,
                'value' => ($checkValue ? $this->checkProperty(
                        $values[$valuesOffset],
                        $this->knowActions[$property['type']]
                    ) : $values[$valuesOffset])
            );
            $lastFound = strrpos($subQuery, '?');
            --$valuesOffset;
        }
        $query->setValues($values);
        #endregion

        if (!isset($this->cacheQuery[$hashQuery])) {
            $requestReplaceList = $this->getReplaceList($defaultRequestable, $requestableList, $isShortMode);

            $this->cacheQuery[$hashQuery] = preg_replace(
                array_keys($requestReplaceList),
                array_values($requestReplaceList),
                $strQuery
            );
        }

        $query->setQuery($this->cacheQuery[$hashQuery]);
    }

    /**
     * @param Interfaces\Requestable $defaultRequestable
     * @param Interfaces\Requestable[] $requestableList
     * @param bool $isShortMode
     * @return array
     */
    private function getReplaceList(
        Kernel\Db\Interfaces\Requestable $defaultRequestable,
        array $requestableList,
        $isShortMode = false
    ) {
        $requestReplaceList = array();
        $startPattern = '#';
        $endPattern = '([^\w\.]|$)#i';
        $defaultGroup = $defaultRequestable->getDefaultGroup();
        foreach ($requestableList as $requestableLabel => $oneRequestable) {
            $isDefault = ($oneRequestable == $defaultRequestable);
            $newReplaceList = $oneRequestable->getRequestReplaceList($isDefault, $isShortMode);
            foreach ($newReplaceList['group'] as $key => $value) {
                if ($isDefault) {
                    if ($key == $defaultGroup) {
                        $key = '@';
                    } else {
                        $key = '@' . $key;
                    }
                } else {
                    $key = '@' . $requestableLabel . '.' . $key;
                }
                $requestReplaceList[$startPattern . $key . $endPattern] = $value . '$1';
            }

            foreach ($newReplaceList['property'] as $key => $value) {
                if ($isDefault) {
                    $key = ':' . $key;
                } else {
                    $key = ':' . $requestableLabel . '.' . $key;
                }
                $requestReplaceList[$startPattern . $key . $endPattern] = $value . '$1';
            }
        }

        return $requestReplaceList;
    }

    /**
	 * @param mixed $value
	 * @param int $type
	 * @return int|string
	 */
	private function checkProperty($value, $type)
	{
		switch ($type) {
			case self::PARAM_INT:
				return (int)$value;
				break;
			case self::PARAM_STR:
				return $this->protectString($value);
				break;
			case self::PARAM_BOOL:
				return (int)(bool)$value;
				break;
			case self::PARAM_FLOAT:
				return (float)$value;
				break;
			default:
				return $value;
				break;
		}
	}

	/**
	 * @param array $property
	 * @return Kernel\Db\Traits\Requestable
	 * @throws Exception
	 */
	private function getManager(array $property)
	{
		if (!isset($property['manager'])) {
			throw new Exception(print_r($property, true), Exception::INVALID_MANAGER);
		}
		return $this->getApp()->getModelManager()->getOneManager($property['manager']);
	}

	/**
	 * @return bool
	 */
	public abstract function startTransaction();

	/**
	 * @return bool
	 */
	public abstract function commitTransaction();

	/**
	 * @return bool
	 */
	public abstract function rollbackTransaction();

	/**
	 * @param string $query
	 * @return Result
	 */
	public abstract function query($query);

	/**
	 * @param Query $query
	 * @return Result
	 */
	public abstract function sendQuery(Kernel\Db\Query $query);

	/**
	 * @return bool
	 */
	public abstract function hasError();

	/**
	 * @return string
	 */
	public abstract function getErrors();

	/**
	 * @return string
	 */
	public abstract function getLastError();

	/**
	 * @return int
	 */
	public abstract function getInsertId();

	/**
	 * @param array $property
	 * @return string
	 */
	public abstract function getStructureFromProperty(array $property);

	/**
	 * @param string $tableToken
	 * @param string $keyName
	 * @param array $key
	 * @return string
	 */
	public abstract function getStructureFromKey($tableToken, $keyName, array $key);

	/**
	 * @param array $tableInfos
	 * @return string
	 */
	public abstract function getStructureFromTableInfos(array $tableInfos);
}
