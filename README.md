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

## Custom Layout Classes

Sometimes, `addLayout` definitions can get quite long, or maybe you want them to be  shared with other `Flexible` fields. The answer to this is to extract your Layout into its own class. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=custom-layout-classes).

## Predefined Preset Classes

In addition to reusable Layout classes, you can go a step further and create `Preset` classes for your Flexible fields. These allow you to reuse your whole Flexible field anywhere you want. They also make it easier to make your Flexible fields dynamic, for example if you want to add Layouts conditionally. And last but not least, they also have the added benefit of cleaning up your Nova Resource classes, if your Flexible field has a lot of `addLayout` definitions. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=predefined-preset-classes).

## Custom Resolver Classes

By default, the field takes advantage of a **JSON column** on your model's table. In some cases, you'd really like to use this field, but for some reason a JSON attribute is just not the way to go. For example, you could want to store the values in another table (meaning you'll be using the Flexible Content field instead of a traditional BelongsToMany or HasMany field). No worries, we've got you covered!

Tell the field how to store and retrieve its content by creating your own Resolver class, which basically just contains two simple methods: `get` and `set`. [See the docs for more infomation on this](https://whitecube.github.io/nova-flexible-content/#/?id=custom-resolver-classes).

## Using nova-page with nova-flexible-content
When using the nova-flexible-content package you are probably using the [nova-page](https://github.com/whitecube/nova-page) also. Below is an example on how to combine the packages together. First of all create a [nova page](https://github.com/whitecube/nova-page#creating-templates) and add the [flexible content tool](https://github.com/whitecube/nova-flexible-content#adding-layouts) to the fields method.

### Requesting the nova-page flexible content
Using the nova-page package, you retrieve the content like `<p>{{ Page::get('title') }}</p>`. When you are requesting the flexible content like this, it returns a json string with the flexible content. In the sample below we are going the create a `trait` wich can be called like this `Page::flexible(...)`

**Create a trait**
Next create a trait (for example in `App\Nova\Traits`) with the name `TemplateTrait`. Paste the following code int he new file.
```php
namespace App\Nova\Traits;

/**
 * Description of TemplateTrait
 *
 */
trait TemplateTrait
{

    /**
     * Get the flxible content by calling the method ::flexible(attribute)
     *
     * @param string $attribute
     * @return \Illuminate\Support\Collection
     */
    public function getFlexible(string $attribute)
    {
        $attribute = isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : "{}";
        $parsedValue = json_decode($attribute);
        return collect($parsedValue);
    }
}
```
You can modify the trait to your needs. The `getFlexible` method decodes the flexible content and returns a collection with the data.
Next, go to your template file (for example `App\Nova\Templates\Home`) and include the trait file like below

```php
namespace App\Nova\Templates;

...
use Whitecube\NovaFlexibleContent\Flexible; //<== the flexible package
use App\Nova\Traits\TemplateTrait; //<== your trait

class Home extends Template //<== extend it
{
    use TemplateTrait; //<== implement it

...
```
Now in your template you can do the following : `Page::flexible('my-flexible-content')`

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made and we would be very happy to merge useful pull requests.

Thanks!

## Made with ❤️ for open source

At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!

We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects. Follow us on [Twitter](https://twitter.com/whitecube_be) for more updates!
