<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

use EvangGeo\CaseStatement\Eloquent\Builders\Exceptions\BuilderException;

class LogicalBuilder
{
    /** @var mixed $ands */
    public $ands = [];
    /** @var mixed $ors */
    public $ors = [];

    public function __invoke(): LogicalBuilder
    {
        return $this;
    }

    public function and($column, $operator = null, $value = null, $method = 'and'): LogicalBuilder
    {
        if (is_callable($column)) {
            throw new BuilderException('nested closure inside nested closure is not supported at the moment');
        }

        if (is_array($column)) {
            return $this->addArrayOfMethod($column, $method);
        }

        //renaming method to the variable ands/ors
        $method = "{$method}s";

        $this->{$method}[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function or($column, $operator = null, $value = null): LogicalBuilder
    {
        return $this->and($column, $operator, $value, 'or');
    }

    /**
     * @return LogicalBuilder
     * @todo unfinished functionality
     */
    public function in(): LogicalBuilder
    {
        return $this;
    }

    /**
     * @return LogicalBuilder
     * @todo unfinished functionality
     */
    public function notIn(): LogicalBuilder
    {
        return $this;
    }

    public function getObject(): LogicalBuilderObject
    {
        return new LogicalBuilderObject($this->ands, $this->ors);
    }

    /**
     * @param array $column
     * @param string $method
     * @return LogicalBuilder
     *
     * this method is used then column is an array of different expressions and adding them to the correct methos
     */
    private function addArrayOfMethod(array $column, string $method = 'and'): LogicalBuilder
    {
        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->{$method}(...array_values($value));
            } else {
                $this->$method($key, '=', $value);
            }
        }

        return $this;
    }

}