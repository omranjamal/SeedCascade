<?php
namespace Hedronium\SeedCascade;

use Illuminate\Database\Seeder;

abstract class SeedCascade extends Seeder
{
	public $count = null;
	public $model = null;
	public $table = null;

	/**
	 * Similar to range but is a generator
	 *
	 * Shamelessly copied from
	 * http://php.net/manual/en/language.generators.overview.php
	 * No ragretz.
	 *
	 * @param int $start	The start of the count
	 * @param int $limit	The maximum value to count to
	 *
	 * @return Generator	a generator object
	 *
	 * @throws LogicException	if a step value is given that causes and infinite loop (for loop).
	 */
	protected function xrange($start, $limit, $step = 1)
	{
	if ($start < $limit) {
		if ($step <= 0) {
			throw new LogicException('Step must be +ve');
		}

		for ($i = $start; $i <= $limit; $i += $step) {
			yield $i;
		}
	} else {
		if ($step >= 0) {
			throw new LogicException('Step must be -ve');
		}

		for ($i = $start; $i >= $limit; $i += $step) {
			yield $i;
		}
	}
	}

	abstract public function seedSheet();

	/**
	 * Returns the number of row to insert
	 *
	 * @return int	number of rows to insert
	 */
	public function count()
	{
	return $this->count;
	}

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
		if ($end > $start) {
		throw new \Exception('The start of the range shouln\'t be greater than the end.');
		}

		$ranges[] = [$start, $end];
	}
	}

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

	/**
	 * Lists all the properties available to insert into
	 *
	 * @param array $sheet	The SeedSheet array.
	 * @return array	An array of property names.
	 */
	protected function getProperties(array $sheet)
	{
	// Holds all the different properties that is insertable
	$properties = [];

	// Lists all the setable properties
	foreach ($sheet as $block) {
		foreach ($block as $property => $value) {
		$properties[] = $property;
		}
	}

	return $properties;
	}

	protected function resolveValue($i, $property, $offset, array $blocks)
	{
		// Current block
		$block = $blocks[$offset];

		// Look for the property in the current block
		if (isset($block[$property])) {
			$prop = $block[$property];

			if (is_callable($prop)) {
				$self = new SelfResolver($i, $offset, $blocks, $this);
				$inherit = new Inheriter($i, $offset, $blocks, $this);

				return $prop($i, $self, $inherit);
			} else {
				return $prop;
			}
		} else {
			if ($offset > 0) {
				return $this->resolveValue($i, $property, $offset-1, $blocks);
			} else {
				return null;
			}
		}
	}

	public function run()
	{
		$sheet = $this->seedSheet();

		// Numeric ranges deduced from the keys (range strings)
		$raw_ranges = $this->getRanges(array_keys($sheet));

		// Flattened ranges
		$ranges = RangeSplitter::split($raw_ranges);

		$this->deduceCount();
		$count = $this->count();

		// All the properties possible
		$properties = $this->getProperties($sheet);

		// Universal Counter
		$i = 0;

		foreach ($ranges as $range) {
			list($start, $end, $raw_keys) = $range;

			// The blocks that apply to the iteration
			$blocks = array_map(function ($raw_key) {
				return $sheet[$raw_key];
			}, $raw_keys);

			// Loop over the flattened sub-ranges
			foreach ($this->xrange($start, $end) as $x) {

				// limit the number of rows inserted
				if ($i < $count) {
					$i++;
				} else {
					break 2;
				}

				// Contains all the data that is resolved.
				$data = [];

				// resolves property values from sheet block
				foreach ($properties as $property) {
					$value = $this->resolveValue($i, $property, count($blocks)-1, $blocks);

					if ($value !== null) {
						$data[$property] = $value;
					}
				}
			}
		}
	}
}
