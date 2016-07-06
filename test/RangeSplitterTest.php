<?php
use PHPUnit\Framework\TestCase;

require __DIR__.'/../src/RangeSplitter.php';
use Hedronium\SeedCascade\RangeSplitter;

class RangeSplitterTest extends TestCase
{
  public function testDoubleBeginnings()
  {
    $out = RangeSplitter::split([
      [1, 3], [1, 5], [5, 7]
    ]);

    $this->assertSame([
      [1, 3, [0,1]],
      [4, 4, [1]],
      [5, 5, [1,2]],
      [6, 7, [2]]
    ], $out);
  }

  public function testDoubleEndings()
  {
    $out = RangeSplitter::split([
      [1, 3], [4, 6], [5, 6]
    ]);

    $this->assertSame([
      [1, 3, [0]],
      [4, 4, [1]],
      [5, 6, [1, 2]]
    ], $out);
  }

  public function testDoubleBeginningsMiddle()
  {
    $out = RangeSplitter::split([
      [1, 2], [2, 4], [2, 7]
    ]);

    $this->assertSame([
      [1, 1, [0]],
      [2, 2, [0, 1, 2]],
      [3, 4, [1, 2]],
      [5, 7, [2]],
    ], $out);
  }

  public function testDoubleEndingsMiddle()
  {
    $out = RangeSplitter::split([
      [1, 4], [3, 4], [4, 7]
    ]);

    $this->assertSame([
      [1, 2, [0]],
      [3, 3, [0, 1]],
      [4, 4, [0, 1, 2]],
      [5, 7, [2]],
    ], $out);
  }

  public function testSameStartEnd()
  {
    $out = RangeSplitter::split([
      [1, 3], [4, 7]
    ]);

    $this->assertSame([
      [1, 3, [0]],
      [4, 7, [1]],
    ], $out);
  }

  public function testTripleBeginning()
  {
    $out = RangeSplitter::split([
      [2, 3], [2, 4], [2, 5]
    ]);

    $this->assertSame([
      [2, 3, [0, 1, 2]],
      [4, 4, [1, 2]],
      [5, 5, [2]],
    ], $out);
  }

  public function testTripleEndings()
  {
    $out = RangeSplitter::split([
      [1, 6], [4, 6], [5, 6]
    ]);

    $this->assertSame([
      [1, 3, [0]],
      [4, 4, [0, 1]],
      [5, 6, [0, 1, 2]],
    ], $out);
  }

  public function testRepeatedRanges()
  {
    $out = RangeSplitter::split([
      [2, 4], [2, 4]
    ]);

    $this->assertSame([
      [2, 4, [0, 1]],
    ], $out);
  }
}
