<?php
namespace Hedronium\SeedCascade;

class SelfResolver extends MagicResolver
{
    public function get($property)
    {
        if ($this->offset === 0) {
            return null;
        } else {
            return $seeder->resolveValue($this->i, $property, $offset, $this->blocks);
        }
    }
}
