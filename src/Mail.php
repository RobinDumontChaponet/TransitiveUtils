<?php

namespace Transitive\Utils;

class Mail
{
	private $senderAddress;
	private $senderName;
	private $subject;
	private $content;

	private $header;
	private $body;

	function __construct(string $senderAddress, string $senderName, string $subject, string $content)
	{
		$this->setSenderAddress($senderAddress);
		$this->setSenderName($senderName);
		$this->setSubject($subject);
		$this->setContent($content);
	}

	public function setSenderAddress(string $senderAddress)
	{
		$senderAddress = trim(htmlspecialchars(filter_var($senderAddress, FILTER_SANITIZE_EMAIL)));

		if(empty($senderAddress))
			throw new ModelException('Vous avez oublié d\'indiquer votre adresse e-mail !');

		if(!filter_var ($senderAddress, FILTER_VALIDATE_EMAIL))
			throw new ModelException('Vous avez fourni une adresse e-mail invalide!');

		$this->senderAddress = $senderAddress;
	}

	public function setSenderName(string $senderName)
	{
		$senderName = trim(htmlspecialchars(filter_var($senderName, FILTER_SANITIZE_STRING)));

		if(empty($senderName))
			throw new ModelException('Vous avez oublié d\'indiquer votre nom !');

		$this->senderName = $senderName;
	}

	public function setSubject(string $subject)
	{
		$subject = trim(htmlspecialchars(filter_var($subject, FILTER_SANITIZE_STRING)));

		if(empty($subject))
			throw new ModelException('Vous avez oublié d\'indiquer votre nom !');

		$this->subject = $subject;
	}

	public function setContent(string $content)
	{
		$content = htmlspecialchars(filter_var($content, FILTER_UNSAFE_RAW));

		if(trim(empty($content)))
			throw new ModelException('Votre message n\'a pas d\'objet !');

		$this->content = $content;
	}

	private function _build()
	{
		if($this->header === null) {
			$this->body = stripslashes(wordwrap($this->content, 70));

			// *********************SENDING(or not sending, that's the question)***

			$this->header = '';
// 			$this->header ='MIME-Version: 1.0' . "\r\n";
			//$this->header.='Content-type: text/html; charset=utf-8' . "\r\n";
			$this->header.='From: "'.$this->senderName.'" <'.$this->senderAddress.'>' . "\r\n";
			$this->header.='Reply-To: '. $this->senderAddress . "\r\n";
		}
	}

	public function send(string $to)
	{
		$this->_build();

		return true === mail($to, $this->subject, $this->body, $this->header);
	}
}
