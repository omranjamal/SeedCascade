<?php
namespace Hedronium\SeedCascade\Traits;

trait RangeGeneration {
    /**
     * Similar to range but is a generator
     *
     * @param int $start	The start of the count
     * @param int $limit	The maximum value to count to
     *
     * @return Generator	a generator object
     *
     * @throws LogicException	if a step value is given that causes and infinite loop (for loop).
     */
    protected function xrange($start, $limit)
    {
        for ($i = $start; $i <= $limit; $i++) {
            yield $i;
        }
    }
}
