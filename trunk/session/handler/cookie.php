<?php
namespace Mu\Kernel\Session\Handler;

use Mu\Kernel;

/**
 * Cookie Handler ::
 * Manage cookies with contexts and use 2 protected keys to check validity of the user cookie
 *
 * Configuration:
 * [ses_cookie]
 * ; context = expire(in hour),secure(int),httponly(int)
 * murloc = 24,0,0
 *
 * @package Mu\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Cookie extends Kernel\Session\Handler
{
    const DEFAULT_EXPIRE = 12;
    const DEFAULT_HTTPONLY = false;
    const DEFAULT_SALT = '67e04b7bbbcbfbe9ebc84f7d29fdb0bc';
    const DEFAULT_SECURE = false;
    const COOKIE_PREFIX = 'mu_';

    protected $keyVerify = 'mu_verify';
    protected $salt;
    protected $secure; // Bool
    protected $httponly; // Bool
    protected $info = array();
    protected $disableRefresh = false;
    protected $saved = false;

    /**
     * Load current cookie and check it's validity
     */
    public function __init()
    {
        // Initialize configuration:
        $this->salt = $this->getConfig('salt', self::DEFAULT_SALT);
        $this->setExpire($this->getConfig('expire', self::DEFAULT_EXPIRE));
        $this->secure = $this->getConfig('secure', self::DEFAULT_SECURE);
        $this->httponly = $this->getConfig('httponly', self::DEFAULT_HTTPONLY);

        // Get cookie:
        $cookie = $this->__getCookie();

        // Test cookie validity if not empty
        if (is_array($cookie)) {
            // Test validity:
            if (!isset($cookie[$this->keyVerify]) || $cookie[$this->keyVerify] != $this->getId()) {
                $this->clean();
                return;
            }

            // Store infos:
            $this->info = $cookie;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __close()
    {
        if (!$this->disableRefresh) {
            $this->save();
        }
    }

    public function clean() {
        parent::clean();

        $this->remove();
    }

    public function remove() {
        setcookie($this->getCookieName(), null, -1, '/');
        $this->saved = true;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        // Don't save if already saved
        if ($this->saved) {
            return;
        }

        // Test if header has been already sent, in this case cookie couldn't be saved:
        if (headers_sent()) {
            return; // TODO Add log
        }

        // Set protected keys:
        if (count($this->info)) {
            $this->info[$this->keyVerify] = $this->getId();

            // Save values:
            $expire = time() + $this->getExpire();
            $value = $this->__cryptCookie($this->info);
            setcookie(
                $this->getCookieName(),
                $value,
                $expire,
                '/',
                '',
                $this->secure,
                $this->httponly
            );
            $this->saved = true;
        }
    }

    /**
     * @return string
     */
    public function getCookieName() {
        return self::COOKIE_PREFIX.$this->getContext();
    }

    public function disableRefresh()
    {
        $this->disableRefresh = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function parseConfig(array $config = array())
    {
        return array(
            'expire' => (isset($config[0])) ? $config[0] : self::DEFAULT_EXPIRE,
            'secure' => (isset($config[1]) && $config[1] == 1),
            'httponly' => (isset($config[2]) && $config[2] == 1)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $default = null)
    {
        return (isset($this->info[$name])) ? $this->info[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll()
    {
        return $this->info;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return substr(md5($this->getContext() . '--' . $this->salt), 3, 6);
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value = null)
    {
        // Do not overload protected keys
        if ($name != $this->keyVerify) {
            $this->info[$name] = $value;
            $this->saved = false;
        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function setAll($values = array())
    {
        $this->info = $values;
        $this->saved = false;
    }

    /**
     * Get correct cookie
     * @return array
     */
    private function __getCookie()
    {
        $cookie = $this->getApp()->getHttp()->getRequest()->getParameters(
            $this->getCookieName(),
            Kernel\Http\Request::PARAM_TYPE_COOKIE,
            false
        );

        return $cookie !== false ? $this->__decryptCookie($cookie) : null;
    }

    /**
     * @param mixed $content
     * @return string
     */
    public function __cryptCookie($content) {
        // JSON encode for objects and array
        $content = json_encode($content);

        // Hash
        $key = hash('sha256', self::DEFAULT_SALT);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', self::DEFAULT_EXPIRE), 0, 16);

        $output = openssl_encrypt($content, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($output);
    }

    /**
     * @param string $content
     * @return mixed
     */
    public function __decryptCookie($content) {
        if (!is_string($content)) {
            $this->clean();
            return null;
        }

        // Hash
        $key = hash('sha256', self::DEFAULT_SALT);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', self::DEFAULT_EXPIRE), 0, 16);

        $output = openssl_decrypt(base64_decode($content), 'AES-256-CBC', $key, 0, $iv);

        return json_decode($output, true);
    }
}