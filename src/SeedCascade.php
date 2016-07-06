<?php
namespace Hedronium\SeedCascade;

use Illuminate\Database\Seeder;

abstract class SeedCascade extends Seeder
{
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
  private function xrange($start, $limit, $step = 1)
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
