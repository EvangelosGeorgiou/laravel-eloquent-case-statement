<?php

use EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder\WhenBuilder;

if (!function_exists('when')){
    /**
     * @throws Throwable
     */
    function when($column, $operator = null, $value = null): WhenBuilder
    {
        return (new EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder\WhenBuilder())->when($column, $operator, $value);
    }
}