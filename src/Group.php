<?php

namespace Transitive\Utils;

class Group extends Model
{
    use Dated, Named;

    /**
     * __constructor.
     *
     * @param int    $id   (default: -1)
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        parent::__construct($id);

        $this->_initNamed($name);
    }

    public function __toString(): string
    {
        return '<span class="group">'.$this->name.'('.$this->id.')</span>';
    }
}
