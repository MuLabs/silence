<?php
namespace Mu\Kernel;

use Mu\Kernel;

class Toolbox extends Service\Core
{
    const CURRENT_VERSION = '$VERSION';

    const PREFIX_PASS_HASH = '54qsd';
    const SUFFIX_PASS_HASH = '32HfD';

    const INCH_TO_CM = 2.54;

    protected $bannedKeywords = array(
        '10eme',
        '1er',
        '1ere',
        '2eme',
        '3eme',
        '4eme',
        '5eme',
        '6eme',
        '7eme',
        '8eme',
        '9eme',
        'abord',
        'afin',
        'ai',
        'ainsi',
        'ais',
        'ait',
        'alors',
        'apres',
        'as',
        'assez',
        'au',
        'aucun',
        'aucune',
        'aupres',
        'auquel',
        'auquelles',
        'auquels',
        'auraient',
        'aurais',
        'aurait',
        'aurez',
        'auriez',
        'aurions',
        'aurons',
        'auront',
        'aussi',
        'aussitot',
        'autant',
        'autre',
        'autres',
        'aux',
        'avaient',
        'avais',
        'avait',
        'avant',
        'avec',
        'avez',
        'aviez',
        'avoir',
        'avons',
        'ayant',
        'beaucoup',
        'car',
        'ce',
        'ceci',
        'cela',
        'celle',
        'celles',
        'celui',
        'cependant',
        'certes',
        'ces',
        'cet',
        'cette',
        'ceux',
        'chacun',
        'chacune',
        'chaque',
        'chez',
        'cinq',
        'comme',
        'comment',
        'crois',
        'd',
        'dans',
        'de',
        'deca',
        'dehors',
        'deja',
        'dela',
        'depuis',
        'des',
        'dessous',
        'dessus',
        'deux',
        'dix',
        'doit',
        'donc',
        'donne',
        'dont',
        'du',
        'duquel',
        'durant',
        'elle',
        'elles',
        'eme',
        'en',
        'encore',
        'enfin',
        'entre',
        'er',
        'est',
        'et',
        'etaient',
        'etais',
        'etait',
        'etant',
        'etc',
        'etes',
        'etiez',
        'etions',
        'etre',
        'eu',
        'eurent',
        'eut',
        'eux',
        'faire',
        'fait',
        'fais',
        'faites',
        'faut',
        'fur',
        'furent',
        'grace',
        'hormis',
        'hors',
        'ici',
        'il',
        'ils',
        'je',
        'jusqu',
        'l',
        'la',
        'laquelle',
        'le',
        'lequel',
        'les',
        'lesquels',
        'leur',
        'leurs',
        'lors',
        'lorsque',
        'lui',
        'mais',
        'malgre',
        'me',
        'melle',
        'meme',
        'memes',
        'mes',
        'mien',
        'mienne',
        'miennes',
        'miens',
        'mm',
        'mme',
        'moi',
        'moins',
        'moment',
        'mon',
        'mr',
        'neanmoins',
        'neuf',
        'ni',
        'non',
        'nos',
        'notamment',
        'notre',
        'notres',
        'nous',
        'on',
        'ont',
        'ou',
        'oui',
        'par',
        'parce',
        'parfois',
        'parmi',
        'partout',
        'pas',
        'pendant',
        'peu',
        'peut',
        'peux',
        'plus',
        'plutot',
        'pour',
        'pourquoi',
        'pouvons',
        'pres',
        'puis',
        'puisqu',
        'puisque',
        'quand',
        'quant',
        'quatre',
        'que',
        'quel',
        'quelle',
        'quelles',
        'quelqu',
        'quelque',
        'quelquefois',
        'quelques',
        'quelquun',
        'quels',
        'qui',
        'quoi',
        'quot',
        'sa',
        'sans',
        'sauf',
        'se',
        'selon',
        'sept',
        'sera',
        'serai',
        'seraient',
        'serais',
        'serait',
        'seras',
        'serez',
        'seriez',
        'serions',
        'serons',
        'seront',
        'ses',
        'si',
        'sien',
        'sienne',
        'siennes',
        'siens',
        'sitot',
        'soi',
        'soit',
        'sommes',
        'son',
        'sont',
        'sous',
        'souvent',
        'suis',
        'sur',
        'tandis',
        'tant',
        'tes',
        'tienne',
        'tiennes',
        'tiens',
        'toi',
        'ton',
        'toujours',
        'utilise',
        'tous',
        'tout',
        'toute',
        'toutefois',
        'toutes',
        'trop',
        'tu',
        'un',
        'une',
        'unes',
        'uns',
        'voici',
        'voila',
        'voir',
        'vos',
        'votre',
        'votres',
        'vous'
    );

