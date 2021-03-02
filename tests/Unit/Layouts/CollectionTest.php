<?php

namespace Tests\Unit\Layouts;

use PHPUnit\Framework\TestCase;
use Whitecube\NovaFlexibleContent\Layouts\Collection;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class CollectionTest extends TestCase {

    public function testFind(): void {
        $collection = new Collection([new Layout('Foo', 'bar')]);

        $this->assertNotNull($collection->find('bar'));
        $this->assertNull($collection->find('a-name'));
    }

}
