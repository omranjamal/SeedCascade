<?php
namespace Hedronium\SeedCascade\Traits;

trait CountDeduction {
    /**
     * Deduces the maximum count based on ranges
     *
     * @param array $ranges	The flattened ranges apecified.
     * @return int	The maximum count
     */
    protected function deduceCount(array $ranges)
    {
        if ($this->count === null) {
            $max = 0;
            foreach ($ranges as $range) {
                foreach ($range as $point) {
                        if ($point > $max) {
                        $max = $point;
                    }
                }
            }

            $this->count = $max;
            return $max;
        }

        return $this->count;
    }
}
