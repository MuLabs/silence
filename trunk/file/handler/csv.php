<?php
namespace Mu\Kernel\File\Handler;

use Mu\Kernel;

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

		foreach ($line as $key => $value) {
			if (!preg_match('#^[0-9]+$#', $value)) {
				$value = preg_replace("#\\n#", " - ", $value); // Replace carriage returns
				//$value = htmlspecialchars_decode($value);
				$value = html_entity_decode($value);
				$line[$key] = $value;
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
		return \Mu\Kernel\Http\Header\Response::MIME_TYPE_CSV;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
		if (!is_array($line)) {
			$line = explode($this->sep_value, $line);
		}

		if (is_array($line) && count($line) > 0) {
			fputcsv($handle, $line);
		}
	}
}
