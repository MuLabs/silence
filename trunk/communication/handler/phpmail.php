<?php
namespace Mu\Kernel\Communication\Handler;

require_once(VENDOR_PATH . '/phpmailer/phpmailer/class.phpmailer.php');

use Mu\Kernel;
use Mu\Kernel\Communication;

/**
 * PhpMail Handler ::
 * Manage SMTP email with contexts
 *
 * Configuration:
 * - from       : mandatory
 * - subject    : mandatory
 * - login      : mandatory
 * - password   : mandatory
 * - host       : mandatory
 * - port       : mandatory
 * - auth       : default = login
 * - secure     : default = ssl
 * - fromname   : default = from address
 *
 * Composer require : "phpmailer/phpmailer": "v5.2.7"
 *
 *
 * @package Mu\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Phpmail extends Kernel\Communication\Handler
{
	/**
	 * {@inheritdoc}
	 */
	public function __init()
	{
		$this->setOrigin($this->getConfig('from', ''));
        $this->setSubject($this->getConfig('subject', ''));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function parseConfig(array $config = array())
	{
		return array(
			'from'      => (isset($config[0])) ? $config[0] : '',
            'subject'   => (isset($config[1])) ? $config[1] : '',
			'login'     => (isset($config[2])) ? $config[2] : '',
			'password'  => (isset($config[3])) ? $config[3] : '',
            'host'      => (isset($config[4])) ? $config[4] : '',
            'port'      => (isset($config[5])) ? $config[5] : '',
            'auth'      => (isset($config[6])) ? $config[6] : 'login',
            'secure'    => (isset($config[7])) ? $config[7] : 'ssl',
            'fromname'  => (isset($config[8])) ? $config[8] : '',
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
        // Initialize mailer:
        $mailer = new \PHPMailer();
        $mailer->IsSMTP();
        $mailer->CharSet    = 'UTF-8';
        $mailer->SMTPDebug  = 0;
		$mailer->SMTPAuth = (int)$this->getConfig('auth', 1);
		$mailer->SMTPSecure = $this->getConfig('secure', 'ssl');
        $mailer->Host       = $this->getConfig('host');
        $mailer->Port       = $this->getConfig('port');
        $mailer->Username   = $this->getConfig('login');
        $mailer->Password   = $this->getConfig('password');

        // Set variables:
        $mailer->From       = $this->getConfig('from');
        $mailer->FromName   = $this->getConfig('fromname', $this->getConfig('from'));
        $mailer->Subject    = $this->getSubject();

        foreach ($this->getDestination() as $to) {
            $mailer->AddAddress($to);
        }
		$bccDestination = $this->getBccDestination();
		if(!empty($bccDestination)) {
			foreach ($bccDestination as $toBcc) {
				$mailer->AddBcc($toBcc);
			}
		}

        // Message HTML
        $mailer->MsgHTML($this->getContent());

		// Add attachment if necessary:
		foreach ($this->getAttachements() as $file) {
            $mailer->AddAttachment($file);
		}

		// Send email
        $mailer->Send();
	}

	/**
	 * Test emails validity
	 * @param $emails
	 * @return bool
	 * @throws Communication\Exception
	 */
	private function checkEmails($emails)
	{
		if (!is_array($emails)) {
			$emails = explode(self::DELIMITER, $emails);
		}
		foreach ($emails as $key=>$email) {
			$email = trim($email);
			$emails[$key] = $email;

			if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email)) {
				throw new Communication\Exception('incorrectly formated email : ' . $email);
			}
		}
		return $emails;
	}
}
