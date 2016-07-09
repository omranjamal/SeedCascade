<?php
namespace Hedronium\SeedCascade;

use Illuminate\Database\Seeder;

abstract class SeedCascade extends Seeder
{
	use Traits\RangeGeneration;
	use Traits\RangeSanitization;
	use Traits\CountDeduction;
	use Traits\TextInterpolation;
	use Traits\DataPersistence;

	public $count = null;
	public $model = null;
	public $table = null;

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
			$self = new SelfResolver($i, $property, $offset, $blocks, $this);
			$inherit = new Inheriter($i, $property, $offset, $blocks, $this);

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
	public function local(\Closure $closure)
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
			return $this->{$method}($i, $self, $inherit);
		});
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

			// Sort for specificity
			usort($raw_keys, function ($a, $b) use (&$raw_ranges) {
				list($a_start, $a_end) = $raw_ranges[$a];
				list($b_start, $b_end) = $raw_ranges[$b];

				return ($b_end-$b_start+1)-($a_end-$a_start+1);
			});

			// The blocks that apply to the iteration
			$blocks = array_map(function ($raw_key) use (&$keys, &$sheet) {
				return $sheet[$keys[$raw_key]];
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

				$this->insertData($i, $data);
			}
		}
	}
}
