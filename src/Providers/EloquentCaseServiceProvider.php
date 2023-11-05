<?php

namespace EvangGeo\CaseStatement\Providers;

use EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder\CaseBuilder;
use EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder\Grammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class EloquentCaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        Builder::macro('case', function ($whens, $else, $alias) {

            $caseBuilder = (new CaseBuilder($this, new Grammar()));
            $caseBuilder->case($whens, $else, $alias);

            /** @var Builder $this */
            $this->selectRaw(
                '(' . $caseBuilder->toSql() . ') as ' . (new Grammar())->wrapColumn($caseBuilder->as),
                $caseBuilder->getBindings()
            );

            return $this;
        });

        $this->app->bind(
            CaseBuilder::class,
            fn($app) => new CaseBuilder($app->make(Builder::class), new Grammar())
        );
    }
}