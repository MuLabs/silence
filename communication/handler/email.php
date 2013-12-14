<?php
namespace Beable\Kernel\Communication\Handler;

use Beable\Kernel;

/**
 * Email Handler ::
 * Manage email with contexts
 *
 * Configuration: no configuration
 *
 * @package Beable\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Email extends Kernel\Communication\Handler
{
	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		$this->setSubject($this->getConfig('subject', ''));
		$from = $this->getConfig('autosend', '');
		if ($from != '') {
			$this->setOrigin($from);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function close()
	{
		try {
			if ($this->getConfig('autosend', false) && $this->getStatus() === self::MESSAGE_READY) {
				$this->send();
			}
		} catch (Exception $e) {
			// TODO log error
			return;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function parseConfig(array $config = array())
	{
		return array(
			'from' => (isset($config[0])) ? $config[0] : '',
			'subject'  => (isset($config[1])) ? $config[1] : '',
			'autosend' => (isset($config[2]) && $config[2]==1)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function formatContent($message)
	{
		return $message;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function formatDestination($to)
	{
		return $this->checkEmails($to);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function formatOrigin($from)
	{
		return $this->checkEmails($from);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function sendMessage()
	{
		// Get variables:
		$subject = $this->getSubject();
		$destination = implode(self::DELIMITER, $this->getDestination());
		$boundary = md5(uniqid(microtime(), TRUE));

		// Headers
		$headers = 'From: '.$this->getOrigin()."\r\n";
		$headers.= 'Mime-Version: 1.0'."\r\n";
		$headers.= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
		$headers.= "\r\n";

		// Message
		$msg = $this->getContent()."\r\n\r\n";

		// Message HTML
		$msg.= '--'.$boundary."\r\n";
		$msg.= 'Content-type: text/html; charset=utf-8'."\r\n\r\n";
		$msg.= $this->getContent()."\r\n";

		// Add file if necessary:
		foreach ($this->getAttachements() as $file) {
			$file_type = filetype($file);
			$file_size = filesize($file);

			$handle  = fopen($file, 'r') or die('File '.$file.' can\'t be open');
			$content = fread($handle, $file_size);
			$content = chunk_split(base64_encode($content));
			fclose($handle);

			$msg.= '--'.$boundary."\r\n";
			$msg.= 'Content-type:'.$file_type.';name='.$file."\r\n";
			$msg.= 'Content-transfer-encoding:base64'."\r\n\r\n";
			$msg.= $content."\r\n";
		}

		// Close message:
		$msg.= '--'.$boundary."\r\n";

		// Function mail()
		mail($destination, $subject, $msg, $headers);
	}

	/**
	 * Test emails validity
	 * @param $emails
	 * @return bool
	 * @throws Exception
	 */
	private function checkEmails($emails)
	{
		if (!is_array($emails)) {
			$emails = explode(self::DELIMITER, $emails);
		}
		foreach ($emails as $email) {
			if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email)) {
				throw new Exception('incorrectly formated email : '.$email);
			}
		}
		return $emails;
	}
}
