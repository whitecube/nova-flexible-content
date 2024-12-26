# Upgrading

## From v2 to v3

`\Wmt\NovaFlexibleContent\Layouts\LayoutInterface::duplicateAndHydrate` method now accepts 3rd parameter `bool $show`. Update all your implementation to match the new signature.

Update all your custom resolvers to consider the `show` field alongside `key,` `layout`, and `attributes.`
