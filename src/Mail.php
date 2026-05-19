<?php

namespace Transitive\Utils;

function getContentTypeString(string $contentType = 'text/plain', string $charset = 'utf-8'): string
{
	return 'Content-Type: '.$contentType.'; charset='.$charset."\r\n".'Content-Transfer-Encoding: quoted-printable'."\r\n\r\n";
}

function getBoundaryString(string $boundary): string
{
	return "\r\n\r\n--".$boundary."\r\n";
}

class Mail
{
	private string $senderAddress;
	private string $senderName;
	private string $subject;
	private string $content;
	private ?string $htmlContent = null;
	private ?string $replyToAddress = null;

	/**
	 * @var array<int, array{content: string, filename: string, contentType: string}>
	 */
	private array $attachments = [];

	public function __construct(
		string $senderAddress,
		string $senderName,
		string $subject,
		string $content,
		?string $htmlContent = null
	) {
		$this->setSenderAddress($senderAddress);
		$this->setSenderName($senderName);
		$this->setSubject($subject);
		$this->setContent($content);
		$this->setHtmlContent($htmlContent);
	}

	public function setSenderAddress(string $senderAddress): self
	{
		$senderAddress = $this->cleanEmail($senderAddress);

		if($senderAddress === '')
			throw new ModelException('Vous avez oublié d\'indiquer l\'adresse d\'envoi');
		if(!$this->isEmail($senderAddress))
			throw new ModelException('Adresse d\'envoi invalide!');

		$this->senderAddress = $senderAddress;

		return $this;
	}

	public function setReplyToAddress(?string $replyToAddress): self
	{
		if($replyToAddress === null || trim($replyToAddress) === '') {
			$this->replyToAddress = null;

			return $this;
		}

		$replyToAddress = $this->cleanEmail($replyToAddress);

		if($replyToAddress === '')
			throw new ModelException('Vous avez oublié d\'indiquer votre adresse e-mail !');
		if(!$this->isEmail($replyToAddress))
			throw new ModelException('Vous avez fourni une adresse e-mail invalide!');

		$this->replyToAddress = $replyToAddress;

		return $this;
	}

	public function setSenderName(string $senderName): self
	{
		$senderName = $this->cleanHeaderText($senderName);

		if($senderName === '')
			throw new ModelException('Vous avez oublié d\'indiquer votre nom !');

		$this->senderName = $senderName;

		return $this;
	}

	public function setSubject(string $subject): self
	{
		$subject = $this->cleanHeaderText($subject);

		if($subject === '')
			throw new ModelException('Vous avez oublié d\'indiquer l\'objet du message !');

		$this->subject = $subject;

		return $this;
	}

	public function setContent(string $content): self
	{
		$content = str_replace("\0", '', $content);

		if(trim($content) === '')
			throw new ModelException('Votre message n\'a pas de contenu !');

		$this->content = $content;

		return $this;
	}

	public function setHtmlContent(?string $htmlContent = null): self
	{
		$this->htmlContent = $htmlContent !== null
			? str_replace("\0", '', $htmlContent)
			: null;

		return $this;
	}

	public function addFile(string $path, ?string $filename = null, ?string $contentType = null): self
	{
		if(!is_file($path) || !is_readable($path))
			throw new ModelException('Pièce jointe illisible: '.$path);

		$content = file_get_contents($path);
		if($content === false)
			throw new ModelException('Impossible de lire la pièce jointe: '.$path);

		return $this->addAttachment(
			$content,
			$filename ?? basename($path),
			$contentType ?? $this->detectContentType($path)
		);
	}

	public function addAttachment(
		string $content,
		string $filename,
		string $contentType = 'application/octet-stream'
	): self {
		$filename = $this->cleanFilename($filename);
		if($filename === '')
			throw new ModelException('Nom de pièce jointe invalide');

		$contentType = $this->cleanContentType($contentType);

		$this->attachments[] = [
			'content' => $content,
			'filename' => $filename,
			'contentType' => $contentType,
		];

		return $this;
	}

	public function clearAttachments(): self
	{
		$this->attachments = [];

		return $this;
	}

