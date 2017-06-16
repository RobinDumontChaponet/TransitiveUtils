<?php

namespace Transitive\Utils;

trait Node
{
    use Dated;

    /**
     * @var User
     */
    protected $user;

    protected function _initNode()
    {
        $this->_initDated();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    public function hasUser(): bool
    {
        return isset($this->user);
    }

    protected function _nodeJsonSerialize(): array
    {
        return [
            'user' => $this->getUser(),
        ] + $this->_datedJsonSerialize();
    }
}
