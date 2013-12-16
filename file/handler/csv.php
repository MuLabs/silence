<?php
namespace Beable\Kernel\File\Handler;

use Beable\Kernel;

class Csv extends Kernel\File\Handler
{
	/**
	 * {@inheritdoc}
	 */
	protected function format($line)
	{
		if (!is_array($line)) {
			$line = explode($this->sep_value, $line);
		};
		
		foreach ($line as $key=>$value) {
			if (!preg_match('#^[0-9,]+$#', $value)) {
				$line[$key] = $this->sep_string.html_entity_decode(htmlspecialchars_decode(preg_replace("#\\n#", " - ", $value))).$this->sep_string;
			}
		}
		return $line;
	}

	/**
	 * Get mime type from Http\Header\Response
	 * @return string
	 */
	protected function getMimeType()
	{
		return \Beable\Kernel\Http\Header\Response::MIME_TYPE_CSV;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
		if (!is_array($line)) {
			$line = explode($this->sep_value, $line);
		}

		if (is_array($line) && count($line)>0) {
			fputcsv($handle, $line);
		}
	}
}
