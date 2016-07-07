<?php
namespace Hedronium\SeedCascade\Traits;

trait RangeSanitization {
    /**
	 * Converts range strings to ranges arrays
	 *
	 * It also checks for errors or
	 * inconsistencies in the ranges
	 *
	 * @param array $keys	Array of range strings.
	 * @return array	Array of Range arrays
	 */
	protected function getRanges(array $keys)
	{
		$ranges = [];

		foreach ($keys as $key) {
			$key .= '';

			if (!is_string($key)) {
				throw new \Exception('Only range strings are suported.');
			}

			$parts = explode('-', $key);

			// If the user wants full cascading style
			// only one number will seffice.
			if (count($parts) === 1) {
				list($start) = $parts;
				$end = $start;
			} else {
				list($start, $end) = $parts;
			}

			// Converting Into Integers (was string)
			$start *= 1;
			$end *= 1;

			// Seeder counting starts from 1
			// (like database auto-increment IDs)
			if ($start === 0) {
				throw new \Exception('Starting of a range cannot be zero.');
			}

			if ($end === 0) {

			// Runs a risk of infinite seeding
			// as the ending point cannot be
			// deduced from the ranges.
			if ($this->count() === null) {
				throw new \Exception('Set the `count` property. Risk of Infinite Seeding.');
			} else {
				$end = $this->count;
			}
			}

			// Colliosions can and will happen.
			if ($start > $end) {
				throw new \Exception('The start of the range shouln\'t be greater than the end.');
			}

			$ranges[] = [$start, $end];
		}

		return $ranges;
	}
}
