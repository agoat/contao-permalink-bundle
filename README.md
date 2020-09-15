# Contao 4 permalink system

[![Version](https://img.shields.io/packagist/v/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink)
[![License](https://img.shields.io/packagist/l/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink)
[![Downloads](https://img.shields.io/packagist/dt/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink)

## Compatibility

Version 2.* is only compatible with Contao 4.9 LTS. Version 3.0.0 or higher will be compatible with the latest Contao 4.10+ version.

## About
A **permalink** is a link under which a certain content can be permanently found (Sometimes called guid or uuid). The name permalink is a short form for a permanent link.

Contao is a page-based CMS. It usually uses the alias to generate the url of a page (e.g. http://www.example.org/pagealias). For explicit content of modules, the alias is appended normally with a keyword (e.g. http://www.example.org/pagealias/items/newsalias or http://www.example.org/pagealias/newalias if auto_item is activated).

Since **permalinks** are absolute urls and are not only available for pages but also for other (content) contexts (such as new, events,...), any url schema can be established that do not have to be based on a page tree.

For a simple and semi-automatic creation of **permalinks** a **pattern** system similar to the **insert tags** can be used. For example, you can use `{{alias}}` to insert the page title and `{{parent}}` to insert the path of the parent page into the permalink.
Different patterns (insert tags) are available for each context.

## Notice
After installation into an existing project, all pages will be unavailable for the first time, unless you create permalinks for all pages (by simply enter a default permalink pattern in the settings, then select all pages and execute 'Generate permalinks').

For news items and events to work, a news respectively event reader module must be integrated in the layout on the forwarding page of the news archive and calendar.
## Install
### Contao manager
Search for the package and install it
```bash
agoat/contao-permalink
```

### Managed edition
Add the package
```bash
# Using the composer
composer require agoat/contao-permalink
```
Registration and configuration is done by the manager-plugin automatically.

### Standard edition
Add the package
```bash
# Using the composer
composer require agoat/contao-permalink
```
Register the bundle in the AppKernel
```php
# app/AppKernel.php
class AppKernel
{
    // ...
    public function registerBundles()
    {
        $bundles = [
            // ...
            // after Contao\CoreBundle\ContaoCoreBundle
            new Agoat\PermalinkBundle\AgoatPermalinkBundle(),
        ];
    }
}
```
