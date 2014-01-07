<?php
namespace Mu\Kernel;

use Mu\Kernel;

class Toolbox extends Service\Core
{
	const CURRENT_VERSION = '$VERSION';

	const PREFIX_PASS_HASH = '54qsd';
	const SUFFIX_PASS_HASH = '32HfD';

	const TVA_VALUE = 0.196;
	const INCH_TO_CM = 2.54;

	private $bannedKeywords = array(
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

	/**
	 * @param int $total
	 * @param int $nb_per_page
	 * @param int $current
	 * @param int $part_size
	 * @return array
	 */
	public function preparePagination($total, $nb_per_page, $current, $part_size = 3)
	{
		$before = floor(($part_size - 1) / 2);
		$after = $part_size - 1 - $before;
		if ($nb_per_page <= 0) {
			return array();
		}

		$nb_page = ceil($total / $nb_per_page);
		$return = array();
		if ($current > 1) {
			$return[] = array(
				'text' => '<<',
				'number' => 1,
			);

			$return[] = array(
				'text' => '<',
				'number' => $current - 1,
			);
		}

		$start_1 = 1;
		$end_1 = $part_size;
		$start_2 = $nb_page - $part_size + 1;
		$end_2 = $nb_page;
		$middle = true;
		$end = true;
		if ($current - $before - 1 <= $end_1) {
			$end_1 = max($current + $after, $end_1);
			$middle = false;

			if ($current + $after + 1 >= $start_2) {
				$end_1 = $nb_page;
				$end = false;
			}
		}

		if ($current + $after + 1 >= $start_2) {
			$start_2 = min($current - $before, $start_2);
			$middle = false;
		}

		for ($i = $start_1; $i <= $end_1; ++$i) {
			$return[] = array(
				'text' => $i,
				'number' => $i,
			);
		}

		if ($middle) {
			$return[] = array(
				'text' => '...',
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
				'text' => '...',
				'number' => 0,
			);

			for ($i = $start_2; $i <= $end_2; ++$i) {
				$return[] = array(
					'text' => $i,
					'number' => $i,
				);
			}
		}

		if ($current < $nb_page) {
			$return[] = array(
				'text' => '>',
				'number' => $current + 1,
			);

			$return[] = array(
				'text' => '>>',
				'number' => $nb_page,
			);
		}

		return $return;
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
		return $value / self::INCH_TO_CM;
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

	#endregion

	/************************************************************************************
	 **  MONEY                                                                       **
	 ************************************************************************************/
	#region MONEY
	/**
	 * @param int $value
	 * @param string $separator
	 * @param string $thousand
	 * @param string $money
	 * @return string
	 */
	public function priceFormat($value, $separator = ',', $thousand = ' ', $money = 'EUR')
	{
		$value = number_format((float)($value / 100), 2, $separator, $thousand);
		$value .= ($money) ? ' ' . $money : '';
		return $value;
	}

	/**
	 * @param int|float $value
	 * @return float
	 */
	public function priceHtToTtc($value)
	{
		return ceil($value * (1 + self::TVA_VALUE));
	}

	/**
	 * @param int|float $value
	 * @return float
	 */
	public function priceTtcToHt($value)
	{
		return floor($value / (1 + self::TVA_VALUE));
	}

	/**
	 * @param int|float $value
	 * @return float
	 */
	public function priceHtToTva($value)
	{
		return ceil($value * self::TVA_VALUE);
	}

	/**
	 * @param int|float $value
	 * @return float
	 */
	public function priceTtcToTva($value)
	{
		return $value - self::priceTtcToHt($value);
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
	 * @param string $string
	 * @return string
	 */
	public function getValidComparator($string)
	{
		switch ($string) {
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
				$string = '%' . $value . '%';
				break;
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
		return '+' . (int)$prefix . $number;
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
	 * Get current OS name
	 * @return string
	 */
	public function getOS()
	{
		$userAgent = $this->getApp()->getHttp()->getRequest()->getParameters(
			'HTTP_USER_AGENT',
			Kernel\Http\Request::PARAM_TYPE_SERVER,
			''
		);
		$testArray = array(
			'/windows nt 6.2/i' => 'Windows 8',
			'/windows nt 6.1/i' => 'Windows 7',
			'/windows nt 6.0/i' => 'Windows Vista',
			'/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
			'/windows nt 5.1/i' => 'Windows XP',
			'/windows xp/i' => 'Windows XP',
			'/windows nt 5.0/i' => 'Windows 2000',
			'/windows me/i' => 'Windows ME',
			'/win98/i' => 'Windows 98',
			'/win95/i' => 'Windows 95',
			'/win16/i' => 'Windows 3.11',
			'/macintosh|mac os x/i' => 'Mac OS X',
			'/mac_powerpc/i' => 'Mac OS 9',
			'/linux/i' => 'Linux',
			'/ubuntu/i' => 'Ubuntu',
			'/iphone/i' => 'iPhone',
			'/ipod/i' => 'iPod',
			'/ipad/i' => 'iPad',
			'/android/i' => 'Android',
			'/blackberry/i' => 'BlackBerry',
			'/webos/i' => 'Mobile'
		);

		foreach ($testArray as $regex => $value) {
			if (preg_match($regex, $userAgent)) {
				return $value;
			}
		}

		// Return default:
		return "Unknown OS Platform";
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
	public function removeLimits()
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
	#endregion
}