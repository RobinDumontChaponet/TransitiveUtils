<?php

namespace Transitive\Utils;
use Reflexive\Model\{Column, Reference, Cardinality};

trait UserContainer
{
    #[Column('accountId')]
	#[Reference(Cardinality::OneToMany, type: User::class, nullable: true)]
    protected ?User $user;

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

    protected function _userContainerJsonSerialize(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }
}
