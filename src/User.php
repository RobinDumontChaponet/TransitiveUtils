<?php

namespace Transitive\Utils;

use DateTime;

class User extends Model implements \JsonSerializable
{
    use Dated, GroupContainer;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $oauthProvider;

    /**
     * @var string
     */
    private $oauthUid;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @var string
     */
    private $sessionHash = '';

    /**
     * @var bool
     */
    private $verified = false;

    private const HASH_COST = 12;

    public function __construct(string $emailAddress, string $passwordHash = '', array $groups = array())
    {
        parent::__construct();
        $this->_initDated();
        $this->_initGroupContainer($groups);

        $this->emailAddress = $emailAddress;

        $this->setPasswordHash($passwordHash);
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

    public function setSessionHash(string $sessionHash = null): void
    {
        $this->sessionHash = $sessionHash;
    }

    public function getOauthProvider(): ?string
    {
        return $this->oauthProvider;
    }

    public function setOauthProvider(string $oauthProvider = null): void
    {
        $this->oauthProvider = $oauthProvider;
    }

    public function getOauthUid(): ?string
    {
        return $this->oauthUid;
    }

    public function setOauthUid(string $oauthUid = null): void
    {
        $this->oauthUid = $oauthUid;
    }


    public function setVerified(bool $verified = true): void
    {
        $this->verified = $verified;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function __toString(): string
    {
        $str = '<address class="user webspace">';
        $str .= '	<a rel="author" href="/users/'.$this->getId().'">'.$this->getLogin().'</a>';
        $str .= '</address>';

        return $str;
    }

    public function connect(): void
    {
        Sessions::set('user', $this);

        $this->aTime = new DateTime();
        $this->sessionHash = Sessions::getId();
    }

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
        + $this->_groupContainerSerialize()
        ;
    }

    public static function randHash(): string
    {
        return md5(rand(0, 1000));
    }
}
