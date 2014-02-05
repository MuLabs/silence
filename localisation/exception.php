<?php
namespace Mu\Kernel\Localization;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const LANG_NOT_SUPPORTED = 1;
	const NO_SUPPORTED_LANGUAGES = 2;


	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::LANG_NOT_SUPPORTED:
				return 'Language not supported : ' . $this->message;
				break;
			case self::NO_SUPPORTED_LANGUAGES:
				return 'No supported language found';
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}