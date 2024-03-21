<?php declare(strict_types=1);

namespace Tests\Unit\Layouts;

use Tests\Unit\ApplicationTestCase;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

/** @covers \Whitecube\NovaFlexibleContent\Layouts\Layout */
final class LayoutTest extends ApplicationTestCase
{
    /** @test */
    public function it_updates_attribute_values(): void
    {
        $layout = new Layout('Any title', 'layout_name', [
            new Flexible('name_a', 'initial a'),
            new Flexible('name_b', 'initial b'),
        ]);

        $layout->forceFill([
            'name_a' => 'modified a',
            'name_b' => 'modified b',
        ]);

        $this->assertSame('modified a', $layout->getAttribute('name_a'));
        $this->assertSame('modified b', $layout->getAttribute('name_b'));
    }
}