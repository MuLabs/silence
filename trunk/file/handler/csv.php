<?php
namespace Mu\Kernel\File\Handler;

use Mu\Kernel;

class Csv extends Kernel\File\Handler
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
		if (!is_array($line)) {
			$line = explode($this->sepValue, $line);
		};

		foreach ($line as $key => $value) {
            if (is_array($value)) {
                $value = implode(' - ',$value);
            };
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
	 * @param $handle
	 * @return string
	 */
	protected function openLine($handle) {
		$line = fgetcsv($handle);

		if (!is_array($line)) {
			return false;
		}
		return trim(implode($this->sepValue, $line));
	}

	/**
	 * Get mime type from Http\Header\Response
	 * @return string
	 */
	public function getMimeType()
	{
		return Kernel\Http\Header\Response::MIME_TYPE_CSV;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
		if (is_array($line) && count($line) > 0) {
			fputcsv($handle, array_map('utf8_decode', $line));
		}
	}

	/**
	 * @param string $name
	 * @param array $header
	 * @throws \Mu\Kernel\File\Exception
	 */
	public function save($name, $header = array())
	{
		$handle = @fopen($name, 'w');
		if (!$handle) {
			throw new Kernel\File\Exception($name, Kernel\File\Exception::FILE_NOT_WRITABE);
		}

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
