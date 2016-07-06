<?php
namespace Hedronium\SeedCascade;

class SelfResolver extends MagicResolver
{
    public function get($property)
    {
        return $this->seeder->resolveValue(
            $this->i,
            $property,
            $this->offset,
            $this->blocks
        );
    }
}
