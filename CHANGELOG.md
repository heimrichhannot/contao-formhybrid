# Change Log
All notable changes to this project will be documented in this file.

## [2.5.58] - 2016-11-14

### Fixed
- set defaults from dca, also within FormHybrid::initialize(), when new entity was created within FormHybrid::create(), otherwise modifyDC/loadDC manipulation will not be considered

## [2.5.57] - 2016-11-11

### Added
- formHybridSingleSubmission flag, that will achieve that after successful submission only messages will get rendered, and no inputs or new entity
 will be generated

## [2.5.56] - 2016-11-11

### Fixed
- fixed scrolling issues
- Check entity create() against Ajax::isRelated(Form::FORMHYBRID_NAME) !== false instead of $this->isSubmitted() 
and added forceCreate attribute to force entity creation for non related ajax request


## [2.5.55] - 2016-11-08

### Added
- custom hash

## [2.5.54] - 2016-11-08

### Fixed
- moved messages in template for css reasons
- scrolling behavior

## [2.5.53] - 2016-11-04

### Fixed
- no objActiveRecord had been created for no entity forms
- "filtered" class was not correctly set

## [2.5.52] - 2016-11-03

### Added
- Set tstamp for activeRecord to 0 on create(), otherwise no record will be created, if nothing set by default

### Changed
- Check entity create() now against $this->isSubmitted() not against Ajax::isRelated(Form::FORMHYBRID_NAME) !== false

### Fixed
- Listen on document for `.formhybrid form[data-async]` form submit