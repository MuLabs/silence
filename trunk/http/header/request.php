<?php
namespace Mu\Kernel\Http\Header;

use Mu\Kernel;

class Request
{
    const OS_OTHER = 1;
    const OS_WINDOWS = 2;
    const OS_MAC = 3;

    /**
     * @return string
     */
    public function getAccept()
    {
        return isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '*/*';
    }

    /**
     * @return string
     */
    public function getAcceptEncoding()
    {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
    }

    /**
     * @return array
     */
    public function getAcceptLanguage()
    {
        $acceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        if (!strlen($acceptedLanguage)) {
            return array();
        }

        $acceptedLanguage = explode(',', $acceptedLanguage);
        $finalAcceptedLanguage = array();
        foreach ($acceptedLanguage as $oneLang) {
            $oneLang = explode(';', $oneLang);
            $arrayLen = count($oneLang);

            if ($arrayLen == 1) {
                $strLang = reset($oneLang);
                $quality = 1;
            } elseif ($arrayLen == 2) {
                $strLang = reset($oneLang);
                $quality = (float)str_replace('q=', '', next($oneLang));
            } else {
                continue;
            }

            $finalAcceptedLanguage[$strLang] = $quality * 100;
        }

        return $finalAcceptedLanguage;
    }

    /**
     * @return string
     */
    public function getConnection()
    {
        return isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * @return string
     */
    public function getHttps()
    {
        return isset($_SERVER['HTTP_HTTPS']) ? $_SERVER['HTTP_HTTPS'] : false;
    }

    /**
     * @return int
     */
    public function getOS()
    {
        $ua = $this->getUserAgent();
        if (preg_match('#windows#i', $ua)
            || preg_match('#cygwin_nt#i', $ua)
            || preg_match('#os\/2#i', $ua)
        ) {
            return self::OS_WINDOWS;
        } else {
            if (preg_match('#mac\sos#i', $ua)
                || preg_match('#mac_powerpc#i', $ua)
                || preg_match('#macintosh#i', $ua)
            ) {
                return self::OS_MAC;
            } else {
                return self::OS_OTHER;
            }
        }
    }
}