    /************************************************************************************
     **  ARRAY                                                                       **
     ************************************************************************************/
    #region ARRAY
    /**
     * @return array
     */
    public function createArray()
    {
        $args = func_get_args();
        $array = array();
        $i = 0;
        while (isset($args[$i * 2])) {
            $array[$args[$i * 2]] = $args[$i * 2 + 1];
            ++$i;
        }

        return $array;
    }
    #endregion

    /************************************************************************************
     **  DATE                                                                       **
     ************************************************************************************/
    #region DATE
    /**
     * @param string $date
     * @return int
     */
    public function daysFromNow($date)
    {
        $time = strtotime(date('Y-m-d 00:00:00'));
        return floor((strtotime($date) - $time) / 86400);
    }

    /**
     * @param string $date
     * @return int
     */
    public function minutesFromNow($date)
    {
        $time = strtotime($date);
        $currentTime = time();

        $hour = (int)date('H', $time) * 3600 + (int)date('i', $time) * 60;
        $currentHour = (int)date('H', $currentTime) * 3600 + (int)date('i', $currentTime) * 60;
        return ($hour - $currentHour) / 60;
    }

    #endregion

    /************************************************************************************
     **  ENCRYPTION                                                                       **
     ************************************************************************************/
    #region ENCRYPTION
    /**
     * @param string $key
     * @return string
     */
    public function sha1Encode($key)
    {
        return sha1(self::PREFIX_PASS_HASH . $key . self::SUFFIX_PASS_HASH);
    }

    /**
     * Generate a random string using sha1Encode
     * Replace generate_activation_key and generate_random_string
     * @param int $length
     * @return string
     */
    public function generateRandomKey($length = 10)
    {
        $key = $this->sha1Encode(time() . uniqid());
        $start = rand(0, strlen($key) - $length - 1);
        return substr($key, $start, $length);
    }

    #endregion

    /************************************************************************************
     **  FILE                                                                       **
     ************************************************************************************/
    #region FILE
    /**
     * Send response in json
     * @param string $content
     */
    public function jsonPage($content)
    {
        $response = $this->getApp()->getHttp()->getResponse();
        $response->getHeader()->setContentType(Kernel\Http\Header\Response::MIME_TYPE_JSON);
        $response->setContent(json_encode($content));
        $response->send();
    }

    /**
     * @param string $dir
     * @param bool $onlyDir
     */
    public function recursiveRmdir($dir, $onlyDir = false)
    {
        if (file_exists($dir) && $dh = opendir($dir)) {
            while (($filename = readdir($dh)) !== false) {
                if ($filename != '.' && $filename != '..') {
                    $file = $dir . '/' . $filename;
                    if (is_dir($file)) {
                        $this->recursiveRmdir($file, $onlyDir);
                    } elseif (!$onlyDir) {
                        @unlink($file);
                    }
                }
            }
            closedir($dh);
            @rmdir($dir);
        }
    }

    #endregion

    /************************************************************************************
     **  MATHS                                                                       **
     ************************************************************************************/
    #region MATHS
    /**
     * @param float $value
     * @return float
     */
    public function inchToCm($value)
    {
        return $value * self::INCH_TO_CM;
    }

    /**
     * @param float $value
     * @return float
     */
    public function cmToInch($value)
    {
        return (int)number_format($value / self::INCH_TO_CM, 0, ',', ' ');
    }

    /**
     * @param int $base
     * @param int $percent
     * @return int
     */
    public function percentFromValue($base, $percent)
    {
        return (int)(($base * $percent) / 10000);
    }

    /**
     * @param int $base
     * @param int $value
     * @return int
     */
    public function valueFromPercent($base, $value)
    {
        return (int)(($value / $base) * 10000);
    }

