# ![Laravel Nova Flexible Content](https://github.com/whitecube/nova-flexible-content/raw/master/title.png)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

## Work In Progress

We're sorry, this package is still under development. We're expecting a release pretty soon, so stay tuned!

For more information & updates, please follow us on [Twitter](https://twitter.com/whitecube_be).

## Quick start

The Flexible field can be used in various ways and for different purposes, but in most cases you'll only need a few of its capabilities. Here's how to get started really quickly.

### Install

```
composer require whitecube/nova-flexible-content
```

### Usage

A flexible field allows easy management of repeatable and orderable groups of fields. As opposed to the few existing solutions for Laravel Nova, this one does not have constraints on which fields you are allowed to use within these groups. That means you can use all Laravel Nova field types, and also any community-made fields.

#### Adding layouts

Each layout represents a group of fields that can be repeated inside the Flexible field. You can add as many as you wish. If only one layout is defined the field will behave like a simple Repeater and by adding more layouts you'll obtain a real Flexible Content. Both concepts are similar to [their cousins in Wordpress' ACF Plugin](https://www.advancedcustomfields.com/add-ons/).

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
        // ...

        Flexible::make('Content')
            ->addLayout('Simple content section', 'wysiwyg', [
                Text::make('Title'),
                Markdown::make('Content')
            ])
            ->addLayout('Video section', 'video', [
                Text::make('Title'),
                Image::make('Video thumbnail', 'thumbnail'),
                Text::make('Video ID (YoutTube)', 'video'),
                Text::make('Video caption', 'caption')
            ])
    ];
}
```
![Example of Flexible layouts](example_layouts.png)


#### Changing the button's label

Change the default "Add layout" button's text by calling the `button(string $label)` method:

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
        // ...

        Flexible::make('Content')
            ->button('Add something amazing!')
    ];
}
```

![Add something amazing](add_something_amazing.png)


## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made and we would be very happy to merge useful pull requests.

Thanks!


## Made with ❤️ for open source
At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!  
We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects.