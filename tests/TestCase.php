<?php

namespace EvangGeo\CaseStatement\Tests;

use EvangGeo\CaseStatement\Providers\EloquentCaseServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithFaker,
        RefreshDatabase,
        DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->setUpFaker();
    }

    protected function getPackageProviders($app): array
    {
        return [
            EloquentCaseServiceProvider::class
        ];
    }
}