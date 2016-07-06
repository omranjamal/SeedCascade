<?php
namespace Hedronium\SeedCascade;

/**
 * Contains the rangle flattening method.
 */
class RangeSplitter
{
	const START = 0;
	const END = 1;

	/**
	 * Flattens an array of ranges to avoid overlapping.
	 *
	 * Splits an array of overlapping range-arrays
	 * into minimum required chunks with no overlapping
	 *
	 * @param array $ranges		An array of range arrays.
	 * @return array	 An array of ranges with keys to the original ranges that apply.
	 */
	public static function split(array $ranges) {
		$points = [];

		// Split the ranges into starting and ending points.
		foreach ($ranges as $i => $range) {
			list($start, $end) = $range;

			//					 Type, Point, Range Key
			$points[] = [self::START, $start, $i];
			$points[] = [self::END, $end, $i];
		}

		// Sort the points according to offsets
		// and set startings before endings.
		usort($points, function ($a, $b) {
			list($a_type, $a_point) = $a;
			list($b_type, $b_point) = $b;

			$diff = $a_point - $b_point;

			// Tie breaking with the point type
			if ($diff === 0) {
				return $a_type - $b_type;
			}

			return $diff;
		});

		// Contains the ranges that apply for each iteration
		$range_map = [];

		// Contains the detected subranges.
		$sub_ranges = [];

		$max = count($points) - 1;

		for ($i = 0; $i < $max; $i++) {
			list($a_type, $a_point, $a_range) = $points[$i];
			list($b_type, $b_point, $b_range) = $points[$i+1];

			if ($a_type === self::START) {

				// If a range has started add in to the range map
				$range_map[$a_range] = true;
			} else {

				// If a range has ended remove it from the map
				$range_map[$a_range] = false;
			}

			// Contains the keys of the ranges that apply
			// to current iteration
			$belongs = [];
			foreach ($range_map as $key => $visible) {
				if ($visible) {
					$belongs[] = $key;
				}
			}

			// Sort for predictability and easy of testing.
			sort($belongs);

			if ($a_type === self::START && $b_type === self::START && ($a_point - $b_point) !== 0) {

				// Ends the sub range right before the next one starts
				$sub_ranges[] = [$a_point, $b_point - 1, $belongs];

			} elseif ($a_type === self::END && $b_type === self::START && ($b_point - $a_point) !== 1) {

				// Ignores is the end of the previous range
				// and start of the range is adjacent to each other

				// Starts the range right after the previous range ends
				// End the range right before the next one starts

				$sub_ranges[] = [$a_point + 1, $b_point - 1, $belongs];

			} elseif ($a_type === self::START && $b_type === self::END) {

				$sub_ranges[] = [$a_point, $b_point, $belongs];

			} elseif ($a_type === self::END && $b_type === self::END && ($a_point - $b_point) !== 0)	{

				// Ignores if the two ending coincide
				// Starts the range right after the previous range ends
				$sub_ranges[] = [$a_point + 1, $b_point, $belongs];

			}
		}

		return $sub_ranges;
	}
}
