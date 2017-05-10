<?php

namespace Transitive\Utils;

class Group extends Model
{
    use Dated, Named;

    /**
     * __constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct();

        $this->_initNamed($name);
    }

    public function __toString(): string
    {
        return '<span class="group">'.$this->name.'('.$this->id.')</span>';
    }
}
