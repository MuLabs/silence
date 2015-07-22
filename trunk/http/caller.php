<?php
namespace Mu\Kernel\Http;

use Mu\Kernel;

class Caller extends Kernel\Service\Core
{
    protected $curlHandler;

    public function __construct()
    {

    }

    /**
     * @param string $url
     * @param array $params
     * @param bool|false $post
     * @param bool|false $bHeader
     * @param string $cookie
     * @param bool|false $returnDetail
     * @return array|mixed
     */
    public function call($url, $params = array(), $post = false, $bHeader = false, $cookie = '', $returnDetail = false) {
        if (isset($params['header'])) {
            $headers = $params['header'];
            unset($params['header']);
        } else {
            $headers = array(
                'Accept:text/html,application/xhtml+xml',
                'Accept-Language:en-US,en;q=0.8,fr;q=0.6',
                'Cache-Control:max-age=0',
                'Connection:keep-alive',
                'Content-Type: application/x-www-form-urlencoded'
            );
        }

        if (isset($params['referer'])) {
            curl_setopt($this->curlHandler, CURLOPT_REFERER, $params['referer']);
            unset($params['referer']);
        }

        if (isset($params['agent'])) {
            $agent = $params['agent'];
            unset($params['agent']);
        } else {
            $agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/34.0.1847.116 Chrome/34.0.1847.116';
        }

        // Initialize request:
        if (!isset($this->curlHandler)) {
            $this->curlHandler = curl_init();
            curl_setopt($this->curlHandler, CURLOPT_URL, $url);
            curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $headers);
        }
        $curl = $this->curlHandler;

        // Set parameters:
        curl_setopt_array ($curl, array(
            CURLOPT_HEADER => (int)$bHeader,
            CURLOPT_USERAGENT => $agent,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ));

    // Add cookie
        if (!empty($cookie)) {
            curl_setopt_array($curl, array(
                CURLOPT_COOKIEJAR => $cookie,
                CURLOPT_COOKIEFILE => $cookie
            ));
        }

    // Add post and parameters:
        if (!empty($params)) {
            if ($post) {
                curl_setopt_array($curl, array(
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $params,
                ));
            } else {
                $url .= '?' . http_build_query($params);
                curl_setopt($curl, CURLOPT_URL, $url);
            }
        }

        // Return the response:
        $result = curl_exec($curl);

        if ($returnDetail) {
            $header_len = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            $header = substr($result, 0, $header_len);
            $body = substr($result, $header_len);

            $result = array(
                'header' => $header,
                'body'   => $body,
            );
        } else {
            $result = str_replace('\'', '"', $result);
        }

        $this->close();

        return $result;
    }

    public function __destruct()
    {
        $this->close();
    }

    protected function close() {
        if (isset($this->curlHandler)) {
            curl_close($this->curlHandler);
            $this->curlHandler = null;
        }
    }
}
