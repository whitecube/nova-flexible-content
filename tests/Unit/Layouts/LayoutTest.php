<?php

namespace Tests\Unit\Layouts;

use PHPUnit\Framework\TestCase;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class LayoutTest extends TestCase
{
    public function testDuplicateAndHydrate(): void
    {
        $layout = new Layout('Test Layout', 'test', [
            DateTime::make('Created At'),
            Text::make('Name', 'name', static function($resource, $attribute) {
                return 'static-name';
            }),
        ]);

        // Should not throw any exceptions
        $duplicate = $layout->duplicateAndHydrate('keylikegenerated', [
            'created_at' => '2023-01-01 00:00:00',
            'name' => 'Test'
        ]);

        $this->assertInstanceOf(Layout::class, $duplicate);
        $this->assertEquals('keylikegenerated', $duplicate->key());
        $this->assertEquals('Test Layout', $duplicate->title());
        $this->assertEquals('test', $duplicate->name());

        // The text field should resolve the value
        $textField = $duplicate->fields()[1];
        $textField->resolve(null);
        $this->assertEquals('static-name', $textField->value);
    }

    /** Regression test for #464 */
    public function testForceFillUpdatesAttributeValues(): void
    {
        $layout = new Layout('Any title', 'layout_name', [
            Text::make('Name A', 'name_a'),
            Text::make('Name B', 'name_b'),
        ]);

        $layout->forceFill([
            'name_a' => 'modified a',
            'name_b' => 'modified b',
        ]);

        $this->assertSame('modified a', $layout->getAttribute('name_a'));
        $this->assertSame('modified b', $layout->getAttribute('name_b'));
    }
}
