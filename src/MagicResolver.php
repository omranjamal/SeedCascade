<?php
namespace Hedronium\SeedCascade;

abstract class MagicResolver
{
    protected $i = 0;
    protected $offset = 0;
    protected $blocks = [];
    protected $seeder = null;

    public function __construct($i, $offset, $blocks, $seeder)
    {
        $this->i = $i;
        $this->offset = $offset;
        $this->blocks = $blocks;
        $this->seeder = $seeder;
    }

    abstract public function get($property);

    public function __get($property)
    {
        return $this->get();
    }
}
