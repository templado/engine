# Changelog

All notable changes to Templado are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.


##[3.0.1] - 2019-05-26

### Changed
* The `SnippetFileLoader` now returns `TempladoSnippts` for templado type snippets rather than `SimpleSnippets`, 
which never workd reliably due to DOMDocumentFragment issues.

### Fixed
* Fix iterable detection in ViewModelRenderer
* Fix potential index underflow in SnapshotDomNodelist


##[3.0.0] - 2019-05-16

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
