<?php

namespace Transitive\Utils;

trait Named
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

	protected function _initNamed(string $name, string $title = '')
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setName(string $name): void
    {
        $e = null;
        $name = trim($name);

        if(empty($name))
            $e = new ModelException('Le nom doit être renseigné.', null, $e);

        if(strlen($name) > 40)
            $e = new ModelException('Le nom doit être au maximum de 40 caractères.', null, $e);

        ModelException::throw($e);

        $this->name = $name;
    }

    public function setTitle(string $title = null): void
    {
        $e = null;
        $title = trim($title);

        if(is_numeric($title))
            $e = new ModelException('Le sous-titre ne peut pas être constitué de chiffres seulement.', null, $e);

        if(strlen($title) > 25)
            $e = new ModelException('Le sous-titre doit être de maximum 25 caractères.', null, $e);

        ModelException::throw($e);

        $this->title = $title;
    }
}
