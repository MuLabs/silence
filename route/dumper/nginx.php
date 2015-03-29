<?php
namespace Mu\Kernel\Route\Dumper;

use Mu\Kernel;

class Nginx extends Kernel\Route\Dumper
{
    const DEFAULT_PATH = '/etc/nginx/';
    const DEFAULT_ENABLED_DIR = 'sites-enabled';
    const DEFAULT_AVAILABLE_DIR = 'sites-available';

    /**
     * @param string $content
     */
    public function moveFile($content) {
        $path = '';

        while (!is_dir($path) || !is_dir($path.self::DEFAULT_AVAILABLE_DIR) || !is_dir($path.self::DEFAULT_ENABLED_DIR)) {
            $path = \Mu\ask('Nginx path ['.self::DEFAULT_PATH.'] : ');
            if (empty($path)) {
                $path = self::DEFAULT_PATH;
            }
        }

        $file = $path.self::DEFAULT_AVAILABLE_DIR.'/'.$this->getApp()->getName() . '.conf';
        file_put_contents($file, $content);

        if ('n' != strtolower(\Mu\ask('Create enabled symlink [Y/n] : '))) {
            $symlink = $path.self::DEFAULT_ENABLED_DIR.'/'.$this->getApp()->getName();
            if (file_exists($path.self::DEFAULT_ENABLED_DIR.'/'.$this->getApp()->getName())) {
                unlink($symlink);
            }
            exec('ln -s ' . $file . ' ' . $symlink);
        }

        if ('n' != strtolower(\Mu\ask('Restart Nginx (root only) [Y/n] : '))) {
            echo 'Testing configuration : ';
            echo exec('/etc/init.d/nginx configtest');
            echo "\n";
            echo 'Restarting nginx :';
            echo exec('/etc/init.d/nginx restart');
            echo "\n";
        }

    }

    /**
     * @param array $sites
     */
    public function dumpSites($sites)
    {
        $statics = $this->getApp()->getStaticList();
        $content = '';
        foreach ($statics as $oneStatic) {
            $oneStatic = str_replace(
                array(
                    'http://',
                    'https://'
                ),
                '',
                $oneStatic
            );

            $content .= "server {\n";
            $content .= "\tlisten       80;\n";
            $content .= "\tserver_name $oneStatic;\n";
            $content .= "\troot " . APP_STATIC_PATH . ";\n";

            $content .= "\tlocation ~* \\.(eot|ttf|woff|woff2)$ {\n";
            $content .= "\t\tadd_header Access-Control-Allow-Origin *;\n";
            $content .= "\t}\n\n";

            $content .= "\tlocation /favicon.ico {\n";
            $content .= "\t\trewrite ^/favicon.ico$ /favicon.ico break;\n";
            $content .= "\t}\n\n";
            $content .= "}\n\n";
        }

        $cacheService = $this->getApp()->getPageCache();
        if ($cacheService !== false) {
            $cacheHost = $cacheService->getHandler()->getHost();
            $cachePort = $cacheService->getHandler()->getPort();
            $content .= "upstream redisPool {\n";
            $content .= "\tserver       $cacheHost:$cachePort;\n";
            $content .= "\tkeepalive 512;\n";
            $content .= "}\n\n";
        }

        foreach ($sites as $siteId) {
            $content .= $this->dumpOneSite($siteId);
        }

        $this->moveFile($content);
    }

