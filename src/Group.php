<?php

namespace Transitive\Utils;

use Reflexive\Model\{Model, ModelAttribute, ModelProperty};

#[ModelAttribute('Group')]
class Group extends Model implements \JsonSerializable
{
	use Named;

	#[Column('comment')]
	protected string $comment;

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
		if(isset($comment))
			$this->comment = trim($comment);
	}

	public function __toString(): string
	{
		return '<span class="group">'.$this->name.'('.$this->id.')</span>';
	}

	public function jsonSerialize(): mixed
	{
		return parent::jsonSerialize()
		+ $this->_namedJsonSerialize();
	}
}
