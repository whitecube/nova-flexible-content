<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\Tests\Unit\Layouts;

use PHPUnit\Framework\TestCase;
use Wmt\NovaFlexibleContent\Layouts\Collection;
use Wmt\NovaFlexibleContent\Layouts\Layout;

class CollectionTest extends TestCase
{
    public function test_find(): void
    {
        $collection = new Collection([new Layout('Foo', 'bar')]);

        $this->assertNotNull($collection->find('bar'));
        $this->assertNull($collection->find('a-name'));
    }
}
