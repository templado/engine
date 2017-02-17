# Templado
A pragmatic approach to templating for PHP 7.x

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/theseer/templado/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/theseer/templado/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/120c85ad-0c88-45ff-b7af-0fb6c4a85a4f/mini.png)](https://insight.sensiolabs.com/projects/120c85ad-0c88-45ff-b7af-0fb6c4a85a4f)
[![Build Status](https://travis-ci.org/theseer/templado.svg?branch=master)](https://travis-ci.org/theseer/templado)
[![Code Coverage](https://scrutinizer-ci.com/g/theseer/templado/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/theseer/templado/?branch=master)

### Motivation

Most of today's templating engines mix code for the required rendering logic with HTML markup in one file and require
the developers to learn their respective language.

Templado follows a different approach on templating: Being in part inspired by [Tempan](https://github.com/watoki/tempan),
Templado relies solely on plain HTML markup. The limited amount of display logic required is contained with the engine
and triggered by the view model when it's applied to the Page.

### Always ready to preview

As a Templado template is plain HTML, previewing is as easy as opening the HTML file with a browser - example data can
and should be included as the engine will clean it up based on the view model upon rendering.

### No markup duplication
 
Templado features asset support, mapping a list of assets based on their ID into a given HTML Page. To automate this
process [Templado CLI](https://github.com/theseer/templado-cli) can be used. Combined with a File watcher in your IDE,
you can have an always up-to-date set of HTML pages without ever writing a block twice.

### Form handling included

To make form handling even more easy, Templado comes with explicit HTML Form support. Based on supplied Input data,
Templado will repopulate the HTML form and even include your CSRF protection code.

### Custom transformations and Filters

Templado allows for custom transformations, like adding a class to every ```a``` tag and string based replacements upon
serialization.

## Examples

Usage examples can be found in the [example section](https://github.com/theseer/templado/tree/master/examples)
