<?php
namespace Mu\Kernel\Error;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
    const HANDLER_NONE = 0;
    const HANDLER_DB = 1;

    protected $handler = self::HANDLER_NONE;
    protected $done = array();
    protected $properties = array(
        'error' => array(
            'infos' => array(
                'db' => 'error',
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'id',
                    ),
                ),
                'i_date' => array(
                    'type' => 'index',
                    'properties' => array(
                        'date',
                    ),
                ),
            ),
            'properties' => array(
                'id' => array(
                    'title' => 'ID',
                    'database' => array(
                        'attribute' => 'idError',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'mediumint',
                    ),
                ),
                'type' => array(
                    'title' => 'Error type',
                    'database' => array(
                        'attribute' => 'type',
                        'type' => 'varchar',
                        'length' => 10,
                    ),
                ),
                'priority' => array(
                    'title' => 'Error priority',
                    'database' => array(
                        'attribute' => 'priority',
                        'type' => 'smallint',
                    ),
                ),
                'message' => array(
                    'title' => 'Error message',
                    'database' => array(
                        'attribute' => 'message',
                        'type' => 'text',
                    ),
                ),
                'file' => array(
                    'title' => 'Error file',
                    'database' => array(
                        'attribute' => 'file',
                        'type' => 'varchar',
                        'length' => 255,
                    ),
                ),
                'line' => array(
                    'title' => 'Error line',
                    'database' => array(
                        'attribute' => 'line',
                        'type' => 'smallint',
                    ),
                ),
                'url' => array(
                    'title' => 'Error url',
                    'database' => array(
                        'attribute' => 'url',
                        'type' => 'varchar',
                        'length' => 255,
                    ),
                ),
                'referer' => array(
                    'title' => 'Error referer',
                    'database' => array(
                        'attribute' => 'referer',
                        'type' => 'varchar',
                        'length' => 255,
                    ),
                ),
                'count' => array(
                    'title' => 'Error count',
                    'database' => array(
                        'attribute' => 'count',
                        'type' => 'smallint',
                    ),
                ),
                'trace' => array(
                    'title' => 'Error trace',
                    'database' => array(
                        'attribute' => 'trace',
                        'type' => 'text',
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


    public function __construct()
    {
        //register_shutdown_function(array($this, 'logFatalError'));
        //set_error_handler(array($this, 'logError'), E_ALL);
    }

    /**
     * Permanent redirection
     */
    public function error301()
    {
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setCode(301);
        $response->send();
    }

    /**
     * Redirection error
     */
    public function error302()
    {
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setCode(302);
        $response->send();
    }

    /**
     * Unauthorized
     */
    public function error401()
    {
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setCode(401);
        $response->send();
    }

    /**
     * Not found
     * @param string $message
     */
    public function error404($message = 'Not found')
    {
        if (!$this->getApp()->getEnvironment() == $this->getApp()->getConstant('ENVIRONMENT_PROD')) {
            exit('ERROR 404 : ' . $message);
        }
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setCode(404);
        $response->send();
    }

    /**
     * Server exception
     */
    public function error500()
    {
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setCode(500);
        $response->send();
    }

    /**
     * Check if a PHP fatal error occurs, and log it
     *
     * @return bool
     */
    public function logFatalError()
    {
        if ($error = error_get_last()) {
            switch ($error['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    $this->create('php', $error['type'], $error['message'], $error['file'], $error['line']);
                return false;
            }
        }
        return false;
    }

    /**
     * Parses a context (stack trace)
     * @static
     * @param    array $trace An array containing the context
     * @return    string            The stack trace as a string
     */
    public function processTrace($trace)
    {
        array_shift($trace);
        $rawOut = array();
        foreach ($trace as $depth => $step) {
            $line = $depth . '. ';
            if (!empty($step['object'])) {
                $line .= get_class($step['object']) . '::';
            }
            $line .= $step['function'] . '()';
            if (isset($step['file'])) {
                $line .= ' at ' . $step['file'] . ' (' . $step['line'] . ')';
            }
            $rawOut[] = $line;
        }
        return implode(PHP_EOL, $rawOut);
    }

    /**
     * @return int
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param int $handler
     */
    public function setHandler($handler)
    {
        $this->handler = (int)$handler;
    }

    /**
     * Get the total numbers of errors
     *
     * @static
     * @return int
     */
    public function getErrorsCount()
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return 0;
        }
        $dbhr = $this->getApp()->getDatabase()->getHandler('readFront');
        $query = new Kernel\Db\Query(
            'SELECT COUNT(:id)	FROM @',
            array(), $this);
        $result = $dbhr->sendQuery($query);

        return $result->fetchValue();
    }

    /**
     * Log a PHP error
     *
     * @static
     * @param    int $errno PHP internal error code
     * @param    string $message PHP internal error description
     * @param    string $file PHP file where error occurs
     * @param    int $line Line on the file where error occurs
     * @return  bool
     */
    public function logError($errno, $message, $file, $line)
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return false;
        }

        $trace = debug_backtrace(true);
        $trace = $this->processTrace($trace);
        $this->create('php', $errno, $message, $file, $line, $trace);

        return false;
    }

    /**
     * Log an external error
     *
     * @static
     * @param    int $errno PHP internal error code
     * @param    string $message PHP internal error description
     * @param    string $file PHP file where error occurs
     * @param    int $line Line on the file where error occurs
     * @return  bool
     */
    public function logExternal($errno, $message, $file = '', $line = 0)
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return false;
        }

        $this->create('php', $errno, $message, $file, $line);
        return true;
    }

    /**
     * @static
     * @param    string $type
     * @param    int $priority
     * @param    string $message
     * @param    string $file
     * @param    int $line
     * @param    string $trace
     * @return bool
     */
    private function create($type, $priority, $message, $file, $line, $trace = '')
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return false;
        }

        if (is_array($trace)) {
            $trace = $this->processTrace($trace);
        }

        if ($this->getApp()->getEnvironment() == Kernel\Application::ENVIRONMENT_LOCAL) {
            $data = array($type, $priority, $message, $file . ':' . $line, $trace);
            echo "<br />Error(" . implode(', ', $data) . ")\n";
        }

        $priority = (int)$priority;
        $line = (int)$line;

        $request = $this->getApp()->getHttp()->getRequest();
        $url = $request->getRequestUri();
        $referer = $request->getRequestHeader()->getReferer();

        // If the unique key (type, priority, message, file, line) is broken
        // just update error count and date, then return the old object!
        $dbhw = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query('
            SELECT :id, :count
                FROM @
                WHERE :type = ?
                    AND :priority = ?
                    AND :file = ?
                    AND :line = ?
                    AND SUBSTRING(:message, 1, 100) = SUBSTRING(?, 1, 100)
            LIMIT 1', array($type, $priority, $file, $line, $message), $this);
        $result = $dbhw->sendQuery($query);

        if ($result->numRows() > 0) {
            list($id, $count) = $result->fetchRow();
        } else {
            $id = 0;
            $count = 1;
        }

        if ($id) {
            if (!isset($this->done[$id]) && $count < 65000) {
                $query = new Kernel\Db\Query('
                    UPDATE @
                        SET :count = :count + 1,
                            :url = ?,
                            :referer = ?,
                            :trace = ?
                        WHERE :id = ?', array($url, $referer, $trace, $id), $this);
                $dbhw->sendQuery($query);
                $this->done[$id] = true;
            }
        } else {
            $query = new Kernel\Db\Query('
                INSERT INTO @
                    (:type, :priority, :message, :file, :line, :url, :referer, :trace, :count)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', array(
                $type,
                $priority,
                $message,
                $file,
                $line,
                $url,
                $referer,
                $trace,
                $count
            ), $this);
            $dbhw->sendQuery($query);
        }

        return true;
    }

    /**
     * Get all errors logged into database, order by date DESC
     *
     * @return    array
     */
    public function getAllErrors()
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return array();
        }

        $dbhr = $this->getApp()->getDatabase()->getHandler('readFront');
        $query = new Kernel\Db\Query('
            SELECT :id, :type, :priority, :message, :file, :line, :url, :referer, :date, :trace, :count
			  FROM @
			ORDER BY :date DESC', array(), $this);
        $result = $dbhr->sendQuery($query);

        $errorsList = array();
        while (list($id, $type, $priority, $message, $file, $line, $url, $referer, $date, $trace, $count) = $result->fetchRow()) {
            $errorsList[] = array(
                'id' => $id,
                'type' => $type,
                'priority' => $this->getErrorPriority($priority),
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'url' => $url,
                'referer' => $referer,
                'date' => $date,
                'trace' => $trace,
                'count' => $count,
            );
        }
        return $errorsList;
    }

    /**
     * Flush all errors from database
     */
    public function flush()
    {
        if ($this->getHandler() != self::HANDLER_DB) {
            return;
        }

        $dbhw = $this->getApp()->getDatabase()->getHandler('writeFront');
        $query = new Kernel\Db\Query('TRUNCATE TABLE @', array(), $this);
        $dbhw->sendQuery($query);
    }

    /**
     * @param int $type
     * @return string
     */
    public function getErrorPriority($type = 1)
    {
        switch($type) {
            case E_ERROR: // 1 //
                return 'ERROR';
            case E_WARNING: // 2 //
                return 'WARNING';
            case E_PARSE: // 4 //
                return 'PARSE';
            case E_NOTICE: // 8 //
                return 'NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'FATAL';
            case E_DEPRECATED: // 8192 //
                return 'DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'USER_DEPRECATED';
            default:
                return 'UNKOWN';
        }
    }
}
