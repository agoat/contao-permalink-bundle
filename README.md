# Contao 4 permalink system

[![Version](https://img.shields.io/packagist/v/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink)
[![License](https://img.shields.io/packagist/l/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink)
[![Downloads](https://img.shields.io/packagist/dt/agoat/contao-permalink.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-permalink) 

## About
A permalink is a link under which a certain content can be permanently found. The name permalink is a short form for a permanent link.

Contao is a page-based CMS. It usually uses the alias to generate the url of a page (e.g. http://www.example.org/pagealias). If special contents of modules are rendered, the alias is appended normally with a keyword (e.g. http://www.example.org/pagealias/items/newsalias).

Since permalinks are absolute urls and can be used not only for pages but also for other content contexts (such as new, events,...), any url schema can be established which is not based on a page tree.

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
