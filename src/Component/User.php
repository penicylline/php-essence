<?php

namespace PhpEssence\Component;

use PhpEssence\Service;

class User extends Service
{
    protected $id;
    protected $role;
    protected $name;

    public function __construct($id, $name = null, $role = null)
    {
        $this->id = $id;
        if ($name) {
            $this->name = $name;
        }
        if ($role) {
            $this->role = $role;
        }
    }

    public function setId($id)
    {
        $this->id = $id;
        return;
    }

    public function setRole($role)
    {
        $this->role = $role;
        return;
    }

    public function setName($name)
    {
        $this->name = $name;
        return;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRole()
    {
        return $this->role;
    }
}