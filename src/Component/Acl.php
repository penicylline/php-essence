<?php

namespace PhpEssence\Component;

use PhpEssence\Service;

class Acl extends Service
{
    const RULE_ACCEPT = 1;
    const RULE_DENIED = 0;

    protected $roles;
    protected $resources;
    protected $rules;

    public function __construct()
    {
        $this->resources = [];
        $this->roles = [];
        $this->rules = [];
    }

    public function isAllow($role, $resource)
    {
        if (isset($this->rules[$role], $this->rules[$role][$resource])) {
            return $this->rules[$role][$resource] === static::RULE_ACCEPT;
        }
        if (isset($this->roles[$role])) {
            return $this->roles[$role] === static::RULE_ACCEPT;
        }
        if (isset($this->resources[$resource])) {
            return $this->resources[$resource] === static::RULE_ACCEPT;
        }
        return false;
    }

    public function addRole($role, $defaultRule)
    {
        if ($defaultRule !== static::RULE_ACCEPT) {
            $defaultRule = static::RULE_DENIED;
        }
        $this->roles[$role] = $defaultRule;
        return $this;
    }

    public function addResource($resource, $defaultRule)
    {
        if ($defaultRule !== static::RULE_ACCEPT) {
            $defaultRule = static::RULE_DENIED;
        }
        $this->resources[$resource] = $defaultRule;
        return $this;
    }

    public function addRule($rule, $role, $resource)
    {
        if ($rule !== static::RULE_ACCEPT) {
            $rule = static::RULE_DENIED;
        }
        $this->rules[$role][$resource] = $rule;
        return $this;
    }
}