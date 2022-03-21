<?php

return [
    /*-------------------------------------
    For register metas associated with your model
    declare (in this example is json)

    \My\Namespace\ModelName::class => [
        'mymeta' => ['type'=>\Dottwatson\Meta::JSON,...],
        ...
    ]

    or
    
    \My\Namespace\ModelName::class => [
        'mymeta' => ['type'=>'json',...],
        ...
    ]

    
    default available types are
    REAL - unsigned integer
    INT - signed integer
    FLOAT - float with 10,5 
    DATETIME - YYYY-MM-DD H:i:s
    DATE - YYYY-MM-DD
    TIME H:i:s
    BOOL 
    JSON

    for other types of meta please refeer to model-meta-types config file
    -------------------------------------*/


];