	public function send(string|array $to): bool
	{
		[$headers, $body] = $this->build();

		return mail($this->formatRecipients($to), $this->encodeHeader($this->subject), $body, $headers);
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	public function build(): array
	{
		$headers = $this->baseHeaders();

		if($this->attachments) {
			$boundary = $this->newBoundary('mixed');
			$headers[] = 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';

			$body = $this->multipartMixedBody($boundary);

			return [implode("\r\n", $headers), $body];
		}

		if($this->hasHtmlContent()) {
			$boundary = $this->newBoundary('alt');
			$headers[] = 'Content-Type: multipart/alternative; boundary="'.$boundary.'"';

			$body = $this->multipartAlternativeBody($boundary);

			return [implode("\r\n", $headers), $body];
		}

		$headers[] = 'Content-Type: text/plain; charset=utf-8';
		$headers[] = 'Content-Transfer-Encoding: quoted-printable';

		return [implode("\r\n", $headers), $this->quotedPrintable($this->content)];
	}

	private function multipartMixedBody(string $boundary): string
	{
		$body = '';

		if($this->hasHtmlContent()) {
			$alternativeBoundary = $this->newBoundary('alt');
			$body .= '--'.$boundary."\r\n";
			$body .= 'Content-Type: multipart/alternative; boundary="'.$alternativeBoundary."\"\r\n\r\n";
			$body .= $this->multipartAlternativeBody($alternativeBoundary)."\r\n";
		} else {
			$body .= '--'.$boundary."\r\n";
			$body .= $this->textPart($this->content);
		}

		foreach($this->attachments as $attachment) {
			$body .= '--'.$boundary."\r\n";
			$body .= 'Content-Type: '.$attachment['contentType'].'; name="'.$this->escapeQuotedString($attachment['filename'])."\"\r\n";
			$body .= "Content-Transfer-Encoding: base64\r\n";
			$body .= 'Content-Disposition: attachment; filename="'.$this->escapeQuotedString($attachment['filename'])."\"\r\n\r\n";
			$body .= chunk_split(base64_encode($attachment['content']), 76, "\r\n")."\r\n";
		}

		$body .= '--'.$boundary.'--';

		return $body;
	}

	private function multipartAlternativeBody(string $boundary): string
	{
		$body = '--'.$boundary."\r\n";
		$body .= $this->textPart($this->content);

		$body .= '--'.$boundary."\r\n";
		$body .= $this->htmlPart($this->htmlContent ?? '');

		$body .= '--'.$boundary.'--';

		return $body;
	}

	private function textPart(string $content): string
	{
		return "Content-Type: text/plain; charset=utf-8\r\n"
			."Content-Transfer-Encoding: quoted-printable\r\n\r\n"
			.$this->quotedPrintable($content)."\r\n";
	}

	private function htmlPart(string $content): string
	{
		return "Content-Type: text/html; charset=utf-8\r\n"
			."Content-Transfer-Encoding: quoted-printable\r\n\r\n"
			.$this->quotedPrintable($content)."\r\n";
	}

	/**
	 * @return list<string>
	 */
	private function baseHeaders(): array
	{
		return [
			'From: '.$this->formatMailbox($this->senderAddress, $this->senderName),
			'Reply-To: '.$this->formatMailbox($this->replyToAddress ?? $this->senderAddress),
			'MIME-Version: 1.0',
			'Message-ID: <'.bin2hex(random_bytes(16)).'@'.$this->messageIdDomain().'>',
			'X-Mailer: PHP v'.PHP_VERSION,
		];
	}

	private function formatRecipients(string|array $recipients): string
	{
		if(is_string($recipients))
			$recipients = array_map('trim', explode(',', $recipients));

		$recipients = array_values(array_filter($recipients, fn($recipient) => trim((string)$recipient) !== ''));
		if(!$recipients)
			throw new ModelException('Vous avez oublié d\'indiquer le destinataire');

		foreach($recipients as $recipient) {
			$recipient = (string)$recipient;
			if($this->hasHeaderInjection($recipient) || !$this->isEmail($recipient))
				throw new ModelException('Adresse de destination invalide: '.$recipient);
		}

		return implode(', ', $recipients);
	}

	private function formatMailbox(string $address, ?string $name = null): string
	{
		if($name === null || $name === '')
			return $address;

		if(preg_match('/^[\x20-\x7E]+$/', $name) === 1)
			return '"'.$this->escapeQuotedString($name).'" <'.$address.'>';

		return $this->encodeHeader($name).' <'.$address.'>';
	}

	private function cleanEmail(string $value): string
	{
		if($this->hasHeaderInjection($value))
			return '';

		return trim((string)filter_var($value, FILTER_SANITIZE_EMAIL));
	}

	private function cleanHeaderText(string $value): string
	{
		if($this->hasHeaderInjection($value))
			return '';

		return trim(str_replace("\0", '', strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))));
	}

	private function cleanFilename(string $filename): string
	{
		$filename = str_replace('\\', '/', $filename);
		$filename = basename($filename);
		$filename = preg_replace('/[\x00-\x1F\x7F]+/', '', $filename) ?? '';

		return trim($filename);
	}

	private function cleanContentType(string $contentType): string
	{
		$contentType = strtolower(trim($contentType));

		if(!preg_match('/^[a-z0-9][a-z0-9.+-]*\/[a-z0-9][a-z0-9.+-]*$/', $contentType))
			return 'application/octet-stream';

		return $contentType;
	}

	private function detectContentType(string $path): string
	{
		if(function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			if($finfo) {
				$contentType = finfo_file($finfo, $path);

				if(is_string($contentType) && $contentType !== '')
					return $this->cleanContentType($contentType);
			}
		}

		if(function_exists('mime_content_type')) {
			$contentType = mime_content_type($path);
			if(is_string($contentType) && $contentType !== '')
				return $this->cleanContentType($contentType);
		}

		return 'application/octet-stream';
	}

	private function encodeHeader(string $value): string
	{
		if(preg_match('/^[\x20-\x7E]+$/', $value) === 1)
			return $value;

		return '=?UTF-8?B?'.base64_encode($value).'?=';
	}

	private function quotedPrintable(string $value): string
	{
		$encoded = quoted_printable_encode(str_replace(["\r\n", "\r"], "\n", $value));

		return str_replace("\n", "\r\n", $encoded);
	}

	private function isEmail(string $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	private function hasHtmlContent(): bool
	{
		return $this->htmlContent !== null && trim($this->htmlContent) !== '';
	}

	private function hasHeaderInjection(string $value): bool
	{
		return preg_match('/[\r\n]/', $value) === 1;
	}

	private function escapeQuotedString(string $value): string
	{
		return addcslashes($value, "\\\"");
	}

	private function newBoundary(string $prefix): string
	{
		return '=_'.bin2hex(random_bytes(18)).'_'.$prefix;
	}

	private function messageIdDomain(): string
	{
		$domain = $_SERVER['SERVER_NAME'] ?? php_uname('n') ?: 'localhost';
		$domain = preg_replace('/[^A-Za-z0-9.-]/', '', $domain) ?? '';

		return $domain !== '' ? $domain : 'localhost';
	}
}
