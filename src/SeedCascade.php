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
				throw new \LogicException('Step must be +ve');
			}

			for ($i = $start; $i <= $limit; $i += $step) {
				yield $i;
			}
		} else {
			if ($step >= 0) {
				throw new \LogicException('Step must be -ve');
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

	/**
	 * Handles simple text imagesetinterpolation
	 *
	 * @param mixed $prop the value of the property
	 * @param string $property the property name.
	 * @param int $i the current iteration count.
	 * @param SelfResolver $self the MagicResolver instance for self.
	 * @param Inheriter $inherit the MagicResolver instance for inheritance.
	 *
	 * @return string  the string with all the data interpolated.
	 */
	protected function interpolateText($prop, $property, $i, SelfResolver $self, Inheriter $inherit)
	{
		// Replace {self.*} and {inherit.*}
		$prop = preg_replace_callback(
			'/\{(self|inherit)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\}/',
			function ($matches) use (&$self, &$inherit) {
				list($expression, $source, $property) = $matches;

				if ($source === 'self') {
					return $self->get($property);
				} elseif ($source === 'inherit') {
					return $inherit->get($property);
				}
			},
			$prop
		);

		// Replace {i} with the current iteration number
		$prop = preg_replace('/\{i\}/', $i, $prop);

		// Replace {inherit} with the inherit value of higher blocks.
		$prop = preg_replace(
			'/\{inherit\}/',
			$inherit->get($property),
			$prop
		);

		// replace excaped curly braces
		$prop = preg_replace_callback(
			'/\\\(\{|\})/',
			function ($matches) {
				return $matches[1];
			},
			$prop
		);

		return $prop;
	}

	/**
	 * Resolves the value of a property
	 *
	 * @param int $i    The current Iteration count.
	 * @param string $property    The property name of the property to resolve.
	 * @param int $offset    The block index from which to resolve the value.
	 * @param array $blocks    The blocks to resolve from.
	 *
	 * @param mixed    The resolved value of the property.
	 */
	public function resolveValue($i, $property, $offset, array $blocks)
	{
		// Current block
		$block = $blocks[$offset];

		// Look for the property in the current block
		if (isset($block[$property])) {
			$prop = $block[$property];

			// Magic objects that allow relative value resolution
			$self = new SelfResolver($i, $offset, $blocks, $this);
			$inherit = new Inheriter($i, $offset, $blocks, $this);

			if (is_callable($prop)) {
				return $prop($i, $self, $inherit);
			} elseif (is_string($prop) && strpos($prop, '{') !== false) {
				return $this->interpolateText($prop, $property, $i, $self, $inherit);
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

	/**
	 * Bind a Closure to the current instance
	 *
	 * @param Closure $closure    The closure to bind.
	 * @return Closure    A new closure instance that is bound to the current instance.
	 */
	public function local(Closure $closure)
	{
		return $closure->bindTo($this);
	}

	/**
	 * Returns a Closure that calls a method on the current instance.
	 *
	 * @param string $method    The method name to call.
	 * @return Closure    a Closure that calls a method on the current instance.
	 */
	public function method($method)
	{
		// Return a closure bound to the current instance.
		return $this->local(function ($i, $self, $inherit) use ($method) {
			$this->{$method}($i, $self, $inherit);
		});
	}

	protected function insertData($i, array $data)
	{
		print_r($data);
	}

	public function run()
	{
		$sheet = $this->seedSheet();

		$keys = array_keys($sheet);

		// Numeric ranges deduced from the keys (range strings)
		$raw_ranges = $this->getRanges($keys);

		// Flattened ranges
		$ranges = RangeSplitter::split($raw_ranges);

		$this->deduceCount($ranges);
		$count = $this->count();

		// All the properties possible
		$properties = $this->getProperties($sheet);

		// Universal Counter
		$i = 0;

		foreach ($ranges as $range) {
			list($start, $end, $raw_keys) = $range;

			// The blocks that apply to the iteration
			$blocks = array_map(function ($raw_key) use (&$keys, &$sheet) {
				return $sheet[$keys[$raw_key]];
			}, $raw_keys);

			// Loop over the flattened sub-ranges
			foreach (range($start, $end) as $x) {

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

				$this->insertData($i, $data);
			}
		}
	}
}
