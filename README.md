# ![Laravel Nova Flexible Content](title.png)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

## Work In Progress

We're sorry, this package is still under development. We're expecting a release pretty soon, so stay tuned!

For more information & updates, please follow us on [Twitter](https://twitter.com/whitecube_be).

## Quick start

The Flexible field can be used in various ways and for different purposes, but in most cases you'll only need a few of its capabilities. Here's how to get you started really quickly.

### Install

```
composer require whitecube/nova-flexible-content
```

### Usage

The basic idea behind this Flexible Content Field is to allow to include most of Laravel Nova's existing fields (and probably your own). You'll only need to add a `Layout` for each "fields group" that needs to be available in the form and you're all set.

#### Adding layouts

Each layout represents a group of fields that can be repeated inside the Flexible field. You can add as many as you wish. If only one is layout is defined the field will behave like a simple Repeater and by adding more layouts you'll obtain a real Flexible Content. Both concepts are similar to their cousins in Wordpress' ACF Plugin.

These layouts can be added using the `addLayout(string $title, string $name, array $fields)` method. The `$name` parameter will be used to store the chosen layout in the field's value, meaning it will probably be used to identify each group's layout in your application.

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
        ID::make('ID', 'id')->sortable(),

        Flexible::make('Content')
            ->addLayout('Title + wysiwyg', 'wysiwyg', [
                Text::make('Title'),
                Markdown::make('Content')
            ])
            ->addLayout('Title + Video + caption', 'video', [
                Text::make('Title'),
                Image::make('Video thumbnail', 'thumbnail'),
                Text::make('Video ID (YoutTube)', 'video'),
                Text::make('Video caption', 'caption')
            ])
    ];
}
```

#### Changing the button's label

Change the default "Add layout" button's copy by calling the `button(string $label)` method:

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
        ID::make('ID', 'id')->sortable(),

        Flexible::make('Content')
            ->button('Add something amazing!')
    ];
}
```