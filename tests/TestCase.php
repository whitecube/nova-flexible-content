<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Tests;

use Illuminate\Support\Facades\Http;
use Wmt\NovaFlexibleContent\FieldServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $enablesPackageDiscoveries = true;

    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->artisan('cache:clear');
    }

    protected function getPackageProviders($app): array
    {
        return [
            FieldServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom([
            '--path' => [
                realpath(__DIR__ . '/../database/migrations'),
                realpath(__DIR__ . '/database'),
            ],
        ]);
    }

    public function ignorePackageDiscoveriesFrom(): array
    {
        return [
            'wmt/form-builder',
            'wmt/resource-lock',
        ];
    }
}
