<?php
namespace Mu\Kernel\Communication;

use Mu\Kernel;

abstract class Handler extends Kernel\Handler\Core
{
	const MESSAGE_EMPTY = 0;
	const MESSAGE_PENDING = 1;
	const MESSAGE_READY = 2;
	const MESSAGE_SENT = 3;

	protected $configPrefix = 'com_';
	protected $delimiter = ',';
	protected $subject = '';
	protected $content;
	protected $origin;
	protected $destination;
	protected $bccDestination;
	protected $files = array();
	protected $status = 0;

	/**
	 * Attach one or more file to this communication thread
	 * @param $files    List of file path separated by DELIMITER
	 * @return int
	 */
	public function attache($files)
	{
		if (!is_array($files)) {
			$files = explode(self::DELIMITER, $files);
		}

		// Test files:
		$final = array();
		foreach ($files as $filepath) {
			if (file_exists($filepath)) {
				$final[] = $filepath;
			} else {
				// TODO add log
			}
		}

		// Merge with current attached files and return count:
		$this->files = array_merge($this->files, $final);
		return count($final);
	}

	/**
	 * Try to send current message throw the handler
	 * @throws Exception
	 */
	public function send()
	{
		if ($this->getStatus() !== self::MESSAGE_READY) {
			throw new Exception('sending', Exception::STATUS_NOT_READY);
		}

		try {
			$this->sendMessage();
			$this->status = self::MESSAGE_SENT;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), Exception::SENDING_FAILURE);
		}
	}

	/**
	 * @return array
	 */
	public function getAttachements()
	{
		return $this->files;
	}

	/**
	 * Get current thread status (see constants)
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Return current stored content
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set message content
	 * @param string|Kernel\View\View $message
	 * @throws Exception
	 */
	public function setContent($message)
	{
		try {
            if (!is_string($message)) {
                $handler = $this->getApp()->getRendererManager()->getHtmlHandler();
                $message = $handler->render($message);
            }

			$this->content = $this->formatContent($message);
			$this->checkStatus();
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . ' -- ' . substr(
					$message,
					0,
					100
				) . '...', Exception::INCORRECT_FORMAT_CONTENT);
		}
	}

	/**
	 * Return current stored subject
	 * @return mixed
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * Set message content
	 * @param $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = (is_array($subject)) ? array_shift($subject) : $subject;
		$this->checkStatus();
	}

	/**
	 * Return current stored destination
	 * @return mixed
	 */
	public function getDestination()
	{
		return $this->destination;
	}

	/**
	 * Return current stored bccDestination
	 * @return mixed
	 */
	public function getBccDestination()
	{
		return $this->bccDestination;
	}

	/**
	 * Set destination, formated by the handler
	 * @param mixed $to Format : see handler formatDestination
	 * @throws Exception
	 */
	public function setDestination($to)
	{
		try {
			$formated = $this->formatDestination($to);
			if (is_array($formated)) {
				$this->destination = (!isset($this->destination)) ? $formated : array_merge(
					$this->destination,
					$formated
				);
			} else {
				$this->destination .= (!isset($this->destination)) ? $formated : $this->delimiter . $formated;
			}
			$this->checkStatus();
		} catch (Exception $e) {
            throw new Exception($e->getMessage() . ' -- ' . print_r(
                $to,
                true
            ), Exception::INCORRECT_FORMAT_DESTINATION);
        }
	}

	public function setBccDestination($to)
	{
		try {
			$formated = $this->formatDestination($to);
			if (is_array($formated)) {
				$this->bccDestination = (!isset($this->bccDestination)) ? $formated : array_merge(
					$this->bccDestination,
					$formated
				);
			} else {
				$this->bccDestination .= (!isset($this->bccDestination)) ? $formated : $this->delimiter . $formated;
			}
			$this->checkStatus();
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . ' -- ' . $to, Exception::INCORRECT_FORMAT_BCC_DESTINATION);
		}
	}

	/**
	 * Return current stored origin
	 * @return mixed
	 */
	public function getOrigin()
	{
		return $this->origin;
	}

	/**
	 * Set the origin, formated by the handler
	 * @param mixed $from Format : see handler formatOrigin
	 * @throws Exception
	 */
	public function setOrigin($from)
	{
		try {
			$formated = $this->formatOrigin($from);
			$this->origin = (!is_array($formated)) ? $formated : array_shift($formated);
			$this->checkStatus();
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . ' -- ' . $from, Exception::INCORRECT_FORMAT_ORIGIN);
		}
	}

	/**
	 * Test the current attributes of the handler and update status if needed
	 * @return void
	 */
	protected function checkStatus()
	{
		$attributes = array('content', 'destination', 'origin');
		$count = 0;
		foreach ($attributes as $attribute) {
			if (isset($this->$attribute)) {
				$count++;
			}
		}

		// Update status if needed:
		if ($count > 0) {
			$this->status = ($count == (count($attributes))) ? self::MESSAGE_READY : self::MESSAGE_PENDING;
		} else {
			$this->status = self::MESSAGE_EMPTY;
		}
	}

    /**
     * {@inheritDoc}
     */
    public function __close()
    {
        return;
    }

	/**
	 * Format the content, should throw an exception if format is incorrect, else return formated content
	 * @param string $message
	 * @return mixed
	 * @throws Exception
	 */
	abstract protected function formatContent($message);

	/**
	 * Format the destination, should throw an exception if format is incorrect, else return formated destination
	 * @param $to
	 * @return mixed
	 * @throws Exception
	 */
	abstract protected function formatDestination($to);

	/**
	 * Format the origin, should throw an exception if format is incorrect, else return formated origin
	 * @param $from
	 * @return mixed
	 * @throws Exception
	 */
	abstract protected function formatOrigin($from);

	/**
	 * Send the message using correct API
	 * @return bool
	 * @throws Exception
	 */
	abstract protected function sendMessage();
}
