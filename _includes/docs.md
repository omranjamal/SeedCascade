
<div id="introduction"></div>

### Introduction

Heres what your new Seeder classes would look like.

```PHP
use Hedronium\SeedCascade\SeedCascade;

class DatabaseSeeder extends SeedCascade {
  public $table = "fruits";

  public function seedSheet() {
    return [
      '1-10' => [
        'name' => 'Potao'
      ]
    ];
  }
}

```

It inserts 10 rows into the `fruits` table, with the name column being set to "tomato" on all of them.

<div id="features-list"></div>

#### Features List

- Range Based Seeding
- Overlapping Ranges
- Interting data directly into database
- Using Models to Insert Data
- Field Inheritance
- String Interpolation
- Closures as value source
- Methods as value source

<div id="getting-started"></div>

### Getting Started

<div id="installation"></div>

#### Installation

Install it via composer. Run the following command in your [Laravel](//laravel.com) project directory.

```BASH
$ composer require hedronium/seed-cascade
```

View it on [Packagist](//packagist.org/packages/hedronium/seed-cascade)
for version information.

<div id="creating-seeder"></div>

#### Creating a Seeder

1. Create a class that extends  
   `Hedronium\SeedCascade\SeedCascade`

2. Add the `$table` or `$model` public property.
3. Implement a `seedSheet()` method that returns your Seeding definitions.


Example

```PHP
use Hedronium\SeedCascade\SeedCascade;

class DatabaseSeeder extends SeedCascade {
  public $table = "fruits";

  public function seedSheet() {
    return [
      '1-10' => [
        'name' => 'Potao'
      ]
    ];
  }
}

```

Seeding definitions are contained in an associative array. The keys act as the ranges of the seeding data (eg. `"1-10"` means the first 10 rows inserted), pointing to another associative array consisting of key value pairs.

##### Using a Model

```PHP
use Hedronium\SeedCascade\SeedCascade;
use App\Potato;

class DatabaseSeeder extends SeedCascade {
  public $model = Potato::class;

  public function seedSheet() {
    // Return seeding definitions.
  }
}

```

##### Multiple Ranges

Multiple ranges can also be specified.

```PHP
return [
    '1-10' => ['name' => 'Potato'],
    '11-20' => ['name' => 'Tomato']
]
```

##### Overlapping Ranges

Overlapping ranges are not only possible but encouraged!

```PHP
return [
    '1-10' => ['name' => 'Potato', color => 'yellow'],
    '6-10' => ['name' => 'Mango']
]
```

In the case of overlapping ranges, properties are automatically inherited. Meaning all rows from **1** _to_ **10** will have the property `color` set to `yellow`.

<div id="running-seeders"></div>

#### Running Seeders

Its the same as with normal [Laravel Seeders](//laravel.com/docs/5.2/seeding).

```BASH
$ php artisan db:seed
```

See, no surprise here.

<div id="seed-definitions"></div>

### Seed Definitions

SeedCascade is so much cooler than what you saw above. Lets get started with leveraging it to its true potential.

<div id="ranges"></div>

#### Ranges

<div id="x-to-y"></div>

##### From `x` to `y`

Exactly like above. Two numbers separated by a hyphen.

```PHP
return [
    '1-5' => ['name' => 'Potatoes']
]
```

<div id="x-to-n"></div>

##### From `x` and onwards

This kind of ranges allow you to write definition that apply from a certain point onwards till the end of time.

```PHP
return [
    '3-n' => ['name' => 'Potatoes']
]
```
A number and the character `n` separated by a hyphen.

> Actually the stuff after the hyphen doesn't really matter.
> Meaning, `'3-Infinity'`, `'3-n'`, `'3-potato'` or even `'3-'` do exactly the same thing.`

<div class="Warning">
  <strong>Warning:</strong> If we use the 'to infinity' ranges such as above, we absolutely MUST set the <code>$count</code> property on the Seeder class, else SeedCascade would be very mad!
</div>

```PHP
class DatabaseSeeder extends SeedCascade
{
    public $count = 20;
    public $table = "edible_things";

    public function seedSheet()
    {
        return [
            '1-10' => ['name' => 'Potao'],
            '3-n' => ['name' => 'Tomato']
        ];
    }
}
```

This makes sure the seeder knows when to stop.

<div id="only-x"></div>

##### Only `x`

Sometimes we might want to change the value of a single row. For that we may just make a key without defining a range.

```PHP
return [
    '1-10' => ['name' => 'Mango'],
    '3' => ['name' => 'Dragon Fruit']
]
```

Only the 3<sup>rd</sup> row inserted will have its `name` field set with `'Dragon Fruit'`.

<div id="values"></div>

#### Values

<div id="direct-values"></div>

##### Direct Value

This ones simple, we just type in the value.

```PHP
return [
    '1-n' => ['name' => 'Potato', 'price' => 20],
    '5-n' => ['name' => 'Potato', 'price' => 100]
]
```

<div id="string-interpolation"></div>

##### String Interpolation

<div id="inheritance"></div>

###### Inheritance

We could ofcourse just write an average string and go your merry way but we threw in a few extra magic just incase. Lets start with an example:

```PHP
return [
  '1-10' => ['name' => 'Potato', 'price' => 30],
  '5-10' => ['name' => 'Super {inherit}', 'price' => 100]
]
```

`{inherit}` will be replaced with `"Potato"`.
It means all the rows 6 from **5** _to_ **10** will be populated with `Super Potato` in their `name` fields.

<div id="referencing-field"></div>

###### Referencing a Field

What if we want to interpolate in another field within the row? SeedCascade has got us covered!

```PHP
return [
    '1-10' => [
        'name' => '{self.type} Engine ({self.model})',
        'type' => 'Jet',
        'model' => 'C3'
    ]
];
```

This will insert 10 rows into the database with all their name fields being `Jet engine (C3)`.

This feature really shines when the current definition block doesn't really have the certain property being refferenced. Eg:

```PHP
return [
    '1-10' => [
        'type' => 'Jet'
    ],
    '1-5' => [
        'name' => '{self.type} Fuel'
    ],
    '6-10' => [
        'name' => '{self.type} Engine'
    ]
];
```

Remmeber the "Cascade" part? So now, we'll have 10 rows in the database. the first 5 being `Jet Fuel` and the last five being `Jet Engine` and all of them will have a `type` of `Jet`.

<div id="explicit-field-inherit"></div>

###### Explicitly Inherit a field

Wanna make sure you get the value from a larger raneg higher up? Have a look:

```PHP
return [
  '1-10' => ['color' => 'Red'],
  '6-10' => [
    'name' => '{inherit.color} Flower',
    'color' => 'Yellow'
  ]
]
```

the last 5 rows will have a name of `'Red Flower'` **NOT** _`'Yellow Flower'`_

<div id="interation-count"></div>

###### Iteration Count

You can also use `{i}` that has the current iteration count.

```PHP
return [
    '1-5' => ['username' => 'user_{i}']
]
```

This will generate 5 rows with username set to: `user_1`, `user_2`, `user_3`, `user_4`, `user_5` respectively.

**Note:** Iteration count starts from `1`.

<div id="closures"></div>

#### Closures

Sometimes you need more power! _Maybe because you're greedy?_ jk jk lol. Values can be closures too.

```PHP
return [
    '1-10' => [
        'order' => function () {
            return rand(1, 100);
        }
    ]
];
```

This will insert 10 rows with the value for `order` being a random integer returned by the closure we provided.

<div id="parameters"></div>

###### Parameters

Closures recieve 3 parameters.

- `$i` - The Iteration Count.
- `$self` - An object taht lets you access other field values.
- `$inherit` - An objects that allows you to inherit field values.

<div id="inhierit"></div>

###### $i

The use of `$i` is obvious. Heres the username example with closures:

```PHP
return [
    '1-5' => [
        'username' => function ($i) {
            return 'user_'.$i;
        }
    ]
];
```

<div id="self"></div>

###### $self

The `$self` object has magic methods implemented that allows dynamic refferencing of properties.

```PHP
return [
    '1-3' => [
        'type' => 'admin'
        'username' => function ($i, $self) {
            return $self->type . '_' . $i;
        }
    ]
];
```

this will insert 3 rows with username being `admin_1`, `admin_2`, `admin_3`.

<div id="inherit"></div>

###### $inherit

The $inherit object can both be invoked like `$inherit()` or concatenated or interpolated into a string like `'super_'.$inherit` or `"super_$inherit"` and all will result in the same value.

```PHP
return [
    '1-n' => [
        'price' => 10
    ],
    '6-10' => [
        'price' => function ($i, $self, $inherit) {
            return $inherit() * 2;
        }
    ]
];
```

the last 5 rows will have twice the price as the first 5 rows.  
string on the other hand may directly be mixed in like:

```PHP
return [
    '1-n' => [
        'type' => 'admin'
    ],
    '6-10' => [
        'type' => function ($i, $self, $inherit) {
            return "super_$inherit";
            // or
            return "super_".$inherit;
        }
    ]
];
```

Inheriting other fields is also supported:

```PHP
return [
  '1-n' => [
    'username' => 'user_{i}'
  ],
  '6-10' => [
    'hash' => function ($i, $self, $inherit) {
      return md5($inherit->username);
    }
  ]
];
```
<div id="bound-closures"></div>

##### Bound Closures

Want closures that refference the seeder class' properties or called a seeder class method? Checkout the `local(Closure $closure)` method.

```PHP
class DatabaseSeeder extends SeedCascade
{
  public $table = "magic";
  protected $meaning_of_life = 42;

  public function seedSheet()
  {
    return [
      '1-10' => [
        'lucky_number' => $this->local(function () {
          return $this->meaning_of_life * rand(1,10)
        })
      ]
    ];
  }
}
```

YES. The `$meaning_of_life` is available inside the closure. MAGIC. Local closures also passed the same parameters as average closures. `$i`, `$self` & `$inherit`.

<div id="using-methods"></div>

##### Using Methods

Let's convert the above example to use a class method instead.

```PHP
class DatabaseSeeder extends SeedCascade
{
  public $table = "magic";
  protected $meaning_of_life = 42;

  protected function life() {
    return $this->meaning_of_life * rand(1, 10);
  }

  public function seedSheet()
  {
    return [
      '1-10' => [
        'lucky_number' => $this->method('life')
      ]
    ];
  }
}
```

<div id="constructors-n-faker"></div>

#### Constructors & Faker

SeedCascade doesn't mind you defining constructors (or destructors).
Heres an example that uses Faker:

```PHP
class DatabaseSeeder extends SeedCascade
{
    public $table = "people";
    protected $faker = null;

    public function __construct()
    {
        $this->faker = new Faker;
    }

    public function seedSheet()
    {
        return [
            '1-10' => [
                'name' => $this->local(function () {
                    return $this->faker->name;
                })
            ]
        ];
    }
}
```

<div id="evaluation-order"></div>

### Order of Evaluation

SeedCascade will start evaluating with the most specific range. Meaning `1-10` will be evaluated first before
`1-20` or `1-Infinity`.

Inheritance will also depend on this. If you `{inherit}` inside the range `1-10` the value interpolated will be resolved from the range `1-20`. If the super range does not have the property it will just traverse backwards till it find a larger range that defines the property.

If no higher ranges define the property `{inherit}` will become `null`.
