<?php
namespace Hedronium\SeedCascade;

class SelfResolver extends MagicResolver
{
    public function get($property)
    {
        if ($property === $this->property) {
            throw new \Exception("Can't have a self refference.");
        }

        return $this->seeder->resolveValue(
            $this->i,
            $property,
            $this->offset,
            $this->blocks
        );
    }
}
