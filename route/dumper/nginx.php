<?php
namespace Mu\Kernel\Route\Dumper;

use Mu\Kernel;

class Nginx extends Kernel\Route\Dumper
{
    public function dumpSites($sites)
    {
        $siteService = $this->getApp()->getSiteService();
        $content = '';
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

            $content .= $this->dumpRoutes($this->getApp()->getRouteManager()->getRoutes(true));

            $content .= "\t\trewrite ^xhprof/(.*)$ /xhprof/index.php$1 break;\n";
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
    protected function dumpRoutes($routes)
    {
        $content = '';
        foreach ($routes as $route) {
            $infos = $this->prepareRuleVars($route);
            $content .= "\t\trewrite " . $infos['pattern'] . ' ' . $infos['dest'] . " break;\n";
        }

        return $content;
    }
}