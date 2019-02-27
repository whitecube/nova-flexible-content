# ![Laravel Nova Flexible Content](https://github.com/whitecube/nova-flexible-content/raw/master/title.png)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

## Work In Progress

We're sorry, this package is still under development. We're expecting a release pretty soon, so stay tuned!

For more information & updates, please follow us on [Twitter](https://twitter.com/whitecube_be).

![Laravel Nova Flexible Content in action](https://github.com/whitecube/nova-flexible-content/raw/master/presentation.gif)

## Quick start

Here's a very condensed guide to get you started asap.  
See the full docs at [https://whitecube.github.io/nova-flexible-content](https://whitecube.github.io/nova-flexible-content)



### Install

```
composer require whitecube/nova-flexible-content
```

### Usage

A flexible field allows easy management of repeatable and orderable groups of fields. As opposed to the few existing solutions for Laravel Nova, this one does not have constraints on which fields you are allowed to use within these groups. That means you can use all Laravel Nova field types, and also any community-made fields.

#### Adding layouts

A layout represents a group of fields that can be repeated inside the Flexible field. You can add as many different layouts as you wish. If only one layout is defined the field will behave like a simple Repeater and by adding more layouts you'll obtain a Flexible Content. Both concepts are similar to [their cousins in Wordpress' ACF Plugin](https://www.advancedcustomfields.com/add-ons/).

Layouts can be added using the following method on your Flexible fields:
```php
 addLayout(string $title, string $name, array $fields)
 ```

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
        // ...

        Flexible::make('Content')
            ->addLayout('Simple content section', 'wysiwyg', [
                Text::make('Title'),
                Markdown::make('Content')
            ])
            ->addLayout('Video section', 'video', [
                Text::make('Title'),
                Image::make('Video Thumbnail', 'thumbnail'),
                Text::make('Video ID (YouTube)', 'video'),
                Text::make('Video Caption', 'caption')
            ])
    ];
}
```
![Example of Flexible layouts](https://github.com/whitecube/nova-flexible-content/raw/master/example_layouts.png)

#### Customizing the button label

You can change the default "Add layout" button's text like so:

```php
Flexible::make('Content')
    ->button('Add something amazing!');
```

![Add something amazing](https://github.com/whitecube/nova-flexible-content/raw/master/add_something_amazing.png)

## Custom Layout Classes

Sometimes, `addLayout` definitions can get quite long, or maybe you want them to be  shared with other `Flexible` fields. The answer to this is to extract your Layout into its own class. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=custom-layout-classes).

## Predefined Preset Classes

In addition to reusable Layout classes, you can go a step further and create `Preset` classes for your Flexible fields. These allow you to reuse your whole Flexible field anywhere you want. They also make it easier to make your Flexible fields dynamic, for example if you want to add Layouts conditionally. And last but not least, they also have the added benefit of cleaning up your Nova Resource classes, if your Flexible field has a lot of `addLayout` definitions. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=predefined-preset-classes).

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made and we would be very happy to merge useful pull requests.

Thanks!


## Made with ❤️ for open source
At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!  
We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects.

