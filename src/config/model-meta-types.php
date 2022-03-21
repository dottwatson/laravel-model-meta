<?php
/*----------------------------------------------------
Here are defined the filed types. This drives the query builder
to correct implement and manage queries retrieving your meta
and properly casting them into models and in queries.

Currently this if focused on MySQL, so please modify the
database_casting according with your database syntax.

the %s is where the meta name will be automatically added using printf

You can modify them, or also add different casting types following the same
structure provided here
----------------------------------------------------*/

return [
    'string'=>[
        'database_casting' => 'CAST(%s.value AS CHAR)', 
        'model_casting' => null
    ],
    'real'=> [
        'database_casting' => 'CAST(%s.value AS SIGNED)', 
        'model_casting' => 'int'
    ],
    'int' => [
        'database_casting' => 'CAST(%s.value AS UNSIGNED)', 
        'model_casting' => 'int'
    ],
    'float' => [
        'database_casting'  => 'CAST(%s.value AS DECIMAL(10,5))', 
        'model_casting'     => 'float'
    ],
    'datetime' => [
        'database_casting' => 'CAST(%s.value AS DATETIME)', 
        'model_casting' => 'datetime', 
    ],
    'date' => [
        'database_casting' => 'CAST(%s.value AS DATE)', 
        'model_casting' => 'date', 
    ],
    'time' => [
        'database_casting' => 'CAST(%s.value AS TIME)', 
        'model_casting' => 'time', 
    ],
    'boolean' => [
        'database_casting' => 'IF(CAST(%s.value AS SIGNED) > 0, TRUE, FALSE)', 
        'model_casting' => 'string', 
    ],
    'json' => [
        'database_casting' => 'CAST(%s.value AS JSON)', 
        'model_casting' => 'array',
    ],

    /*--------------------------------
        add your custom meta types here
    ----------------------------------*/
];
