<?php

namespace Transitive\Utils;

trait Node
{
    use Dated, UserContainer;

    protected function _initNode()
    {
        $this->_initDated();
    }

    protected function _nodeJsonSerialize(): array
    {
        return [
        ]
        + $this->_datedJsonSerialize()
        + $this->_userContainerJsonSerialize();
    }
}
