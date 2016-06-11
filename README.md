Bundle to Mass-Modify Twig Templates
====

What does this bundle do?
----

This bundle does not do anything by itself - it helps you to modify your existing twig templates by replacing parsed
nodes with some other twig code.

It could be used to mass-edit many templates at once.

For usage example see [maba/webpack-migration-bundle](https://github.com/mariusbalcytis/webpack-migration-bundle).

Installation
----

```shell
composer require maba/twig-template-modification-bundle
```

Inside `AppKernel`:

```php
new Maba\Bundle\WebpackBundle\MabaTwigTemplateModificationBundle(),
```

Usage
----

Make service which implements `TwigNodeReplacerInterface`.

```php
use Maba\Bundle\TwigTemplateModificationBundle\Service\TwigNodeReplacerInterface;
use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use Twig_Node as Node;

class MyNodeReplacer implements TwigNodeReplacerInterface
{
        /**
         * @param Node $node
         * @param TemplateContext $context
         *
         * @return null|string string if this node should be replaced with given twig code
         */
        public function replace(Node $node, TemplateContext $context)
        {
            if ($node instanceof NameExpression && $node->getAttribute('name') === 'my_var') {
                return '123';
            }
            return null;
        }
}
```

`replace` method will be called on each and every node in every twig template.

If string is returned (not `null`), node will be replaced with given string content.

`TemplateContext` holds template name. You can also add notices to it (for example unable to replace some node)
and hold attributes (and reuse them later - same context is passed for each node in same template).

Initiate template rewrites with this code:

```php
$factory = $container->get('maba_twig_template_modification.factory.files_replacer');
$replacer = $factory->createFilesReplacer(new MyNodeReplacer());

// both arguments (closures) are optional
$replacer->replace(function($filePath, $contents, $notices) {
    // log or write to output before replacing file in $filePath with $contents
}, function (array $notices) use ($output) {
    // log or write to output notices
});
```

You could also create replacer with dependency injection using factory service:

```xml
<service id="acme.files_replacer"
         class="Maba\Bundle\TwigTemplateModificationBundle\Service\FilesReplacer">
     <factory service="maba_twig_template_modification.factory.files_replacer" method="createFilesReplacer"/>
    <argument type="collection">
        <argument type="service" id="acme.my_node_replacer"/>
    </argument>
</service>
```

**Important!** Code replaces content in templates in your bundles and app directory - be sure to run it only
when using version control system with no changes uncommitted.

## Running tests

[![Travis status](https://travis-ci.org/mariusbalcytis/twig-template-modification-bundle.svg?branch=master)](https://travis-ci.org/mariusbalcytis/twig-template-modification-bundle)

```shell
composer install
vendor/bin/codecept run
```
