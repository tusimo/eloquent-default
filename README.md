EloquentDefault
==================
set the default value for  [Laravel](https://laravel.com/)'s ORM.  And define the column can save to database.


## Installation

Either [PHP](https://php.net) 5.6+ is required.

To get the latest version of EloquentDefault, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require tusimo/eloquent-default
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "tusimo/eloquent-default": "^0.1"
    }
}
```

## Usage

Within your eloquent model class add following line

```php
use Tusimo\EloquentDefault\DefaultTrait;

class User extends Model {
    use DefaultTrait;
    
    /**
     * define the columns the table has 
     * and define the default value saving to the database when attributes not set the column
     */
    protected $defaults = [
        '_id',
        'gender' => 1,
        'status' => 0,
        'deleted_at' => null,
    ];
    /**
     * when save attributes to database ,columns only defined in the defaults will save to database
     */
    protected $columnStrict = true;
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
