<?php
namespace Hedronium\SeedCascade;

use Illuminate\Database\Seeder;

abstract class SeedCascade extends Seeder
{
  public $count = null;

  /**
   * Similar to range but is a generator
   *
   * Shamelessly copied from
   * http://php.net/manual/en/language.generators.overview.php
   * No ragretz.
   *
   * @param int $start    The start of the count
   * @param int $limit    The maximum value to count to
   *
   * @return Generator    a generator object
   *
   * @throws LogicException    if a step value is given that causes and infinite loop (for loop).
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
   * @return int    number of rows to insert
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
   * @param array $keys    Array of range strings.
   * @return array    Array of Range arrays
   */
  protected function getRanges($keys)
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

  public function run()
  {
    $sheet = $this->seedSheet();
    $seed_points = array_keys($sheet);
    $ending = count($seed_points) - 1;

    $ranges = [];

    for ($i = 0; $i < $ending; $i++) {
      $ranges = [];
    }
  }
}
