<?php
namespace Beable\Kernel\File\Handler;

use Beable\Kernel;

class Text extends Kernel\File\Handler
{
	/**
	 * {@inheritdoc}
	 */
	protected function formatStored($line)
	{
		if (is_array($line)) {
			$line = implode(self::SEPARATOR_VALUE, $line);
		};

		return $line;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function formatOutput($handle, $line, $header = false)
	{
		if ($header && is_array($line)) {
			$line = implode(self::SEPARATOR_VALUE, $line);
		}

		fwrite($handle, $line);
	}

	/**
	 * Get mime type from Http\Response_header
	 * @return string
	 */
	protected function getMimeType()
	{
		return \Beable\Kernel\Http\Response_header::MIME_TYPE_TEXT;
	}
}
