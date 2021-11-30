<?php

namespace Transitive\Utils;

class Group extends Model implements \JsonSerializable
{
	use Named;

	/**
	 * @var string
	 */
	protected $comment;

	/**
	 * __constructor.
	 *
	 * @param string $name
	 */
	public function __construct(string $name, string $comment = null)
	{
		parent::__construct();

		$this->_initNamed($name);
		$this->setComment($comment);
	}

	public function getComment(): ?string
	{
		return $this->comment;
	}
	public function setComment(?string $comment = null): void
	{
		$this->comment = trim($comment);
	}

	public function __toString(): string
	{
		return '<span class="group">'.$this->name.'('.$this->id.')</span>';
	}

	public function jsonSerialize()
	{
		return parent::jsonSerialize()
		+ $this->_namedJsonSerialize();
	}
}
