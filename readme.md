# Laravel Model Meta
## _Extend your models with unlimited meta attributes_

[![Build Status](https://travis-ci.org/joemccann/dillinger.svg?branch=master)](https://travis-ci.org/joemccann/dillinger)

Easly add meta data to your model, without change any logic. This package can gives you the ability to extend your laravel model with a configurable set of meta data. Each meta will be automatically inserted,upadted or deleted, following your model modifications and lifecycle.

Meta data will be treated exactly like attributes, so they can be cast, hidden, and have accessories and mutators available. You don't need to know any new syntax, as it is all manageable with the standard model conventions.

### Installation
```
composer require dottwtson/laravel-model-meta
```
then publish config files
```
php artisan vendor:publish --tag=model-meta-config
```
##### File config/model-meta-types.php
Here you can set all the available meta types. You can add any kind of meta type for all your needs
```php
<?php
return [
    ...
    // This is the identifier for the meta type
    'int' => [
        // This is the casting that will be applied on the query. %s will be replaced with the meta name
        'database_casting' => 'CAST(%s.value AS UNSIGNED)', 
        // This is the model casting, according with laravale casting
        'model_casting' => 'int'
    ],
    ...
]
```

Fill free to define all your own meta types

#### config/model-meta.php
Here you can define all the meta data available on your model.
```php
<?php
return [
        ...
    \My\Namespace\ModelName::class => [
        'mymeta' => ['type'=>\Dottwatson\Meta::JSON,...],
    ],
    ...
    \My\Namespace\ModelName::class => [
        'mymeta' => ['type'=>'json',...], // the type is defined in model-meta-type
        ...
    ]
]
```
You can also assign a meta list directly on the model (see below).

#### Implements meta on your existing model
```php
<?php

namespace App\Models;

...
use Dottwatson\ModelMeta\ModelMeta;

class MyModel extends Model
{
    use ModelMeta;
    ...
    protected $tableMeta = 'mymodel_meta';

   ...

}

```

Define meta directly on the model
```php
<?php

namespace App\Models;

...
use Dottwatson\ModelMeta\ModelMeta;

class MyModel extends Model
{
    use ModelMeta;
    ...
    protected $tableMeta = 'mymodel_meta';

   ...
   
   protected static function metaAttributes()
   {
       return [
        'mymeta' => ['type'=>'json',...], // the type is defined in model-meta-type
       ];
   }
}

```

For default, the meta data are relationed to the model primary key. For customize it on your needs add this in your model
```php
<?php

namespace App\Models;

...
use Dottwatson\ModelMeta\ModelMeta;

class MyModel extends Model
{
   ...
 
    public static function metaReference()
    {
        return 'my_column';
    }
    
    ...

}
```

### Commands
```
php artisan make:model-meta MyModel [meta_table_name]
```
This create a preset model under app\Models, and if passed, creates also the table into database

```
php artisan make:meta-table meta_table_name
```

This creates a standalone meta table ready to be used in your model according with the ```$tableMeta``` property.

### Queries
No modifications are required, No extra methods are implemented. 
If you use the model builder,each meta will automatically be treated as if it were a column in your table.

