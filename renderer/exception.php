<?php
namespace Mu\Kernel\Renderer;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const RENDER_NOT_FOUND = 1;
    const TYPE_NOT_FOUND = 2;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::RENDER_NOT_FOUND:
				return 'Renderer not found : ' . $this->message;
				break;
            case self::TYPE_NOT_FOUND:
                return 'Renderer type not found : ' . $this->message;
                break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}