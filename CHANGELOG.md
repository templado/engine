# Changelog

All notable changes to Templado are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.2.0] - 2022-04-28

### Changed

* Include information from libxml_get_last_error() in TempladoException message (Thanks @sebastianbergmann)

### Added

* `Html::extractAsSnippets` to allow extraction of elements as snippets 


## [4.1.4] - 2022-01-25

### Fixed

* `SnapshotAttributeList`: PHP 8.1 deprecation notices


## [4.1.3] - 2021-09-07

### Added

* Added option to `SnippetLoader` to explictly specify snippet id to use when loading


## [4.1.2] - 2021-08-25

### Fixed

* Regression from 4.1.1: `SnippetRenderer` was missing a type check


## [4.1.1] - 2021-08-25

### Fixed

* Regression from 4.0.5 in `SnippetRenderer`: A multi element `TempladoSnippet` only handled
  the first element when applied in replace mode, ignoring the sibling elements

* `TempladoSnippet` returned the replaced context node anchored in the wrong document


## [4.1.0] - 2021-08-10

### Fixed

* The FormDataRenderer now also finds a `<form>` element when it's the document root element

### Added

* Introduced fluent interface for easier chaining to `Html::applySnippet()`, `Html::applySnippets()`, `Html::applyViewModel()`,
  `Html::applyFormData()`, `Html::applyCSRFProtection()`, and `Html::applyTransformation()`

* Added `Html::toSnippet` to allow for easier re-use and nesting

* Added new convenience method `Html::applySnippet` to reduce boilerplate code when only adding a single snippet

## [4.0.6] - 2021-03-20

### Fixed

* Consecutive nodes with the same property got incorrectly always treated as if in array processing mode and thus the
  presumed redundant ones got removed.

  
## [4.0.5] - 2021-01-09

### Fixed

* An invalid duplicate ID exception could be triggered in `SnippetRenderer` because of an invalid use of DOMNodelist, reported privately by @spriebsch

## [4.0.4] - 2020-10-25

### Fixed

* Regression: A Null type check in `ViewModelRenderer` was not working correctly, producing false positives

## [4.0.3] - 2020-10-08

### Fixed

* CSRFProtectionRenderer could have leaked an internal DOMXPath reference over multiple invocations

### Changed

* The `ViewModelRenderer` now produces a more helpful error messages when it encounters unsupported types

## [4.0.2] - 2019-11-21

### Fixed

