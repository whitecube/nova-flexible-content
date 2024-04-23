<?php declare(strict_types=1);

namespace Tests\Unit;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class ApplicationTestCase extends OrchestraTestCase
{
    /** @inheritDoc */
    protected function getPackageProviders($app)
    {
        return [
            \Whitecube\NovaFlexibleContent\FieldServiceProvider::class,
        ];
    }
}
