<?php

namespace Dottwatson\ModelMeta;

use Illuminate\Database\Eloquent\Builder;

class ModelMetaBuilder extends Builder{

    
    /**
     * @inheritDoc
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {

        $column = $this->buildSqlMetaKey($column);
        return parent::where($column, $operator, $value, $boolean);
    }
    
    /**
     * @inheritDoc
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        $column = $this->buildSqlMetaKey($column);

        return parent::where($column, $operator, $value, 'or');
    }

    /**
     * @inheritDoc
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $column = $this->buildSqlMetaKey($column);

        return parent::whereBetween($column, $values, $boolean, $not);
    }


    
    /**
     * @inheritDoc
     */
    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->buildSqlMetaKey($column);
        
        return parent::orderBy($column,$direction);
    }

    
    
    /**
     * useful for meta called with json structure
     *
     * @param string $name
     * @return string
     */
    protected function buildSqlMetaKey(string $name)
    {
        $blocks = explode('->',$name);
        if($this->model::isMeta($blocks[0])){
            $name = $this->model::queryMeta($blocks[0]);
            return (isset($blocks[1]))
                ?"{$name}->{$blocks[1]}"
                :$name;
        }

        return $name;
    }
    
    // /**
    //  * @inheritDoc
    //  */
    // public function whereMeta()
    // {
    //     $args = func_get_args();
    //     $name = $args[0];

    //     if(!$this->model::isMeta($name)){
    //         throw new \Exception("{$name} is not a valid meta for ".get_class($this->model));
    //     }

    //     $args[0] = $this->model::queryMeta($name);

    //     call_user_func_array([$this,'where'],$args);

    //     return $this;
    // }

    // /**
    //  * @inheritDoc
    //  */
    // public function orWhereMeta()
    // {
    //     $args = func_get_args();
    //     $name = $args[0];

    //     if(!$this->model::isMeta($name)){
    //         throw new \Exception("{$name} is not a valid meta for ".get_class($this->model));
    //     }

    //     $args[0] = $this->model::queryMeta($name);

    //     call_user_func_array([$this,'orWhere'],$args);

    //     return $this;
    // }


    // /**
    //  * @inheritDoc
    //  */
    // public function orderByMeta($name,string $orderWay = null)
    // {
    //     if(!$this->model::isMeta((string)$name)){
    //         throw new \Exception("{$name} is not a valid meta for ".get_class($this->model));
    //     }

    //     $meta = $this->model::queryMeta($name);

    //     call_user_func_array([$this,'orderBy'],[$meta,$orderWay]);

    //     return $this;
    // }

}