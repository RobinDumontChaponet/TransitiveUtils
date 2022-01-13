<?php

namespace Transitive\Utils;

// use Reflexive\Model\{ModelAttribute, Column, ModelProperty, Collection};

trait GroupContainer
{
    #[Column(arrayOf:'Transitive\Utils\Group')]
    protected ?Collection $groups;

    protected function _initGroupContainer(?Collection $groups)
    {
        $this->groups = $groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function getGroups(): Collection
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

    public function hasGroups(): bool
    {
        return !empty($this->groups);
    }

    public function hasGroupById(int $groupId): bool
    {
        return isset($this->groups[$groupId]);
    }

    protected function _groupContainerSerialize(): array
    {
        return [
            'groups' => $this->getGroups(),
        ];
    }
}
