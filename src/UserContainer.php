<?php

namespace Transitive\Utils;

trait UserContainer
{
    /**
     * @var User
     */
    protected $user;

    protected function _initUserContainer(User $user)
    {
        $this->user = $user;
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

    protected function _userContainerSerialize(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }
}
