<?php
namespace Beable\Kernel\File\Handler;

use Beable\Kernel;

class Text extends Kernel\File\Handler
{
	/**
	 * {@inheritdoc}
	 */
	protected function format($line)
	{
		if (is_array($line)) {
			$line = implode($this->sep_value, $line);
		};

		return $line;
	}

	/**
	 * Get mime type from Http\Header\Response
	 * @return string
	 */
	protected function getMimeType()
	{
		return \Beable\Kernel\Http\Header\Response::MIME_TYPE_TEXT;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
		if (is_array($line)) {
			$line = implode($this->sep_value, $line);
		}

		return fwrite($handle, $line);
	}
}