    /**
     * Return the query part to select a distance compute between 2 points
     * @param int $latitude
     * @param int $longitude
     * @param string $fieldLat
     * @param string $fieldLon
     * @param bool $bRound
     * @param bool $bKm
     * @return string
     */
    public function getDistanceBetweenQuery($latitude = 0, $longitude = 0, $fieldLat = ':latitude', $fieldLon = ':longitude', $bRound = false, $bKm = false)
    {
        // Earth radius : distance from the Earth's center to its surface
        $meters = 6353000;
        if ($bKm) {
            $meters /= 1000;
        }

        // Set query:
        $query = "$meters * 2 * ASIN( SQRT(
            POWER(SIN(($latitude - abs($fieldLat)) * pi()/180 / 2),2) + COS($latitude * pi()/180) *
            COS(abs($fieldLat) *  pi()/180) * POWER(SIN(($longitude - $fieldLon) *  pi()/180 / 2), 2)
        ) )";

        // Round value:
        if ($bRound) {
            $query = "ROUND($query, 2)";
        }

        // Return query string:
        return "($query)";
    }

    #endregion

    /************************************************************************************
     **  STRING                                                                       **
     ************************************************************************************/
    #region STRING
    /**
     * @param string $string
     * @return string
     */
    public function cleanString($string)
    {
        return htmlspecialchars(trim($string), ENT_NOQUOTES);
    }

    public function arrayWithKeyToString($array)
    {
        return implode(', ', array_map(function ($v, $k) {
            return $k . '=' . $v;
        }, $array, array_keys($array)));
    }

    /**
     * Compare two string size
     * @param string $a
     * @param string $b
     * @return int
     */
    public function compareLength($a, $b)
    {
        $lowera = strtolower($a);
        $lowerb = strtolower($b);
        if ($lowera != $a && $lowerb == $b) {
            return -1;
        } elseif ($lowerb != $b && $lowera == $a) {
            return 1;
        }
        if (strlen($a) == strlen($b)) {
            return 0;
        }
        return (strlen($a) > strlen($b)) ? -1 : 1;
    }

    /**
     * Convert a decimal value into hexadecimal color
     * @param int $decimal
     * @return string
     */
    public function decimalToColor($decimal)
    {
        $color = dechex($decimal);
        // Check decimal length:
        if (strlen($color) < 6) {
            $color = str_repeat('0', 6 - strlen($color)) . "$color";
        }
        return '#' . $color;
    }

    /**
     * @return array|bool
     */
    public function multiSort()
    {
        //get args of the function
        $args = func_get_args();

        //return false if column and order not given in function
        $c = count($args);
        if ($c < 2) {
            return false;
        }

        //get the array to sort
        $array = array_splice($args, 0, 1);
        $array = $array[0];

        //get the type of sort : DESC or NOT
        $desc = array_splice($args, -1);
        $desc = $desc[0];

        //sort with an function giving in args
        usort(
            $array,
            function ($a, $b) use ($args) {
                $i = 0;
                $c = count($args);
                $cmp = 0;
                while ($cmp == 0 && $i < $c) {
                    if (!empty($args[$i])) {
                        $func = 'get' . ucfirst($args[$i]);
                        $cmp = strcmp($a->$func(), $b->$func());
                    }
                    $i++;
                }

                return $cmp;
            }
        );

        if ($desc) {
            return array_reverse($array);
        } else {
            return $array;
        }
    }

    /**
     * @return array
     */
    public function getBannedKeywords()
    {
        return $this->bannedKeywords;
    }

    /**
     * @param string $string
     * @return array
     */
    public function extractKeywords($string)
    {
        return explode('_', $this->rewriteString($string));
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @param string $string
     * @return string
     */
    public function getValidComparator($string)
    {
        switch ($string) {
            case '>=':
                $string = '>=';
                break;
            case '<=':
                $string = '<=';
                break;
            case '>':
                $string = '>';
                break;
            case '<':
                $string = '<';
                break;
            case '<>':
                $string = '<>';
                break;
            case '=':
            default :
                $string = '=';
                break;
        }

        return $string;
    }

    /**
     * @param $comp
     * @param $value
     * @return string
     */
    public function getValidLikeValue($comp, $value)
    {
        switch ($comp) {
            case '>':
                $string = '%' . $value;
                break;
            case '<':
                $string = $value . '%';
                break;
            case '<>':
            case '!=':
                $string = '%' . $value . '%';
                break;
            case '=':
            default :
                $string = $value;
                break;
        }

        return $string;
    }

    /**
     * @param string $text
     * @return string
     */
    public function htmlFormat($text)
    {
        return nl2br(htmlspecialchars($text));
    }

    /**
     * @param int $prefix
     * @param string $number
     * @return string
     */
    public function formatPhoneNumber($prefix, $number)
    {
        return (!empty($prefix)) ? '+' . (int)$prefix . ' ' . $number : $number;
    }

    /**
     * Replace each accent character by the correct character without it
     * @param string $string
     * @return string
     */
    public function replaceAccents($string)
    {
        $charset = mb_detect_encoding($string, 'UTF-8,ISO-8859-1,ISO-8859-15');
        $string = htmlentities($string, ENT_NOQUOTES, $charset);
        $string = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/u', '$1', $string);
        return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @param string $string
     * @param bool $wordFiltering
     * @return string
     */
    public function rewriteString($string, $wordFiltering = true)
    {
        $string = self::replaceAccents($string);
        $string = utf8_decode($string);
        $string = preg_replace(
            array('/(^|\W)\w{1,2}\'/', '/\'\w{1,2}(\W|$)/', '/\W+/', '/\-+/', '/\-+$/', '/^\-+/'),
            array('-', '-', '-', '-', '', ''),
            $string
        );
        $arrayStringLower = explode('-', strtolower($string));
        $arrayString = explode('-', $string);

        if ($wordFiltering) {
            $arrayStringLower = array_diff($arrayStringLower, self::getBannedKeywords());
            uasort($arrayStringLower, array($this, 'sizeCmp'));
            $j = 0;
            foreach ($arrayStringLower as $key => $content) {
                if ($content != '') {
                    $length = strlen($content);
                    // Cleaning keywords to avoid infinite URLs...
                    if ($j >= 3
                        || ($j >= 1 /* ensure have at least one string */
                            && (($length > 60 /* too big to be a real keyword! */
                                    || $length <= 1) /* too short for a keyword...*/
                                && !preg_match('/[^a-z]/', $arrayString[$key]) /* AND not a single char! */
                            )
                        )
                    ) {
                        unset($arrayString[$key]);
                    } else {
                        $j++;
                    }
                }
            }
        }
        $stringFinal = '';
        foreach ($arrayString as $key => $content) {
            if (isset($arrayStringLower[$key]) && trim($arrayString[$key]) != '') {
                $stringFinal .= $arrayStringLower[$key] . '-';
            }
        }
        if ($stringFinal != '') {
            $stringFinal = substr($stringFinal, 0, -1);
        } else {
            $stringFinal = 'default';
        }
        return urlencode($stringFinal);
    }

    /**
     * @param string|int $date
     * @param string $format
     * @return string
     */
    public function getConvertedDate($date = null, $format = 'fr')
    {
        // Get current timestamp:
        if (empty($date)) {
            $date = time();
        }

        // Convert date to time:
        if (!is_int($date)) {
            $date = strtotime($date);
        }

        if ($format == 'fr') {
            $format = '%e %B %Y';
        } elseif ($format == 'en') {
            $format = '%B %e %Y';
        }

        return strftime($format, $date);
    }

    /**
     * @static
     * @param string $a
     * @param string $b
     * @return int
     */
    public function sizeCmp($a, $b)
    {
        $lowera = strtolower($a);
        $lowerb = strtolower($b);
        if ($lowera != $a && $lowerb == $b) {
            return -1;
        } elseif ($lowerb != $b && $lowera == $a) {
            return 1;
        }
        if (strlen($a) == strlen($b)) {
            return 0;
        }
        return (strlen($a) > strlen($b)) ? -1 : 1;
    }

    /**
     * @param $string
     * @return array|mixed
     */
    public function realEscapeString($string)
    {
        if (is_array($string))
            return array_map(__METHOD__, $string);

        if (!empty($string) && is_string($string)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);
        }

        return trim($string);
    }

    /**
     * @param $string
     * @return Kernel\Model\Entity[]
     */
    public function getEntitiesFromAutocompleteString($string)
    {
        $string = trim($string);

        if (substr($string, -3) == '|,|') {
            $string = substr($string, 0, -3);
        }

        $objects = array_unique(explode('|,|', $string));

        $entities = array();
        foreach ($objects as $object) {
            $aObject = explode('|:|', $object);

            if (count($aObject) < 2) {
                continue;
            }
            list($type, $id) = $aObject;
            $entities[] = $this->getApp()->getModelManager()->getEntityFromTypeAndId($type, $id);
        }

        return $entities;
    }

    /**
     * @param Kernel\Model\Entity[] $entities
     * @return string
     */
    public function getAutocompleteStringFromEntities($entities)
    {
        $aEntity = array();
        foreach ($entities as $entity) {
            $aEntity[] = $entity . '|:|' . $entity->getEntityType() . '|:|' . $entity->getId();
        }

        $sEntity = implode('|,|', $aEntity);
        return $sEntity;
    }

    public function isValidEmail($email)
    {
        if (preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email))
            return true;
        else
            return false;
    }


    #endregion

    /************************************************************************************
     **  SYSTEM                                                                       **
     ************************************************************************************/
    #region SYSTEM
    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return self::CURRENT_VERSION;
    }

    /**
     * Get current browser name
     * @return string
     */
    public function getBrowser()
    {
        $userAgent = $this->getApp()->getHttp()->getRequest()->getParameters(
            'HTTP_USER_AGENT',
            Kernel\Http\Request::PARAM_TYPE_SERVER,
            ''
        );
        $testArray = array(
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        );

        foreach ($testArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }

        // Return default:
        return "Unknown Browser";
    }

    /**
     * Remove php memory and time limits
     */
    public function removeMemoryLimits()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1500M');
    }

    /**
     * Register the aulodoader
     * @param $autoload
     */
    public function registerAutoload($autoload)
    {
        if (is_callable($autoload)) {
            $autoloadFunctions = spl_autoload_functions();
            // Remove all autoload functions
            foreach ($autoloadFunctions as $oneAutoload) {
                spl_autoload_unregister($oneAutoload);
            }

            // Set this new function first autoload function
            spl_autoload_register($autoload);

            // Re-set all autoload functions
            foreach ($autoloadFunctions as $oneAutoload) {
                spl_autoload_register($oneAutoload);
            }
        }
    }

    /**
     * Prepare pagination
     *
     * @param int $total
     * @param int $nbPerPage
     * @param int $current
     * @param int $partSize
     * @return array
     */
    public function preparePagination($total, $nbPerPage, $current, $partSize = 3)
    {
        $before = floor(($partSize - 1) / 2);
        $after = $partSize - 1 - $before;
        if ($nbPerPage <= 0) {
            return array();
        }

        $nbPage = ceil($total / $nbPerPage);
        $return = array();
        if ($current > 1) {
            $return[] = array(
                'text' => '__first__',
                'number' => 1,
            );

            $return[] = array(
                'text' => '__prev__',
                'number' => $current - 1,
            );
        }

        $start1 = 1;
        $end1 = $partSize;
        $start2 = $nbPage - $partSize + 1;
        $end2 = $nbPage;
        $middle = true;
        $end = true;
        if ($current - $before - 1 <= $end1) {
            $end1 = max($current + $after, $end1);
            $middle = false;

            if ($current + $after + 1 >= $start2) {
                $end1 = $nbPage;
                $end = false;
            }
        }

        if ($current + $after + 1 >= $start2) {
            $start2 = min($current - $before, $start2);
            $middle = false;
        }

        for ($i = $start1; $i <= $end1; ++$i) {
            $return[] = array(
                'text' => $i,
                'number' => $i,
            );
        }

        if ($middle) {
            $return[] = array(
                'text' => '__sep__',
                'number' => 0,
            );

            for ($i = $current - $before; $i <= $current + $after; ++$i) {
                $return[] = array(
                    'text' => $i,
                    'number' => $i,
                );
            }
        }

        if ($end) {
            $return[] = array(
                'text' => '__sep__',
                'number' => 0,
            );

            for ($i = $start2; $i <= $end2; ++$i) {
                $return[] = array(
                    'text' => $i,
                    'number' => $i,
                );
            }
        }

        if ($current < $nbPage) {
            $return[] = array(
                'text' => '__next__',
                'number' => $current + 1,
            );

            $return[] = array(
                'text' => '__last__',
                'number' => $nbPage,
            );
        }

        return $return;
    }

    #endregion

    public function getAttributeFromHtml($html, $attribute = 'src')
    {
        preg_match('/' . $attribute . '="([^"]+)"/', $html, $match);
        $matchSrc = str_ireplace('src="', '', $match[1]);
        return $matchSrc;
    }

    public function getStringFromHourInt($hourString)
    {
        $length = strlen($hourString);
        $nIteration = 6 - $length;

        for ($i = 0; $i < $nIteration; $i++) {
            $hourString = '0' . $hourString;
        }
       $hourString = preg_replace('/^(\d{2})(\d{2})(\d{2})$/', '$1:$2:$3', $hourString);

        return $hourString;
    }

    public function generateRandomString($numberBytes){
        $time = '"' . time() . "'";
        $substr1 = substr($time, 0,8 );
        $substr2 = substr($time, 8 );

        $string = $substr1 . 'murloc' . $substr2;

        switch ($numberBytes) {
            case '32':
                $string = md5($string);
                break;
            default :
                break;
        }

        return $string;
    }
}
