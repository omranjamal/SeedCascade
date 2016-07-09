# SeedCascade
A range based Cascading Seeder for [Laravel](http://laravel.com), because Laravel's
default Seeding system even with [Model Factories](https://laravel.com/docs/5.2/seeding#using-model-factories) is cumbersome.

View the [Documentation](http://hedronium.github.io/SeedCascade).

## Features List

- Range Based Seeding
- Overlapping Ranges
- Inserting data directly into database
- Using Models to Insert Data
- Field Inheritance
- String Interpolation
- Closures as value source
- Methods as value source

## Introduction

Heres what your new Seeder classes would look like.

```PHP
use Hedronium\SeedCascade\SeedCascade;

class DatabaseSeeder extends SeedCascade {
  public $table = "food";

  public function seedSheet() {
    return [
      '1-6' => [
        'name' => 'Cabbage',
        'type' => 'vegetable'
      ],
      '4-6' => [
        'name' => 'Carrot'
      ]
    ];
  }
}
```
Inserted Data in `food` table:

| name    | type      |
|---------|-----------|
| Cabbage | vegetable |
| Cabbage | vegetable |
| Cabbage | vegetable |
| Carrot  | vegetable |
| Carrot  | vegetable |
| Carrot  | vegetable |

It inserts 6 rows into the `food` table, with the name column being set to "Cabbage" on the first three and "Carrot" on the last three.

## Installation

Install it via composer. Run the following command in your Laravel project directory.

```BASH
$ composer require hedronium/seed-cascade
```

View it on [Packagist](//packagist.org/packages/hedronium/seed-cascade)
for version information.
