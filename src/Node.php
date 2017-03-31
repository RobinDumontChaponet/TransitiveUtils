<?php

use Transitive\Utils as Utils;

trait Node
{
	use Utils\Dated;

    /**
     * @var User
     */
    protected $user;

    protected function _initNode()
    {
	    $this->_initDated();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
