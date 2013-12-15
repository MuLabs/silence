<?php
namespace Beable\Kernel\File\Handler;

use Beable\Kernel;

class Csv extends Kernel\File\Handler
{
	/**
	 * {@inheritdoc}
	 */
	protected function formatStored($line)
	{
		if (!is_array($line)) {
			$line = explode(self::SEPARATOR_VALUE, $line);
		};
		
		foreach ($line as $key=>$value) {
			if (!preg_match('#^[0-9,]+$#', $value)) {
				$line[$key] = self::SEPARATOR_STRING.html_entity_decode(htmlspecialchars_decode(preg_replace("#\\n#", " - ", $value))).self::SEPARATOR_STRING;
			}
		}
		return $line;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function formatOutput($handle, $line, $header = false)
	{
		if (!is_array($line)) {
			$line = explode(self::SEPARATOR_VALUE, $line);
		}

		if (is_array($line) && count($line)>0) {
			fputcsv($handle, $line);
		}
	}

	/**
	 * Get mime type from Http\Response_header
	 * @return string
	 */
	protected function getMimeType()
	{
		return \Beable\Kernel\Http\Response_header::MIME_TYPE_CSV;
	}
}
