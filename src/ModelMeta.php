<?php
namespace Dottwatson\ModelMeta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


trait ModelMeta
{

    /**
     * the meta attributes
     *
     * @var array
     */
    public $metaAttributes = [];
    
    // protected $tableMeta = '';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        foreach($attributes as $key=>$value){
            if(static::isMeta($key)){
                $this->setMeta($key,$value);
            }
        }

        $this->initModelWithMeta();
    }

    /**
     * @inheritDoc
     */
    public function __call($name,$args)
    {
        if(preg_match('#^get.*Attribute$#',$name)){
            $metas = static::metaAttributes();
            foreach($metas as $metaKey=>$metaInfo){
                $callable = 'get'.ucfirst(Str::camel($metaKey)).'Attribute';
                if($callable == $name){
                    return $this->{$metaKey};
                }
            }
        }

        return parent::__call($name,$args);
    }

    /**
     * @inheritDoc
     */
    protected static function booted()
    {
        $self           = new static;
        $modelTable     = $self->getTable();
        $metaAttributes = static::metaAttributes();
        $metaReference  = static::metaReference();

        static::addGlobalScope('withMeta', function (Builder $builder) use($self,$modelTable,$metaReference,$metaAttributes) {
            $tableMeta  = $self->getTableMeta();
            $builder->addSelect("{$modelTable}.*");
            foreach($metaAttributes as $metaKey=>$metaInfo){
                $builder->addSelect("{$metaKey}.value AS {$metaKey}");
                $builder->leftJoin("{$tableMeta} as {$metaKey}",function($join) use ($metaKey,$modelTable,$metaReference){
                    $join->on("{$metaKey}.model_reference","{$modelTable}.{$metaReference}");
                    $join->where("{$metaKey}.name",'=',DB::raw("'$metaKey'"));
                });
            }
        });
    
       
        static::saved(function($model){
            $metaReferenceValue = $model->getMetaReferenceValue();
            $metaAttributes     = $model::metaAttributes();
            
            DB::table($model->getTableMeta())
                ->where('model_reference',$metaReferenceValue)
                ->delete();
            
            foreach($metaAttributes as $metaKey=>$metaInfo){
                DB::table($model->getTableMeta())
                    ->updateOrInsert(
                        ['model_reference'=>$metaReferenceValue,'name'=>$metaKey],
                        ['value'=>$model->getRawOriginalMeta($metaKey)]
                    );
            }
        });

        static::deleted(function($model){
            $metaReferenceValue = $model->getMetaReferenceValue();

            DB::table($model->getTableMeta())
                ->where('model_reference',$metaReferenceValue)
                ->delete();
        });        
        
        parent::booted();
    }

    /**
     * get the table of meta
     *
     * @return string
     */
    public function getTableMeta()
    {
        return $this->tableMeta;
    }

    /**
     * get available meta names for current model
     *
     * @return array
     */
    public static function metaAttributes()
    {
        return config('model-meta.'.static::class,[]);
    }

    /**
     * get the referenced key for meta
     * Default is the primary key
     *
     * @return string
     */
    public static function metaReference()
    {
        return (new static)->getKeyName();
    }

    /**
     * get the referenced value for meta
     *
     * @return mixed
     */
    public function getMetaReferenceValue()
    {
        $key = static::metaReference();

        return $this->{$key};
    }

    /**
     * @inheritDoc
     */
    public function newEloquentBuilder($query)
    {
        return new ModelMetaBuilder($query);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if(static::isMeta($key)){
            if (array_key_exists($key, $this->casts) ||
                $this->hasGetMutator($key) ||
                $this->isClassCastable($key)) {
                return $this->transformModelValue($key, $this->metaAttributes[$key]);
            }

            if (method_exists(self::class, $key)) {
                return;
            }
    
            return $this->getRelationValue($key);
        }
        else{
            return parent::getAttribute($key);
        }

    }

    /**
     * Get an attribute from the $metaAttributes or $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        return $this->metaAttributes[$key] ?? parent::getAttributeFromArray($key);
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key,$value);

        foreach($this->attributes as $key => $value)
        {
            if( static::isMeta($key)){
                $this->setMeta($key,$value);
                unset($this->attributes[$key]);
            }
        }
        
        return $this;        
    }

    /**
     * @inheritDoc
     */
    public static function create(array $attributes = [])
    {
        $model = new static;
        foreach($attributes as $name=>$value){
            if(static::isMeta($name)){
                $model->setMeta($name,$value);
            }
            else{
                $model->{$name} = $value;
            }
        }

        $saved = $model->save();

        return $saved ? $model:$saved;
    }
    

    /**
     * check if meta attribute is a valid meta for current model
     *
     * @param string $name
     * @return boolean
     */
    public static function isMeta(string $name)
    {
        $metaAttributes = static::metaAttributes();

        return isset($metaAttributes[$name]);
    }

    /**
     * get meta and returns its value or default
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $name,$default = null)
    {
        return $this->metaAttributes[$name] ?? $default;
    }   


    /**
     * Set a meta value, if it is available
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function setMeta(string $key,$value)
    {
        if(static::isMeta($key)){
            if(isset($this->casts[$key]) && in_array($this->casts[$key],['array','json'])){
                //check if data needs to be encoded in json
                $result = @json_decode($value);
                if($value !== null && $result === null){
                    $value = json_encode($value);
                }
            }
            $this->metaAttributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Get sql casted meta key syntax according with field type
     *
     * @param string $name
     * @return string|\Illuminate\Database\Query\Expression
     */
    public static function queryMeta(string $name)
    {
        $metas = static::metaAttributes();
        if(isset($metas[$name])){
            $configKey = 'model-meta-types.'.$metas[$name]['type'];

            $casting            = config($configKey,['database_casting'=>$name]);
            $databaseCasting    = sprintf($casting['database_casting'],$name);
            
            return ($casting['database_raw'])
                ?DB::raw($databaseCasting)
                :$databaseCasting;
        }
        
        return $name;
    }

    /**
     * return the original meta value, not casted or mutated
     *
     * @param string $key
     * @return mixed
     */
    public function getRawOriginalMeta(string $key)
    {
        return $this->metaAttributes[$key] ?? null;
    }


    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return static
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        
        $metas          = static::metaAttributes();
        // dd($metas,$attributes);
        foreach($metas as $metaKey=>$metaInfo){
            if(isset($attributes[$metaKey])){
                $this->metaAttributes[$metaKey] = $attributes[$metaKey];
                unset($attributes[$metaKey]);
            }
            else{
                if(!isset($this->metaAttributes[$metaKey])){
                    $this->metaAttributes[$metaKey] = null;
                }
            }
        }

        return parent::setRawAttributes($attributes,$sync);
    }


    /**
     * init model with casts and fillable properties absed on meta informations
     *
     * @return static;
     */
    protected function initModelWithMeta()
    {
        //add Casting based on described meta
        $metaCasts = [];
        $availableMetas = static::metaAttributes();

        foreach($availableMetas as $metaKey=>$metaInfo){
            $metaType = config('model-meta-types.'.$metaInfo['type'],false);
            if(!$metaType){
                throw new \Exception("{$metaInfo['type']} is not a valid meta type");
            }
            
            if(isset($metaType['model_casting'])){
                $metaCasts[$metaKey] = $metaType['model_casting'];    
            }

            if(!$this->guarded || !in_array($metaKey,$this->guarded)){
                if(!in_array($metaKey,$this->fillable)){
                    $this->fillable[]=$metaKey;
                }
            }

            if(!$this->hidden || !in_array($metaKey,$this->hidden)){
                if(!in_array($metaKey,$this->appends)){
                    $this->appends[]=$metaKey;
                }
            }
        }
        
        $this->mergeCasts($metaCasts);
    }

    /**
     * relaod metas from database. all changes will be lost
     *
     * @return static
     */
    public function reloadMeta()
    {
        //because some meta information will be changed,
        //we want be sure to have all correctly set
        $this->initModelWithMeta();
        
        $this->metaAttributes   = [];
        $metas                  = static::metaAttributes();
        $metaReferenceValue     = $this->getMetaReferenceValue();
        $tableMeta              = $this->getTableMeta();
        $dbMetas                = DB::table($tableMeta)
            ->select('*')
            ->where('model_reference',$metaReferenceValue)
            ->get();

        $dbMetas = $dbMetas->mapWithKeys(function($item,$key){
            return [$item->name => $item];
        })->all();

        foreach($metas as $metaKey=>$metaInfo){
            if(isset($dbMetas[$metaKey])){
                $this->setMeta($metaKey,$metas[$metaKey]->value);
            }
            else{
                $this->setMeta($metaKey,null);
            }
        }       

        return $this;
    }


}
