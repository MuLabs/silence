<?php
namespace Mu\Kernel\Renderer;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
    const HANDLER_HTML = 'html';
    const HANDLER_JSON = 'json';
    const HANDLER_HJSON= 'hjson';

    protected $allowedHandlers   = array(self::HANDLER_HJSON, self::HANDLER_JSON, self::HANDLER_HTML);
    protected $supportedHandlers = array();
    protected $currentRender;
    protected $currentHandler;

    public function initialize()
    {
        $this->registerHandler("Mu\\Kernel\\Renderer\\Handler\\Html", self::HANDLER_HTML);
        $this->registerHandler("Mu\\Kernel\\Renderer\\Handler\\Json", self::HANDLER_JSON);
        $this->registerHandler("Mu\\Kernel\\Renderer\\Handler\\HtmlJson", self::HANDLER_HJSON);

        return true;
    }

    /**
     * @param string $class
     * @param string $type
     * @throws Exception
     */
    public function registerHandler($class, $type = self::HANDLER_HTML)
    {
        if (!in_array($type, $this->allowedHandlers)) {
            throw new Exception($type, Exception::TYPE_NOT_FOUND);
        }
        $this->supportedHandlers[$type] = $class;
    }

    /**
     * @return Handler
     */
    public function getHandler()
    {
        if (!$this->currentHandler) {
            $type = $this->getCurrentRender();
            $this->currentHandler = $this->getHandlerByType($type);
        }

        return $this->currentHandler;
    }

    /**
     * @return Handler
     */
    public function getHtmlHandler()
    {
        if ($this->currentRender == self::HANDLER_HTML) {
            return $this->getHandler();
        } else {
            return $this->getHandlerByType(self::HANDLER_HTML);
        }
    }

    /**
     * @return string
     */
    public function getCurrentRender()
    {
        if (!$this->currentRender) {
            $request    = $this->getApp()->getHttp()->getRequest();
            $httpAccept = $request->getHttpAccept();
            $contentType= $request->getContentType();

            if (preg_match('#application\/json#', $httpAccept) && preg_match('#hjson#', $contentType)) {
                $this->currentRender = self::HANDLER_HJSON;
            } else if (preg_match('#application\/json#', $httpAccept)) {
                $this->currentRender = self::HANDLER_JSON;
            } else {
                $this->currentRender = self::HANDLER_HTML;
            }
        }

        return $this->currentRender;
    }

    /**
     * @param string $type
     * @return Handler
     * @throws Exception
     */
    private function getHandlerByType($type = self::HANDLER_HTML)
    {
        if (!in_array($type, array_keys($this->supportedHandlers))) {
            throw new Exception($type, Exception::TYPE_NOT_FOUND);
        }

        $class = $this->supportedHandlers[$type];
        return new $class();
    }
}