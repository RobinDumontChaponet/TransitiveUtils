<?php

namespace Transitive\Utils;

abstract class Model
{
    protected $id;

    public function __construct($id = -1)
    {
        $this->setId($id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId($id = -1): void
    {
        $this->id = trim(id);
    }

    public function __toString()
    {
        return  get_class().' [ id: '.$this->id.(((!get_parent_class())) ? ' ]' : ';  ');
    }

    public function toJson()
    {
        return 'TODO';
    }

    public function toXml()
    {
        return 'TODO';
    }
}
