<?php
namespace Mu\Kernel\File\Handler;

use Mu\Kernel;

class Text extends Kernel\File\Handler
{
	/**
	 * {@inheritDoc}
	 */
	public function __close()
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function __init()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	protected function format($line)
	{
		if (is_array($line)) {
			$line = implode($this->sepValue, $line);
		};

		return $line;
	}

	/**
	 * Get mime type from Http\Header\Response
	 * @return string
	 */
	public function getMimeType()
	{
		return \Mu\Kernel\Http\Header\Response::MIME_TYPE_TEXT;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
		return fwrite($handle, $this->toString($line) . "\r\n");
	}
}
