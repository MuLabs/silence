<?php
namespace Mu\Kernel\Cron;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
    protected $properties = array(
        'cron' => array(
            'infos' => array(
                'db' => 'crontab',
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'id',
                    ),
                )
            ),
            'properties' => array(
                'id' => array(
                    'title' => 'ID',
                    'db' => 'id',
                    'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                    'type' => 'smallint',
                ),
                'minute' => array(
                    'title' => 'Minutes frequency',
                    'db' => 'minute',
                    'type' => 'varchar',
                    'length' => 5,
                    'pdo_extra' => 'NOT NULL DEFAULT "*"',
                ),
                'hour' => array(
                    'title' => 'Hour frequency',
                    'db' => 'hour',
                    'type' => 'varchar',
                    'length' => 5,
                    'pdo_extra' => 'NOT NULL DEFAULT "*"',
                ),
                'day' => array(
                    'title' => 'Day of month frequency',
                    'db' => 'day',
                    'type' => 'varchar',
                    'length' => 5,
                    'pdo_extra' => 'NOT NULL DEFAULT "*"',
                ),
                'month' => array(
                    'title' => 'Month frequency',
                    'db' => 'month',
                    'type' => 'varchar',
                    'length' => 5,
                    'pdo_extra' => 'NOT NULL DEFAULT "*"',
                ),
                'week' => array(
                    'title' => 'Week day frequency',
                    'db' => 'week',
                    'type' => 'varchar',
                    'length' => 11,
                    'pdo_extra' => 'NOT NULL DEFAULT "*"',
                ),
                'script' => array(
                    'title' => 'Script path',
                    'db' => 'script',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'params' => array(
                    'title' => 'Script params',
                    'db' => 'params',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'dateLast' => array(
                    'title' => 'Execution date',
                    'db' => 'dateLast',
                    'type' => 'timestamp',
                ),
                'dateError' => array(
                    'title' => 'Last error date',
                    'db' => 'dateError',
                    'type' => 'timestamp',
                ),
                'active' => array(
                    'title' => 'Status active',
                    'form' => array(
                        'type' => 'checkbox',
                    ),
                    'db' => 'active',
                    'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 1',
                    'type' => 'tinyint',
                ),
                'force' => array(
                    'title' => 'Force next execution',
                    'form' => array(
                        'type' => 'checkbox',
                    ),
                    'db' => 'force',
                    'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 0',
                    'type' => 'tinyint',
                ),
            )
        )
    );

    /**
     * @param int
     * @return array
     */
    public function get($id)
    {
        $sql = 'SELECT :id, :minute, :hour, :day, :month, :week, :script, :params, :dateLast, :dateError, :active, :force
			    FROM @
			    WHERE id = ? ';
        $data= array((int)$id);

        $dbhr = $this->getApp()->getDatabase()->getHandler('readFront');
        $query = new Kernel\Db\Query($sql, $data, $this);
        $result = $dbhr->sendQuery($query);

        return $this->getEntity($result->fetchRow());
    }

    /**
     * @param null|bool
     * @return array
     */
    public function getAll($active = null)
    {
        $sql = 'SELECT :id, :minute, :hour, :day, :month, :week, :script, :params, :dateLast, :dateError, :active, :force
			    FROM @ ';
        $data= array();

        if ($active!==null) {
            $sql.= ' WHERE :active = ? ';
            $data[] = (int)$active;
        }

        $dbhr = $this->getApp()->getDatabase()->getHandler('readFront');
        $query = new Kernel\Db\Query($sql, $data, $this);
        $result = $dbhr->sendQuery($query);

        $list = array();
        while ($data = $result->fetchRow()) {
            $list[] = $this->getEntity($data);
        }
        return $list;
    }

    /**
     * @param $id
     * @param bool $active
     */
    public function setActive($id, $active = true)
    {
        $sql = 'UPDATE @ SET :active = ? WHERE :id = ?';

        $dbhr = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query($sql, array((int)$active, $id), $this);
        $dbhr->sendQuery($query);
    }

    /**
     * @param $id
     * @param bool $force
     */
    public function setForce($id, $force = true)
    {
        $sql = 'UPDATE @ SET :force = ? WHERE :id = ?';

        $dbhr = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query($sql, array((int)$force, $id), $this);
        $dbhr->sendQuery($query);
    }

    /**
     * @param $id
     */
    public function setLastExecution($id)
    {
        $sql = 'UPDATE @ SET :dateLast = NOW(), :force = ? WHERE :id = ?';

        $dbhr = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query($sql, array(0, $id), $this);
        $dbhr->sendQuery($query);
    }

    /**
     * @param $id
     */
    public function setLastError($id)
    {
        $sql = 'UPDATE @ SET :dateError = NOW(), :force = ? WHERE :id = ?';

        $dbhr = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query($sql, array(0, $id), $this);
        $dbhr->sendQuery($query);
    }

    /**
     * @param string $frequency
     * @param bool $time
     * @return mixed
     */
    function isExecutionTime($frequency = '* * * * *', $time = false)
    {
        $time = is_string($time) ? strtotime($time) : time();
        $time = explode(' ', date('i G j n w', $time));

        // Get frequency into array:
        $crontab = explode(' ', $frequency);

        // Create the test from crontab frequency:
        foreach ($crontab as $k => &$v) {
            $v = explode(',', $v);

            $regexps = array(
                '/^\*$/',   # every
                '/^\d+$/',  # digit
                '/^(\d+)\-(\d+)$/', # range
                '/^\*\/(\d+)$/'     # every digit
            );
            $content = array(
                "true",              # every
                "{$time[$k]} === 0", # digit
                "($1 <= {$time[$k]} && {$time[$k]} <= $2)", # range
                "{$time[$k]} % $1 === 0"                    # every digit
            );

            foreach ($v as &$v1) {
                $v1 = preg_replace($regexps, $content, $v1);
            }
            $v = '('.implode(' || ', $v).')';
        }

        // Rebuild test from array:
        $crontab = implode(' && ', $crontab);

        // Return test result:
        return eval("return {$crontab};");
    }

    /**
     * @param array $data
     * @return array
     */
    private function getEntity(array $data)
    {
        list($id, $minute, $hour, $day, $month, $week, $script, $params, $dateLast, $dateError, $active, $force) = $data;

        return array(
            'id'        => $id,
            'minute'    => $minute,
            'hour'      => $hour,
            'day'       => $day,
            'month'     => $month,
            'week'      => $week,
            'script'    => $script,
            'params'    => $params,
            'dateLast'  => $dateLast,
            'dateError' => $dateError,
            'active'    => (bool)$active,
            'force'     => (bool)$force,
            'frequency' => "$minute $hour $day $month $week"
        );
    }
}
