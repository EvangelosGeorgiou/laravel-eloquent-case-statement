<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Throwable;

class CaseBuilder
{
    public array $whens = [];
    public array $thenCase = [];

    public ?string $else = null;

    public string $as;

    public array $bindings = [];

    public bool $sum = false;

    public Grammar $grammar;

    public QueryBuilder $queryBuilder;


    public function __construct(QueryBuilder $queryBuilder, Grammar $grammar)
    {
        $this->queryBuilder = $queryBuilder;
        $this->grammar = $grammar;
    }

    /**
     * @param $whens
     * @param $else
     * @param $alias
     * @return $this
     */
    public function case($whens, $else, $alias): CaseBuilder
    {
        $this->whens = collect($whens)->whereInstanceOf(WhenBuilderObject::class)->toArray();
        $this->as = $alias;
        $this->else = $else;

        return $this;
    }

    /**
     * @param mixed $column
     * @param mixed $operator
     * @param mixed $value
     * @return WhenBuilder
     * @throws Throwable
     */
    public function when($column, $operator = null, $value = null): WhenBuilder
    {
        throw_if(!empty($this->whens), 'when already added');

        $instance = new WhenBuilder();
        $this->whens[] = $instance;
        return $instance->when($column, $operator, $value);
    }

    public function addWhen(WhenBuilder $builder): CaseBuilder
    {
        $this->whens[] = $builder;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function addBinding($value): CaseBuilder
    {
        $this->bindings[] = $value;
        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function else($value): self
    {
        $this->else = '?';

        $this->addBinding($value);

        return $this;
    }

    public function as(string $alias)
    {
        $this->as = '?';
        $this->addBinding($alias);
    }

    /**
     * @throws Throwable
     */
    public function toSql(): string
    {
        return $this->grammar->compile($this);
    }

    /**
     * The following methods is to build the query
     */

    public function methodWhen(array $values): string
    {
        [$value, $operator] = $this->queryBuilder->prepareValueAndOperator(
            $values['value'],
            $values['operator'],
            is_null($values['value'])
        );

        $this->addBinding($value);
        return 'when ' . $this->grammar->wrapColumn($values['column']) . ' ' . $operator . ' ?';
    }

    public function methodAnds($values, $method = 'and'): string
    {
        if (empty($values)) {
            return '';
        }

        if ($values instanceof LogicalBuilderObject) {
            return $this->logicalMethod($values);
        }

        return "{$method} ( " . trim(implode(" {$method} ", $this->prepareConditionsToSql($values))) . ' )';
    }

    public function methodOrs($ors): string
    {
        return $this->methodAnds($ors, 'or');
    }

    public function methodThen($value): string
    {
        if (is_array($value)) {
            $whens = collect($value)->whereInstanceOf(WhenBuilderObject::class)->toArray();
            $this->thenCase = $whens;
            return 'then ( ' . $this->grammar->compile($this) . ' )';
        }
        $this->addBinding($value);
        return 'then ?';
    }

    public function methodElse(): string
    {
        $this->addBinding($this->else);
        return 'else ?';
    }

    private function logicalMethod(LogicalBuilderObject $values): string
    {
        $result = collect($values)->map(function ($v, $key) {
            if (empty($v)) {
                return '';
            }
            $key = ucfirst($key);
            $method = "logicalMethod{$key}";
            return $this->$method($v);
        })->filter(fn($v) => !empty($v))->toArray();

        if (count($result) == 1) {
            return "and ( {$result[array_key_first($result)]} )";
        }

        $andCondition = $result['ands'];
        $orCondition = $result['ors'];

        return "and ( {$andCondition} or {$orCondition} ) ";
    }

    private function logicalMethodAnds($values, $method = 'and'): string
    {
        if (empty($values)) {
            return '';
        }

        $result = $this->prepareConditionsToSql($values);

        return "( " . trim(implode(" {$method} ", $result)) . ' )';
    }

    private function logicalMethodOrs($values): string
    {
        return $this->logicalMethodAnds($values, 'or');
    }

    /**
     * @param array $conditions
     * @return array
     *
     * collecting all the conditions, validating the $value and $operator and creating the sql
     */
    private function prepareConditionsToSql(array $conditions): array
    {
        return collect($conditions)->map(function ($condition) {
            [$value, $operator] = $this->queryBuilder->prepareValueAndOperator(
                $condition['value'],
                $condition['operator'],
                is_null($condition['value'])
            );

            $this->addBinding($value);
            return $this->grammar->wrapColumn($condition['column']) . ' ' . $operator . ' ?';
        })->toArray();
    }
}