<?php

namespace Transitive\Utils;

use DateTime;

class User extends Model
{
	use Dated;

    /**
     * @var string
     */
    private $emailAddress;

	/**
     * @var string
     */
    private $pseudonym;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @var string
     */
    private $sessionHash;

    /**
     * @var Group[]
     */
    private $groups;

    private const HASH_COST = 12;

    public function __construct(string $emailAddress, string $pseudonym, string $passwordHash = null, array $groups = array())
    {
        parent::__construct();
        $this->_initDated();

        $this->emailAddress = $emailAddress;

        $this->pseudonym = $pseudonym;

        if(isset($passwordHash))
            $this->setPasswordHash($passwordHash);
        else
            $this->setPassword(Strings::random());

        $this->sessionHash = '';
        $this->groups = $groups;
    }

    public function getLogin(): string
    {
        return $this->getEmailAddress();
    }

    public function setLogin(string $emailAddress): void
    {
        $this->setEmailAddress($emailAddress);
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getSessionHash(): ?string
    {
        return $this->sessionHash;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $e = null;
        $emailAddress = trim($emailAddress);

        if(!Validation::is_valid_email($emailAddress))
            $e = new ModelException('L\'adresse-email doit être dans un format valide.', null, $e);

        if(strlen($emailAddress) > 128)
            $e = new ModelException('L\'adresse-email doit être au maximum de 128 caractères.', null, $e);

        ModelException::throw($e);

        $this->emailAddress = $emailAddress;
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash(trim($password), PASSWORD_BCRYPT, ['cost' => self::HASH_COST]);
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = trim($passwordHash);
    }

    private function setSessionHash(string $sessionHash): void
    {
        $this->sessionHash = $sessionHash;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function addGroup(Group $group): void
    {
        $this->groups[$group->getId()] = $group;
    }

    public function removeGroup(int $groupId): void
    {
        $this->removeGroupById($group->getId());
    }

    public function removeGroupById(int $groupId): void
    {
        if(isset($this->groups[$groupId]))
            unset($this->groups[$groupId]);
    }

    public function hasGroup(Group $group): bool
    {
        return $this->hasGroupById($group->getId());
    }

    public function hasGroupById(int $groupId): bool
    {
        return isset($this->groups[$groupId]);
    }

    public function getPseudonym(): string
    {
        return $this->pseudonym;
    }

    public function setPseudonym(string $pseudonym): void
    {
        $e = null;
        $pseudonym = trim($pseudonym);

        if(is_numeric($pseudonym))
            $e = new ModelException('Le pseudonyme ne peut pas être constitué de chiffres seulement.', null, $e);

        if(strlen($pseudonym) > 40)
            $e = new ModelException('Le pseudonyme ne peut contenir plus de 20 caractères.', null, $e);

        ModelException::throw($e);

        $this->pseudonym = $pseudonym;
    }

    public function __toString(): string
    {
        $str = '<address class="user webspace">';
        $str .= '	<a rel="author" href="/users/'.$this->getId().'">'.$this->getPseudonym().'</a>';
        $str .= '</address>';

        return $str;
    }

    public function connect(): void
    {
        Sessions::set('user', $this);

        $this->aTime = new DateTime();
        $this->sessionHash = Sessions::getId();

        UserDAO::update($this);
    }
}
