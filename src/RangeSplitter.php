<?php
namespace Hedronium\SeedCascade;

class RangeSplitter
{
  const START = 0;
  const END = 1;

  public static function split($ranges) {
    $points = [];

    foreach ($ranges as $i => $range) {
      list($start, $end) = $range;

      // Type, Point, Range Key
      $points[] = [self::START, $start, $i];
      $points[] = [self::END, $end, $i];
    }

    usort($points, function ($a, $b) {
      list($a_type, $a_point) = $a;
      list($b_type, $b_point) = $b;

      $diff = $a_point - $b_point;

      if ($diff === 0) {
        return $a_type - $b_type;
      }

      return $diff;
    });

    $range_map = [];
    $sub_ranges = [];

    $max = count($points) - 1;
    for ($i = 0; $i < $max; $i++) {
      list($a_type, $a_point, $a_range) = $points[$i];
      list($b_type, $b_point, $b_range) = $points[$i+1];

      if ($a_type === self::START) {
        $range_map[$a_range] = true;
      } else {
        $range_map[$a_range] = false;
      }

      $belongs = [];
      foreach ($range_map as $key => $visible) {
        if ($visible) {
          $belongs[] = $key;
        }
      }

      sort($belongs);

      if ($a_type === self::START && $b_type === self::START && ($a_point - $b_point) !== 0) {
        $sub_ranges[] = [$a_point, $b_point - 1, $belongs];
      } elseif ($a_type === self::END && $b_type === self::START && ($b_point - $a_point) !== 1) {
        $sub_ranges[] = [$a_point + 1, $b_point - 1, $belongs];
      } elseif ($a_type === self::START && $b_type === self::END) {
        $sub_ranges[] = [$a_point, $b_point, $belongs];
      } elseif ($a_type === self::END && $b_type === self::END && ($a_point - $b_point) !== 0)  {
        $sub_ranges[] = [$a_point + 1, $b_point, $belongs];
      }
    }

    return $sub_ranges;
  }
}
