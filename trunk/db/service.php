<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

abstract class Service extends Kernel\Service\Core
{
    const CONTEXT_INSTALL = 'install';

    protected $logs = array();
    protected $handlers = array();
    protected $contexts = array();
    protected $properties = array(
        'version' => array(
            'infos' => array(
                'db' => 'site_db_version',
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'id',
                    ),
                ),
            ),
            'properties' => array(
                'id' => array(
                    'title' => 'ID',
                    'database' => array(
                        'attribute' => 'id_db_version',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'mediumint',
                    ),
                ),
                'bundle' => array(
                    'title' => 'Bundle name',
                    'database' => array(
                        'attribute' => 'bundle',
                        'type' => 'varchar',
                        'length' => 30,
                    ),
                ),
                'filename' => array(
                    'title' => 'Update name',
                    'database' => array(
                        'attribute' => 'filename',
                        'type' => 'char',
                        'length' => 20,
                        'pdo_extra' => 'NOT NULL',
                    ),
                ),
                'date' => array(
                    'title' => 'Execution date',
                    'database' => array(
                        'attribute' => 'date',
                        'type' => 'timestamp',
                        'pdo_extra' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    ),
                ),
            )
        ),
    );

    /**
     * @param string $name
     * @param Handler $handler
     * @return void
     */
    public function addHandler($name, Handler $handler)
    {
        $this->handlers[$name] = $handler;
    }

    /**
     * @return Handler[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param string $name
     * @throws Exception
     * @return Handler
     */
    public function getHandler($name)
    {
        if (!isset($this->handlers[$name])) {
            $this->handlers[$name] = $this->generateHandler($name);
        }
        return $this->handlers[$name];
    }

    /**
     * @param Context $context
     */
    public function registerContext(Context $context)
    {
        // Register installation context:
        if (empty($this->contexts)) {
            $params = $context->getParameters();
            $params['dsn'] = preg_replace('#;dbname=\w+#', '', $params['dsn']);
            $install = new Kernel\Db\Context($this->getConstant('CONTEXT_INSTALL'), $params);
            $this->contexts[$install->getName()] = $install;
        }

        $this->contexts[$context->getName()] = $context;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function getContext($name)
    {
        if (!isset($this->contexts[$name])) {
            throw new Exception($name, Exception::CONTEXT_NOT_FOUND);
        }
        return $this->contexts[$name];
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param string $contextName
     * @return Handler
     */
    abstract protected function generateHandler($contextName);

    /**
     * @param string $stdOut
     * @param bool $exec
     */
    public function defaultUpdate($stdOut, $exec = true)
    {
        $this->getApp()->getToolbox()->removeMemoryLimits();

        $updatePath = array(
            'main' => APP_UPDATE_PATH
        );
        $bundleList = $this->getApp()->getBundler()->getAll();
        foreach ($bundleList as $bundleName => $bundleObject) {
            if (file_exists($bundleObject->getUpdatePath())) {
                $updatePath[$bundleName] = $bundleObject->getUpdatePath();
            }
        }

        $updateDone = array();
        $handler = $this->getHandler($this->getApp()->getDefaultDbContext());
        $sql = 'SELECT :bundle, :filename FROM @';
        $query = new Query($sql, array(), $this);
        $result = $handler->sendQuery($query);

        while (list($bundle, $filename) = $result->fetchRow()) {
            $updateDone[$bundle . '/' . $filename] = 1;
        }

        $updateTodo = array();

        foreach ($updatePath as $bundleName => $oneUpdatePath) {
            $dirh = opendir($oneUpdatePath);
            while ($filename = readdir($dirh)) {
                if (is_file($oneUpdatePath . '/' . $filename) && empty($updateDone[$bundleName . '/' . $filename])) {
                    $updateTodo[] = $bundleName . '/' . $filename;
                }
            }
        }

        uasort($updateTodo, array($this, 'sortUpdates'));

        $count = count($updateTodo);
        call_user_func($stdOut, $count . ' updates to do...');
        $i = 1;

        // Used into update files
        foreach ($updateTodo as $filename) {
            list($bundleName, $filename) = explode('/', $filename);
            call_user_func($stdOut, 'Start update ' . $i . '/' . $count . ' : ' . $filename);

            if ($exec) {
                require_once($updatePath[$bundleName] . '/' . $filename);
            }

            $sql = 'REPLACE INTO @ (:bundle, :filename) VALUES (?, ?)';
            $query = new Query($sql, array($bundleName, $filename), $this);
            $handler->sendQuery($query);
            call_user_func($stdOut, 'End update ' . $i++ . '/' . $count);
        }
    }

    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    private function sortUpdates($a, $b)
    {
        list($unused, $a) = explode('/', $a);
        list($unused, $b) = explode('/', $b);

        return strcmp($a, $b);
    }
}
