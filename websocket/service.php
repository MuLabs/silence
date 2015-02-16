<?php
namespace Mu\Kernel\Websocket;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
    const WEBSOCKET_MAGIC_KEY = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    const BUFFER_LENGTH = 4096;
    const STATUS_STARTED = 1;
    const STATUS_PAUSED = 2;
    const STATUS_STOPPED = 3;

    protected $master;
    protected $host = 'localhost';
    protected $port = 8000;
    protected $callbacks = array();
    protected $connectCallbacks = array();
    protected $disconnectCallbacks = array();
    protected $clients = array();
    protected $status = self::STATUS_STARTED;
    protected $logCallback;

    /**
     * @return string
     */
    public function getWebsocketMagicKey()
    {
        return self::WEBSOCKET_MAGIC_KEY;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return callable|null
     */
    public function getLogCallback()
    {
        return $this->logCallback;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
    }

    /**
     * @param callable $callback
     */
    public function setLogCallback($callback)
    {
        $this->logCallback = $callback;
    }

    /**
     * @return array
     */
    public function getConnectCallbacks()
    {
        return $this->connectCallbacks;
    }

    /**
     * @return array
     */
    public function getDisconnectCallbacks()
    {
        return $this->disconnectCallbacks;
    }

    /**
     * @param string $type
     * @return callable
     */
    public function getTypeCallback($type)
    {
        if (!isset($this->callbacks[$type])) {
            return null;
        }

        return $this->callbacks[$type];
    }

    /**
     * @param callable $function
     */
    public function registerConnectCallback($function)
    {
        if (is_callable($function)) {
            $this->connectCallbacks[] = $function;
        }
    }

    /**
     * @param callable $function
     */
    public function registerDisconnectCallback($function)
    {
        if (is_callable($function)) {
            $this->disconnectCallbacks[] = $function;
        }
    }

    /**
     * @param string $action
     * @param callable $function
     */
    public function registerTypeCallback($action, $function)
    {
        if (!is_callable($function)) {
            return;
        }

        $this->callbacks[$action] = $function;
    }

    public function __destruct()
    {
        if ($this->master) {
            socket_close($this->master);
        }
    }

    /**
     * Start websocket
     */
    public function start()
    {
        $this->connectSocket();

        while ($this->status != self::STATUS_STOPPED) {
            if ($this->status == self::STATUS_STARTED) {
                $messages = $this->getMessages();
                foreach ($messages as $oneMessage) {
                    $content = $oneMessage['content'];

                    $action = $this->getTypeCallback($content['type']);
                    if (!empty($action)) {
                        call_user_func($action, $this, $oneMessage['from'], $oneMessage['content']);
                    }
                }
                /*unset($messages);
                gc_collect_cycles();*/
            }
            sleep(1);
        }
    }

    public function connectSocket()
    {
        if ($this->master) {
            return;
        }

        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->master, $this->getHost(), $this->getPort());
        socket_listen($this->master);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        /** @var array $changed */

        $loop = 0;
        while (true) {
            sleep(1);
            ++$loop;
            $changed = $this->clients;
            $changed[] = $this->master;
            socket_select($changed, $null, $null, 0, 10);

            if ($loop == 60) {
                $loop = 0;
                foreach ($this->clients as $socket) {
                    $content = socket_read($socket, self::BUFFER_LENGTH);
                    if ($content === "" || $content === false) {
                        $this->disconnectClient($socket);
                        continue;
                    }
                }
            }

            if (empty($changed)) {
                continue;
            }

            foreach ($changed as $socket) {
                if ($socket == $this->master) {
                    $this->connectClient();
                    continue;
                }

                $content = socket_read($socket, self::BUFFER_LENGTH);
                if ($content === "" || $content === false) {
                    $this->disconnectClient($socket);
                    continue;
                }

                $this->writeLog('Received updated clients');
                $receivedText = $this->unmask($content);
                $this->writeLog('Message text : ' . $receivedText);

                $messages[] = array(
                    'from' => $socket,
                    'content' => json_decode($receivedText, true)
                );
                return $messages;
            }
        }
        return array();
    }

    /**
     * Connect a new client socket
     */
    public function connectClient()
    {
        $this->writeLog('Accept new client');
        $socketNew = socket_accept($this->master);
        $this->clients[] = $socketNew;

        $header = socket_read($socketNew, self::BUFFER_LENGTH);
        $this->performHandshaking($header, $socketNew, $this->getHost(), $this->getPort());

        $callbacks = $this->getConnectCallbacks();
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this, $socketNew);
        }
    }

    public function disconnectClient($client)
    {
        $callbacks = $this->getDisconnectCallbacks();
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this, $client);
        }

        socket_close($client);
        $key = array_search($client, $this->clients);
        unset($this->clients[$key]);
        unset($client);
    }

    /**
     * @param string $msg
     * @param array $clients
     * @return bool
     */
    public function sendMessage($msg, $clients = null)
    {
        if ($clients === null) {
            $clients = $this->clients;
        } else {
            if (!is_array($clients)) {
                $clients = array($clients);
            }
        }

        $response = $this->mask(json_encode($msg));
        foreach ($clients as $changedSocket) {
            socket_write($changedSocket, $response, strlen($response));
        }
        return true;
    }

    /**
     * @param string $msg
     * @return string
     */
    private function unmask($msg)
    {
        $length = ord($msg[1]) & 127;
        if ($length == 126) {
            $masks = substr($msg, 4, 4);
            $data = substr($msg, 8);
        } elseif ($length == 127) {
            $masks = substr($msg, 10, 4);
            $data = substr($msg, 14);
        } else {
            $masks = substr($msg, 2, 4);
            $data = substr($msg, 6);
        }

        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    /**
     * @param string $msg
     * @return string
     */
    private function mask($msg)
    {
        // 0x1 text frame (FIN + opcode)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($msg);

        $header = '';
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < self::BUFFER_LENGTH) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= self::BUFFER_LENGTH && $length <= pow(2, 63)) {
            // some code for 64 bit byte integer
            list($lower, $upper) = $this->get64Bit($length);
            $header = pack('CCNN', $b1, 127, $upper, $lower);
        }
        return $header . $msg;
    }

    protected final function get64Bit($value)
    {
        $BIGINT_DIVIDER = 0x7fffffff + 1;
        $lower = intval($value % $BIGINT_DIVIDER);
        $upper = intval(($value - $lower) / $BIGINT_DIVIDER);
        return array($lower, $upper);
    }

    /**
     * @param $receivedHeader
     * @param $clientConn
     */
    private function performHandshaking($receivedHeader, $clientConn)
    {
        $headers = array();
        $lines = preg_split("/\r\n/", $receivedHeader);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey    = (isset($headers['Sec-WebSocket-Key'])) ? $headers['Sec-WebSocket-Key'] : '';
        $secAccept = base64_encode(pack('H*', sha1($secKey . $this->getWebsocketMagicKey())));
        //hand shaking header
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $this->host\r\n" .
            "WebSocket-Location: ws://$this->host:$this->port/\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        socket_write($clientConn, $upgrade, strlen($upgrade));
    }

    /**
     * @param string $message
     */
    public function writeLog($message)
    {
        $callback = $this->getLogCallback();
        if (is_callable($callback)) {
            call_user_func($callback, $message);
        }
    }
}