* [#17](https://github.com/templado/engine/issues/17): CSRFPotectionRenderer fails to update existing token element when in xml namespace

## [4.0.1] - 2019-09-20

### Changed

* The `Iterator` returned by the `Selection` class is no longer based on `DOMNodeList` but on `ArrayIterator`. This is
  necessary to not have the list reset on changes to the DOM but to provide a stable Snapshots of the selection result.   
  
## [4.0.0] - 2019-09-13

### Changed

* The `ViewModelRenderer` now ensures that text content can no longer be used to create syntactically invalid markup.
  While this is technically a bug fix, some  may consider it a BC break as it also removes the "non-feature" of
  having a model returning xml/html markup. If you need html fragments to be inserted, please use a snippet instead.
* `SnapshotDOMNodelist` no longer implements \Iterator and \Countable as those became superflous

### Fixed

* ViewModelRenderer: Might skip a node when processing arrays due to invalid use of foreach on iteration

## [3.0.1] - 2019-05-26

### Changed 

* The `SnippetFileLoader` now returns `TempladoSnippts` for templado type snippets rather than `SimpleSnippets`, 
which never workd reliably due to DOMDocumentFragment issues.

### Fixed

* Fix iterable detection in ViewModelRenderer
* Fix potential index underflow in SnapshotDomNodelist

## [3.0.0] - 2019-05-16

### Changed

* Raise minimum PHP Version to 7.2
* Explicitly clear libxml error buffer (PHP internal global state for the win, Thanks @spriebsch)

### Fixed

* Detect and prohibit potential endless recursion in SnippetRenderer (Thanks @spriebsch)
  

## [2.3.2] - 2018-10-26

### Fixed

* Simpler implementation for [#10](https://github.com/templado/engine/issues/10) that also works with PHP 7.3


## [2.3.1] - 2018-10-26

### Fixed

* [#10](https://github.com/templado/engine/issues/10): NULL returned in EmtpyElementsFilter

## [2.3.0] - 2018-10-25

### Added

* Implement model resolving using RDFa Lite resource annotation
* Implement prefix support for view model resolving using RDFa prefix annotation
* Added a transformation to optionally strip RDFa lite attributes

## [2.2.7] - 2018-04-11

### Fixed

* [#9](https://github.com/templado/engine/issues/9): Removing an Attribute breaks Iteration in View ModelRenderer

## [2.2.6] - 2018-02-06

### Fixed

* Ensure iterators implement countable to make them work as array (Regression from 2.2.5)

## [2.2.5] - 2018-02-06

### Fixed

* [#8](https://github.com/templado/engine/issues/8): Error: Call to a member function removeChild() on null in ViewModelRenderer

### Changed

* ViewModelRenderer: An empty array will be treated as boolean false, leading to removal of context node
* ViewModelRenderer: Trying to remove the document element now throws an exception
* ViewModelRenderer: Trying to apply an array with multiple items on document element now throws an exception 

## [2.2.4] - 2018-01-20

### Fixed

* [#7](https://github.com/templado/engine/issues/7): SnapshotNodeList: Undefined offset -1

## [2.2.3] - 2017-12-07

### Fixed

* [#5](https://github.com/templado/engine/issues/5): Arguments of content in snippets are not processed the same way as non-snippet content?

## [2.2.2] - 2017-12-04

### Fixed

* [#4](https://github.com/templado/engine/issues/4): Snippets and CSRFTokens

## [2.2.1] - 2017-11-27

* ViewModelRenderer: Attributes with dashes are now explicitly mapped (e.g. data-foo to getDataFoo) 

## [2.2.0] - 2017-11-24

* ViewModelRenderer: RDFa Lite attribute 'typeof' now supported for use as conditional selection
* Minor tweaks to improve performance

## [2.1.2] - 2017-10-06

### Fixed

* Regression from 2.1.1

## [2.1.1] - 2017-10-06

### Fixed

* [#2](https://github.com/templado/engine/issues/2): Problems with multiple properties

## [2.1.0] - 2017-08-17

### Added

* ViewModel: Iterators are now supported as an alternative to array return types 

### Changed

* ViewModel: Call to asString() now gets the original nodeValue passed along

### Fixed

* [#1](https://github.com/templado/engine/issues/1): XPathSelector produces Uncaught TypeError with empty query

## [2.0.0] - 2017-08-02

### Changed

* Renamed Asset to Snippet, as that's what it truely is
* Renamed Page to HTML
* Snippet Interface changed: `applyTo()` now has to return a `\DOMNode` 

### Fixed

* Snippet processing: Replace now works recursively

### Removed

* Examples got moved into their own project

## [1.1.0] - 2017-04-17

### Changed

* Added support for text only assets

## [1.0.0] - 2017-04-16

* Initial Release

[4.1.3]: https://github.com/templado/engine/compare/4.1.2...4.1.3
[4.1.2]: https://github.com/templado/engine/compare/4.1.1...4.1.2
[4.1.1]: https://github.com/templado/engine/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/templado/engine/compare/4.0.6...4.1.0
[4.0.6]: https://github.com/templado/engine/compare/4.0.5...4.0.6
[4.0.5]: https://github.com/templado/engine/compare/4.0.4...4.0.5
[4.0.4]: https://github.com/templado/engine/compare/4.0.3...4.0.4
[4.0.3]: https://github.com/templado/engine/compare/4.0.2...4.0.3
[4.0.2]: https://github.com/templado/engine/compare/4.0.1...4.0.2
[4.0.1]: https://github.com/templado/engine/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/templado/engine/compare/3.0.1...4.0.0
[3.0.1]: https://github.com/templado/engine/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/templado/engine/compare/2.3.1...3.0.0
[2.3.2]: https://github.com/templado/engine/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/templado/engine/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/templado/engine/compare/2.2.7...2.3.0
[2.2.7]: https://github.com/templado/engine/compare/2.2.6...2.2.7
[2.2.6]: https://github.com/templado/engine/compare/2.2.5...2.2.6
[2.2.5]: https://github.com/templado/engine/compare/2.2.4...2.2.5
[2.2.4]: https://github.com/templado/engine/compare/2.2.3...2.2.4
[2.2.3]: https://github.com/templado/engine/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/templado/engine/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/templado/engine/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/templado/engine/compare/2.1.2...2.2.0
[2.1.2]: https://github.com/templado/engine/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/templado/engine/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/templado/engine/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/templado/engine/compare/1.1.0...2.0.0
[1.1.0]: https://github.com/templado/engine/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/templado/engine/compare/38a2f74f3af2693193e344df0e1f9130d264835c...1.0.0
