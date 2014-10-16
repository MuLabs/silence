<?php
namespace Mu\Kernel\Db\Interfaces;

use Mu\Kernel;

interface Requestable
{
    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param bool $default
     * @return array
     */
    public function getRequestReplaceList($default = false);

    /**
     * @param string $stdOut
     * @return bool
     */
    public function createStructure($stdOut = '\print');

    /**
     * @param string $stdOut
     * @return bool
     */
    public function createDefaultDataSet($stdOut = '\print');

    /**
     * @return string
     */
    public function getDefaultGroup();

    /**
     * @param string $group
     * @param string $name
     * @throws Kernel\Db\Exception
     * @return array
     */
    public function getProperty($group, $name);

    /**
     * @param string $group
     * @return array
     * @throws Kernel\Db\Exception
     */
    public function getGroupPropertyInfos($group);

    /**
     * @param string $group
     * @param string $name
     * @param bool $isShortMode
     * @return string
     */
    public function getPropertyForDb($group, $name, $isShortMode = false);

    /**
     * @param string $group
     * @return string
     */
    public function getGroupForDb($group);

    /**
     * @return Kernel\Db\Handler
     * @throws Kernel\Db\Exception
     */
    public function getDbHandler();

    /**
     * @param Kernel\Db\Handler $handler
     */
    public function setDbHandler(Kernel\Db\Handler $handler);
}