# Changelog

All notable changes to Templado are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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

