# Changelog

All notable changes to Templado are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.0.0] - 2024-XX-YY

Documentation can be found at https://docs.tempolado.io

Main changes from 4.x:

- Requires PHP 8.2+
- Templado 5.0 is quite a bit faster than 4.x
- `HTML` and `Snippet` got logically merged into a `Document`, representing both types
- A new `HTMLSerializer` has been introduced to generate HTML 5 output
- ViewModels now can have
    - public properties to satisfy the rendering needs
    - return a signal instance rather than relying on bool types
- Form data handling now supports nested structures as well as fields referenced by id
- A `Selector` can now be used in more cases to specify what areas of a document to apply the change to
- Optionally `vocab` aware
- Enhanced trace output in case of errors
- Snapshot support

## [5.0.0-rc.5] - 2024-10-22

### Changed

- The `ViewModelRenderer` now processes Signal results (Remove / Ignore) before performing a conditional `typeOf` check. This allows for removing of elements that would otherwise require a matching type. 


## [5.0.0-rc.4] - 2024-04-03

### Fixed

- Attribute removal didn't work properly
- Make `FormdataRenderer` not choke on unnamed elements

### Added

- `DocumentCollection::isEmpty` has been added

### Changed

- HTMLSerializer now trims Text nodes to avoid redundant line breaks


## [5.0.0-rc.3] - 2024-01-24

### Added

- Introduce Snapshot support

## [5.0.0-rc.2] - 2023-12-03

### Changed

- Rewrite HTMLSerializer to produce better HTML 5 output

### Added

- Allow ViewModel implementing __get or __call to signal "not defined"
- Support non utf-8 encoding in HTMLSerializer


### Removed

- NamespaceCleaningTransformation, as it is no longer needed


## [5.0.0-rc.1] - 2023-08-29

First Release Candidate for Templado 5.0.

[5.0.0-rc.3]: https://github.com/templado/engine/compare/5.0.0-rc.3...5.0.0-rc.4
[5.0.0-rc.3]: https://github.com/templado/engine/compare/5.0.0-rc.2...5.0.0-rc.3
[5.0.0-rc.2]: https://github.com/templado/engine/compare/5.0.0-rc.1...5.0.0-rc.2
[5.0.0-rc.1]: https://github.com/templado/engine/compare/4.2.4...5.0.0-rc.1
