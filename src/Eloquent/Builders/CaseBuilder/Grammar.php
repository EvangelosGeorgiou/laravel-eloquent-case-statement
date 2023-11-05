<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

use Illuminate\Support\Arr;

class Grammar
{
    public function compile(CaseBuilder $caseBuilder): string
    {
        //setting the case key word in the frond before we get the other conditions
        $components = ['case'];

        $whens = $caseBuilder->whens;
        if (!empty($caseBuilder->thenCase)) {
            $whens = $caseBuilder->thenCase;
            $caseBuilder->thenCase = [];
        }

        /**
         * collecting all the conditions that we have created earlier using the WhenBuilder
         * and turn them into raw sql with the values as bindings
         */
        $whenCases = collect($whens)->map(function (WhenBuilderObject $when) use ($caseBuilder) {
            return collect($when)->map(function ($values, $key) use ($caseBuilder) {
                $key = ucfirst($key);
                $method = "method{$key}";
                //colling the method for every specific WhenBuilderObject key to transform them into sql
                return $caseBuilder->{$method}($values);
            })->filter(fn($v) => !empty($v))->toArray();
        })->toArray();

        $components = array_merge($components, $whenCases);
        $components[] = $caseBuilder->methodElse();
        $components[] = 'end';

        return trim(implode(' ', Arr::flatten($components)));
    }

    public function wrapColumn($value): string
    {
        if(strpos($value, '.')){
            $segments = explode('.', $value);
            return collect($segments)->map(function ($segment, $key) use ($segments) {
                return $this->wrapColumn($segment);
            })->implode('.');
        }
        return '`' . str_replace('`', '``', $value) . '`';
    }

    public function wrapValue($value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}