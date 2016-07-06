<?php
namespace Hedronium\SeedCascade;

class Inheriter extends MagicResolver
{
    public function get($property)
    {
        if ($this->offset === 0) {
            return null;
        } else {
            return $this->seeder->resolveValue(
                $this->i,
                $property,
                $this->offset-1,
                $this->blocks
            );
        }
    }
}
