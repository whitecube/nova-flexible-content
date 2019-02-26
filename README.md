# ![Laravel Nova Flexible Content](https://github.com/whitecube/nova-flexible-content/blob/master/title.png)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

## Work In Progress

We're sorry, this package is still under development. We're expecting a release pretty soon, so stay tuned!

For more information & updates, please follow us on [Twitter](https://twitter.com/whitecube_be).

![Laravel Nova Flexible Content in action](https://github.com/whitecube/nova-flexible-content/blob/master/presentation.gif)

## Quick start

The Flexible field can be used in various ways and for different purposes, but in most cases you'll only need a few of its capabilities. Here's how to get you started really quickly.

### Install

```
composer require whitecube/nova-flexible-content
```

### Usage

The basic idea behind this field is to register most of Laravel Nova's existing fields (and probably your own) in _layouts_ for each group of fields you wish to repeat.

#### Adding layouts

You can add as many layouts as you wish. If only one is layout is provided, the field will behave like a simple Repeater Field. By adding more of them you'll be able to choose between the available groups as in any complex Flexible Content Field. Both concepts are similar to their cousins in Wordpress' ACF Plugin.

Layouts can be added using the `addLayout(string $title, string $name, array $fields)` method.

The `$name` parameter is used to store the chosen layout in the field's value. Choose it wisely, you'll probably use it to identify the layouts in your application.

```php
use Whitecube\NovaFlexibleContent\Flexible;

/**
 * Get the fields displayed by the resource.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return array
 */
public function fields(Request $request)
{
    return [
        Flexible::make('Content')
            ->addLayout('Title + Wysiwyg', 'wysiwyg', [
                Text::make('Title'),
                Markdown::make('Content')
            ])
            ->addLayout('Title + Video + Caption', 'video', [
                Text::make('Title'),
                Image::make('Video Thumbnail', 'thumbnail'),
                Text::make('Video ID (YoutTube)', 'video'),
                Text::make('Video Caption', 'caption')
            ])
    ];
}
```

#### Customizing the button label

Change the default "Add layout" copy by calling the `button(string $label)` method:

```php
Flexible::make('Content')
    ->button('Add something amazing!');
```

## Custom Layout Classes

Since some `addLayout` fields definitions can get quite long or are used in other `Flexible` fields, you can also define them in a custom Layout Class:

```php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Markdown;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class SimpleWysiwygLayout extends Layout
{
    /**
     * The layout's unique identifier
     *
     * @var string
     */
    protected $name = 'wysiwyg';

    /**
     * The displayed title
     *
     * @var string
     */
    protected $title = 'Title + WYSIWYG';

    /**
     * Get the fields displayed by the layout.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Title'),
            Markdown::make('Content')
        ];
    }
}
```

You can then refer to this class as first and single parameter of the `addLayout(string $classname)` method:

```php
Flexible::make('Content')
    ->addLayout(\App\Nova\Flexible\Layouts\SimpleWysiwygLayout::class);
```

> **Quickly generate Layout classes**:
> Using the `php artisan flexible:layout {?classname} {?layout}` command, you can create these Layout files promptly. 