# ![Laravel Nova Flexible Content](https://github.com/whitecube/nova-flexible-content/raw/master/title.png)

![](https://img.shields.io/github/release/whitecube/nova-flexible-content.svg?style=flat)
[![](https://img.shields.io/packagist/dt/whitecube/nova-flexible-content.svg?colorB=green&style=flat)](https://packagist.org/packages/whitecube/nova-flexible-content)
[![](https://img.shields.io/github/license/whitecube/nova-flexible-content.svg?style=flat)](https://github.com/whitecube/nova-flexible-content/blob/master/LICENSE)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

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

### Using Flexible values in views

The field stores its values as a single JSON string, meaning this string needs to be parsed before it can be used in your application. Of course, you could cast the attribute to a collection in your models, but you'll end up with complex `stdClass` objects making it more difficult to interract with the layout's attributes.

By implementing the `HasFlexible` trait on your models, you can call the `flexible($attribute)` method, which will automatically transform the attribute's value into a fully parsed `Whitecube\NovaFlexibleContent\Layouts\Collection`. Feel free to apply this `flexible()` call directly in your blade views or to extract it into an attribute's mutator method as shown below:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

class MyModel extends Model
{
    use HasFlexible;

    public function getFlexibleContentAttribute()
    {
        return $this->flexible('flexible-content');
    }
}
```

By default, the `HasFlexible` trait will collect basic `Layout` instances. If you want to map the layouts into [Custom Layout instances](https://github.com/whitecube/nova-flexible-content#custom-layout-classes), it is also possible to specify the mapping rules as follows:

```php
public function getFlexibleContentAttribute()
{
    return $this->flexible('flexible-content', [
        'wysiwyg' => \App\Nova\Flexible\Layouts\WysiwygLayout::class,
        'video' => \App\Nova\Flexible\Layouts\VideoLayout::class,
    ]);
}
```

Each Layout (or custom layout extending the base Layout) is already implementing the `HasFlexible` trait, meaning you can directly use the `$layout->flexible('my-sub-layout')` method to parse nested flexible content values.

#### The Layouts Collection

Collections returned by the `HasFlexible` trait extend the original `Illuminate\Support\Collection`. These custom layout collections expose a `find(string $name)` method which returns the first layout having the given layout `$name`.

#### The Layout instance

Layouts are some kind of _fake models_. They use Laravel's `HasAttributes` trait, which means you can define accessors & mutators for the layout's attributes. Furthermore, it's also possible to access the Layout's properties using the following methods:

##### `name()`

Returns the layout's name.

##### `title()`

Returns the layout's title (as shown in Nova).

##### `key()`

Returns the layout's unique key (the layout's unique identifier).

## Going further

When using the Flexible Content field, you'll quickly come across of some use cases where the basics described above are not enough. That's why we developed the package in an extendable way, making it possible to easily add custom behaviors and/or capabilities to Field and its output.

### Custom Layout Classes

Sometimes, `addLayout` definitions can get quite long, or maybe you want them to be shared with other `Flexible` fields. The answer to this is to extract your Layout into its own class. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=custom-layout-classes).

### Predefined Preset Classes

In addition to reusable Layout classes, you can go a step further and create `Preset` classes for your Flexible fields. These allow you to reuse your whole Flexible field anywhere you want. They also make it easier to make your Flexible fields dynamic, for example if you want to add Layouts conditionally. And last but not least, they also have the added benefit of cleaning up your Nova Resource classes, if your Flexible field has a lot of `addLayout` definitions. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=predefined-preset-classes).

### Custom Resolver Classes

By default, the field takes advantage of a **JSON column** on your model's table. In some cases, you'd really like to use this field, but for some reason a JSON attribute is just not the way to go. For example, you could want to store the values in another table (meaning you'll be using the Flexible Content field instead of a traditional BelongsToMany or HasMany field). No worries, we've got you covered!

Tell the field how to store and retrieve its content by creating your own Resolver class, which basically just contains two simple methods: `get` and `set`. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=custom-resolver-classes).

### Usage with nova-page

Maybe you heard of one of our other packages, [nova-page](https://github.com/whitecube/nova-page), which is a Nova Tool that allows to edit static pages such as an _"About"_ page (or similar) without having to declare a model for it individually. More often than not, the Flexible Content Field comes in handy. Don't worry, both packages work well together! First of all create a [nova page template](https://github.com/whitecube/nova-page#creating-templates) and add a [flexible content](https://github.com/whitecube/nova-flexible-content#adding-layouts) to the template's fields.

As explained in the documentation, you can [acces nova-page's static content](https://github.com/whitecube/nova-page#accessing-the-data-in-your-views) in your blade views using `{{ Page::get('attribute') }}`. When requesting the flexible content like this, it returns a raw json string describing the flexible content, which is of course not very useful. Instead, you can simply implement the `Whitecube\NovaFlexibleContent\Concerns\HasFlexible` trait on your page Templates, which will expose the `Page::flexible('attribute')` facade method and will take care of the flexible content's transformation.

```php
namespace App\Nova\Templates;

// ...
use App\Nova\Templates\Concerns\HasFlexible;

class Home extends Template
{
    use HasFlexible;

    // ...
}
```

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made and we would be very happy to merge useful pull requests.

Thanks!

## Made with ❤️ for open source

At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!

We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects. Follow us on [Twitter](https://twitter.com/whitecube_be) for more updates!