    protected function dumpOneSite($siteId) {
        $content = '';
        $siteService = $this->getApp()->getSiteService();
        $pageCacheService = $this->getApp()->getPageCache();
        $localizationService = $this->getApp()->getLocalizationService();
        $langList = $localizationService->getSupportedLanguages();

        $siteUrl = str_replace(
            array(
                'http://',
                'https://'
            ),
            '',
            $siteService->getSiteUrl($siteId)
        );
        $siteService->setCurrentSite($siteId);

        if ($pageCacheService !== false) {
            $dumpableRoutes = array();
            foreach ($this->getApp()->getRouteManager()->getRoutes(true) as $oneRoute) {
                if ($oneRoute->hasDumpCache()) {
                    $dumpableRoutes[] = $oneRoute;
                }
            }

            $hasDumpCache = (bool)count($dumpableRoutes);
            if ($hasDumpCache) {
                $content .= "server {\n";
                $content .= "\tlisten       80;\n";
                $content .= "\tserver_name $siteUrl;\n";

                $content .= "\tlocation @fallback {\n";
                $content .= "\t\tproxy_pass ".$siteService->getSiteUrl($siteId).":8001;\n";
                $content .= "\t}\n\n";

                foreach ($dumpableRoutes as $oneRoute) {
                    if (empty($langList)) {
                        $content .= $this->dumpCacheRoute($oneRoute);
                    } else {
                        foreach ($langList as $oneLang => $unused) {
                            $content .= $this->dumpCacheRoute($oneRoute, $oneLang);
                        }
                    }

                }

                $content .= "}\n\n";
            }
        } else {
            $hasDumpCache = false;
        }

        $content .= "server {\n";
        if ($hasDumpCache) {
            $content .= "\tlisten       8001;\n";
        } else {
            $content .= "\tlisten       80;\n";
        }
        $content .= "\tserver_name $siteUrl;\n";
        $content .= "\troot " . PUBLIC_PATH . ";\n";

        $content .= "\tlocation /favicon.ico {\n";
        $content .= "\t\trewrite ^/favicon.ico$ /favicon.ico break;\n";
        $content .= "\t}\n\n";

        $content .= "\tlocation /robots.txt {\n";
        $content .= "\t\trewrite ^/robots.txt$ /robots.txt break;\n";
        $content .= "\t}\n\n";

        $content .= "\tlocation / {\n";
        $content .= "\t\tindex index.php;\n";
        $content .= "\t\trewrite ^\\/xhprof\\/(.*)$ /xhprof/index.php$1 break;\n";

        if ($siteId) {
            if (empty($langList)) {
                $content .= $this->dumpRoutes($this->getApp()->getRouteManager()->getRoutes(true));
            } else {
                foreach ($langList as $oneLang => $unused) {
                    $content .= $this->dumpRoutes($this->getApp()->getRouteManager()->getRoutes(true), $oneLang);
                }
            }
        } else {
            $content .= $this->dumpRoutes($this->getApp()->getRouteManager()->getRoutes(true));
        }

        $content .= "\t\trewrite ^.*$ /index.php break;\n\n";
        $content .= "\t\tfastcgi_pass   127.0.0.1:9001;\n";
        $content .= "\t\tfastcgi_index  index.php;\n";
        $content .= "\t\t" . 'fastcgi_param  SCRIPT_FILENAME   $document_root$fastcgi_script_name;' . "\n";
        $content .= "\t\tinclude        fastcgi_params;\n";
        $content .= "\t}\n";
        $content .= "}\n\n";

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    protected function dumpRoutes($routes, $lang = null)
    {
        $content = '';
        foreach ($routes as $route) {
            $infos = $this->prepareRuleVars($route, $lang);
            $content .= "\t\trewrite " . $infos['pattern'] . ' ' . $infos['dest'] . " break;\n";
        }

        return $content;
    }


    protected function dumpCacheRoute(Kernel\Route\Route $route, $lang = null)
    {
        $cacheService = $this->getApp()->getPageCache();
        $dumpCacheKey = $cacheService->getRealKey($route->getDumpCacheKey());

        $content = '';
        $infos = $this->prepareRuleVars($route, $lang);
        $content .= "\tlocation ~".$infos['pattern']." {\n";
        $content .= "\t\tset \$redis_key \"$dumpCacheKey\";\n";
        $content .= "\t\tredis_pass     redisPool;\n";
        $content .= "\t\tredis_read_timeout 5;\n";
        $content .= "\t\tredis_connect_timeout 5;\n";
        $content .= "\t\tdefault_type   text/html;\n";
        $content .= "\t\terror_page     404 502 504 = @fallback;\n";
        $content .= "\t}\n\n";

        return $content;
    }
}