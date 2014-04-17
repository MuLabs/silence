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
				$value = preg_replace(
					array("#\\r\\n#", "#\\n\\r#", "#\\r#", "#\\n#"),
					' - ',
					$value
				);
				// Replace carriage returns
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
		return Kernel\Http\Header\Response::MIME_TYPE_CSV;
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

	/**
	 * @param string $name
	 * @param array $header
	 * @throws \Mu\Kernel\File\Exception
	 */
	public function save($name, $header = array())
	{
		$bom = chr(239) . chr(187) . chr(191);

		$handle = @fopen($name, 'w');
		if (!$handle) {
			throw new Kernel\File\Exception($name, Kernel\File\Exception::FILE_NOT_WRITABE);
		}
		fwrite($handle, $bom);

		// Output headers:
		if (is_array($header)) {
			$this->writeLine($handle, $header);
		}

		// Output content:
		foreach ($this->content as $line) {
			$this->writeLine($handle, $line);
		}

		// Close file handler:
		fclose($handle);
	}
}
