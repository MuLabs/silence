<?php
namespace Mu\Kernel\Route\Dumper;

use Mu\Kernel;

class Nginx extends Kernel\Route\Dumper
{
    public function dumpSites($sites)
    {
        $siteService = $this->getApp()->getSiteService();
        $localizationService = $this->getApp()->getLocalizationService();
        $langList = $localizationService->getSupportedLanguages();

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

            $content .= "\tlocation /favicon.ico {\n";
            $content .= "\t\trewrite ^/favicon.ico$ /favicon.ico break;\n";
            $content .= "\t}\n\n";

            $content .= "\tlocation /robots.txt {\n";
            $content .= "\t\trewrite ^/robots.txt$ /robots.txt break;\n";
            $content .= "\t}\n\n";
            $content .= "}\n\n";
        }

        foreach ($sites as $siteId) {
            $siteUrl = str_replace(
                array(
                    'http://',
                    'https://'
                ),
                '',
                $siteService->getSiteUrl($siteId)
            );
            $siteService->setCurrentSite($siteId);

            $content .= "server {\n";
            $content .= "\tlisten       80;\n";
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
                foreach ($langList as $oneLang => $unused) {
                    $content .= $this->dumpRoutes($this->getApp()->getRouteManager()->getRoutes(true), $oneLang);
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
        }

        file_put_contents(PUBLIC_PATH . '/' . $this->getApp()->getName() . '.conf', $content);
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
}