<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

use EvangGeo\CaseStatement\Eloquent\Builders\Exceptions\MissingConditionException;
use Throwable;

/**
 * This class is used to build the 'when' cases with all the conditions and then parse it to compiler
 */
class WhenBuilder
{
    protected array $when = [];
    protected $ands = [];
    protected $ors = [];
    /** @var string|array $then  */
    protected $then = [];

    /**
     * @param mixed $column
     * @param mixed $operator
     * @param mixed $value
     * @return WhenBuilder
     * @throws Throwable
     *
     */
    public function when($column, $operator = null, $value = null): WhenBuilder
    {
        $this->when = compact('column', 'operator', 'value');
        return $this;
    }

    /**
     * @param mixed $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function and($column, $operator = null, $value = null, $method = 'and'): WhenBuilder
    {
        throw_if(empty($this->when), new MissingConditionException("Using '{$method}' without 'when' condition"));

        if(is_callable($column)){
            $column($logicalBuilder = new LogicalBuilder());
            $column = $logicalBuilder->getObject();
            $method = "{$method}s";
            $this->{$method} = $column;
            return $this;
        }

        if(is_array($column)){
            return $this->addArrayOfMethod($column, $method);
        }

        //renaming method to the variable ands/ors
        $method = "{$method}s";

        $this->{$method}[] = compact('column', 'operator', 'value');
        return $this;
    }

    /**
     * @param mixed $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function or($column, $operator = null, $value = null): WhenBuilder
    {
        return $this->and($column, $operator, $value, 'or');
    }

    /**
     * @param $column
     * @param array $values
     * @return WhenBuilder
     * @todo unfinished functionality
     */
//    public function in($column, array $values): WhenBuilder
//    {
//        return $this;
//    }

    /**
     * @param $column
     * @param array $values
     * @return WhenBuilder
     * @todo unfinished functionality
     */
//    public function notIn($column, array $values): WhenBuilder
//    {
//        return $this;
//    }

    /**
     * @param mixed $value
     * @throws Throwable
     */
    public function then($value, $else = null): WhenBuilderObject
    {
        /** in case the $value of the then method is an array it means that we have another case statement */
        if(is_array($value)){
            throw_if(is_null($else), new MissingConditionException("Condition 'else' is missing from the 'then' condition case"));
            $this->then = $value;
            return new WhenBuilderObject($this->when, $this->ands, $this->ors, $this->then);
        }

        throw_if(empty($this->when), new MissingConditionException("Using 'then' without 'when' condition"));
        $this->then = $value;
        return new WhenBuilderObject($this->when, $this->ands, $this->ors, $this->then);
    }

    private function addArrayOfMethod($column, $method = 'and'): WhenBuilder
    {
        foreach ($column as $key => $value){
            if (is_numeric($key) && is_array($value)) {
                $this->{$method}(...array_values($value));
            } else {
                $this->$method($key, '=', $value);
            }
        }

        return $this;
    }
}