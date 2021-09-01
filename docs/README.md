# ![Laravel Nova Flexible Content](https://github.com/whitecube/nova-flexible-content/raw/master/title.png)

An easy & complete Flexible Field for Laravel Nova, perfect for repeated and flexible field groups.

![Laravel Nova Flexible Content in action](https://github.com/whitecube/nova-flexible-content/raw/master/presentation.gif)

## Quick start

The Flexible field can be used in various ways and for different purposes, but in most cases you'll only need a few of its capabilities. Here's how to get started really quickly.

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

#### Making the field full width

You can make the flexible field full width, taking up all available space on the form, and moving the label above the field by doing the following:

```php
Flexible::make('Content')
    ->fullWidth()
```

#### Limiting layouts

You can limit how many times the "Add Layout" button will appear by doing the following:

```php
Flexible::make('Content')->limit(2);
```

You can specify any integer, or no integer at all; in that case it will default to 1.

#### Layout removal confirmation

You can choose to display a confirmation prompt before a layout is deleted by doing:

```php
Flexible::make('Content')->confirmRemove();

// You can override the text as well
Flexible::make('Content')->confirmRemove($label = '', $yes = 'Delete', $no = 'Cancel');
```

![Add something amazing](https://github.com/whitecube/nova-flexible-content/raw/master/confirm_remove.png)

#### Layout selection menu

You can customize the way your user selects a layout, you can choose between 'flexible-drop-menu' and 'flexible-search-menu' or create your own custom menu component.

```php
// Default, simple list of all layouts
Flexible::make('Content')->menu('flexible-drop-menu');

// searchable select field
Flexible::make('Content')->menu('flexible-search-menu');

// customized searchable select field
Flexible::make('Content')
    ->menu(
        'flexible-search-menu',
        [
            'selectLabel' => 'Press enter to select',
            // the property on the layout entry
            'label' => 'title',
            // 'top', 'bottom', 'auto'
            'openDirection' => 'bottom',
        ]
    );
```

All you're doing here is defining which Vue component needs to be used.

You can take `resources/js/components/OriginalDropMenu.vue` or `resources/js/components/SearchMenu.vue` as a starting point.

## Using Flexible values in views

The field stores its values as a single JSON string, meaning this string needs to be parsed before it can be used in your application.

### With casts

This can be done trivially by using the `FlexibleCast` class in this package:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast;

class MyModel extends Model
{
    protected $casts = [
        'flexible-content' => FlexibleCast::class
    ];
}
```

#### Writing a custom flexible cast

By default, the `FlexibleCast` class will collect basic `Layout` instances. If you want to map the layouts into [Custom Layout instances](https://github.com/whitecube/nova-flexible-content#custom-layout-classes), it is also possible. First, create a custom flexible cast by running `php artisan flexible:cast MyFlexibleCast`. This will create the file in the `App\Casts` directory.

Then easily map your custom layout classes to the proper keys:

```php
namespace App\Casts;

class MyFlexibleCast extends FlexibleCast
{
    protected $layouts = [
        'wysiwyg' => \App\Nova\Flexible\Layouts\WysiwygLayout::class,
        'video' => \App\Nova\Flexible\Layouts\VideoLayout::class,
    ];
}
```

#### Having more control over the layout mappings

If you need to do complex things with your mappings instead of having a static array as shown above, you can override the `getLayoutMappings` method on your cast.

```php
namespace App\Casts;

class MyFlexibleCast extends FlexibleCast
{
    protected function getLayoutMappings()
    {
        $mappings = [];
        
        // Conditionally add mappings however you want
        
        return $mappings;
    }
}
```

### With the `HasFlexible` trait

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

## Layouts

### The Layouts Collection

Collections returned by `FlexibleCast` or the `HasFlexible` trait extend the original `Illuminate\Support\Collection`. These custom layout collections expose a `find(string $name)` method which returns the first layout having the given layout `$name`.

### The Layout instance

Layouts are some kind of _fake models_. They use Laravel's `HasAttributes` trait, which means you can define accessors & mutators for the layout's attributes.

Each Layout (or custom layout extending the base Layout) already implements the `HasFlexible` trait, meaning you can directly use the `$layout->flexible('my-sub-layout')` method to parse nested flexible content values.

Furthermore, it's also possible to access the Layout's properties using the following methods:

##### `name()`

Returns the layout's name.

##### `title()`

Returns the layout's title (as shown in Nova).

##### `key()`

Returns the layout's unique key (the layout's unique identifier).


## Going further

When using the Flexible Content field, you'll quickly come across of some use cases where the basics described above are not enough. That's why we developed the package in an extendable way, making it possible to easily add custom behaviors and/or capabilities to Field and its output.

## Custom Layout Classes

Sometimes, `addLayout` definitions can get quite long, or maybe you want them to be  shared with other `Flexible` fields. The answer to this is to extract your Layout into its own class.

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
    protected $title = 'Simple content section';

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

#### Limiting layouts per type
You can limit how many times the "Add Layout" button will appear for a specific type in a custom Layout class by setting the `$limit` attribute.

```php
/**
 * The maximum amount of this layout type that can be added
 */
protected $limit = 1;
```

You can specify any integer, or no integer at all; in that case it will default to 1.

You can create these Layout classes easily with the following artisan command
```
php artisan flexible:layout {classname?} {name?}

// Ex: php artisan flexible:layout SimpleWysiwygLayout wysiwyg
```


## Predefined Preset Classes

In addition to reusable Layout classes, you can go a step further and create `Preset` classes for your Flexible fields. These allow you to reuse your whole Flexible field anywhere you want. They also make it easier to make your Flexible fields dynamic, for example if you want to add Layouts conditionally. And last but not least, they also have the added benefit of cleaning up your Nova Resource classes, if your Flexible field has a lot of `addLayout` definitions.

```php
namespace App\Nova\Flexible\Presets;

use App\PageBlocks;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class WysiwygPagePreset extends Preset
{

    /**
     * The available blocks
     *
     * @var Illuminate\Support\Collection
     */
    protected $blocks;

    /**
     * Create a new preset instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->blocks = PageBlocks::orderBy('label')->get();
    }

    /**
     * Execute the preset configuration
     *
     * @return void
     */
    public function handle(Flexible $field)
    {
        $field->button('Add new block');
        $field->resolver(\App\Nova\Flexible\Resolvers\WysiwygPageResolver::class);
        $field->help('Go to the "<strong>Page blocks</strong>" Resource in order to add new WYSIWYG block types.');

        $this->blocks->each(function($block) use ($field) {
            $field->addLayout($block->title, $block->id, $block->getLayoutFields());
        });
    }
}
```

Please note that Preset classes are resolved using Laravel's Container, meaning you can type-hint any useful dependency in the Preset's `__construct()` method.

Once the Preset is defined, just reference its classname in your Flexible field using the `preset` method:
```php
Flexible::make('Content')
    ->preset(\App\Nova\Flexible\Presets\WysiwygPagePreset::class);
```

You can create these Preset classes easily with the following artisan command:
```
php artisan flexible:preset {classname?}

// Ex: php artisan flexible:preset WysiwygPagePreset
```


## Custom Resolver Classes

By default, the field takes advantage of a **JSON column** on your model's table. In some cases, a JSON attribute is just not the way to go. For example, you could want to store the values in another table (meaning you'll be using the Flexible Content field instead of a traditional BelongsToMany or HasMany field). No worries, we've got you covered!

First, create the new Resolver class. For convenience, this can be achieved using the following artisan command:
```
php artisan flexible:resolver {classname?}

// Ex: php artisan flexible:resolver WysiwygPageResolver
```

It will place the new Resolver class in your project's `app/Nova/Flexible/Resolvers` directory. Each Resolver should implement the `Whitecube\NovaFlexibleContent\Value\ResolverInterface` contract and therefore feature at least two methods: `set` and `get`.

### Resolving the field

The `get` method is used to resolve the field's content. It is responsible to retrieve the content from somewhere and return a collection of hydrated Layouts. For example, we could want to retrieve the values on a `blocks` table and transform them into Layout instance:

```php
/**
 * get the field's value
 *
 * @param  mixed  $resource
 * @param  string $attribute
 * @param  Whitecube\NovaFlexibleContent\Layouts\Collection $layouts
 * @return Illuminate\Support\Collection
 */
public function get($resource, $attribute, $layouts) {
    $blocks = $resource->blocks()->orderBy('order')->get();

    return $blocks->map(function($block) use ($layouts) {
        $layout = $layouts->find($block->name);

        if(!$layout) return;

        return $layout->duplicateAndHydrate($block->id, ['value' => $block->value]);
    })->filter();
}
```

### Filling the field

The `set` method is responsible for saving the Flexible's content somewhere the `get` method will be able to access it. In our example, it should store the data in a `blocks` table:

```php
/**
 * Set the field's value
 *
 * @param  mixed  $model
 * @param  string $attribute
 * @param  Illuminate\Support\Collection $groups
 * @return void
 */
public function set($model, $attribute, $groups)
{
    $class = get_class($model);

    $class::saved(function ($model) use ($groups) {
        $blocks = $groups->map(function($group, $index) {
            return [
                'name' => $group->name(),
                'value' => json_encode($group->getAttributes()),
                'order' => $index
            ];
        });

        // This is a quick & dirty example, syncing the models is probably a better idea.
        $model->blocks()->delete();
        $model->blocks()->createMany($blocks);
    });
}
```

## Usage with ebess/advanced-nova-media-library
By popular demand, we have added compatibility with the advanced-nova-media-library field.
This requires a few extra steps, as follows:

1. You must use a [custom layout class](https://whitecube.github.io/nova-flexible-content/#/?id=custom-layout-classes).
2. Your custom layout class must implement `Spatie\MediaLibrary\HasMedia` and use the `Whitecube\NovaFlexibleContent\Concerns\HasMediaLibrary` trait.
3. The parent model must implement `Spatie\MediaLibrary\HasMedia` and use the `Spatie\MediaLibrary\InteractsWithMedia` trait.

Quick example, consider `Post` has a flexible field with a `SliderLayout`:

```php
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;

class Post extends Model implements HasMedia
{
    use HasFlexible;
    use InteractsWithMedia;
}
```

```php
use Spatie\MediaLibrary\HasMedia;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Whitecube\NovaFlexibleContent\Concerns\HasMediaLibrary;

class SliderLayout extends Layout implements HasMedia
{
    use HasMediaLibrary;

    protected $name = 'sliderlayout';
    protected $title = 'SliderLayout';

    public function fields()
    {
        return [
            Images::make('Images', 'images')
        ];
    }

}
```

You can now call `getMedia('images')` on your `SliderLayout` instance.

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made and we would be very happy to merge useful pull requests.

Thanks!

### Unit tests

When adding a new feature or fixing a bug, please add corresponding unit tests. The current set of tests is limited, but every unit test added will improve the quality of the package.

Run PHPUnit by calling `composer test`.

## Made with ❤️ for open source
At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!
We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects.
