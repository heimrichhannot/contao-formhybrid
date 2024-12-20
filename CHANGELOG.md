# Changelog

All notable changes to this project will be documented in this file.

## [3.25.1] - 2024-12-04
- Fixed: exception when not in web context
- Changed: require at least contao 4.9
- Fixed: some legacy code

## [3.25.0] - 2024-05-23
- Changed: require at least contao 4.4
- Fixed: to lazy skip parameter

## [3.24.0] - 2023-08-03
- Changed: skip validation on disabled fields

## [3.23.3] - 2023-03-20
- Fixed: php 8 related issues

## [3.23.2] - 2023-03-14
- Fixed: php 8 related issues

## [3.23.1] - 2023-02-22
- Fixed: multicolumnwizard version conflict

## [3.23.0] - 2022-06-02
- Changed: dropped php 5 support
- Fixed: warning in php 8

## [3.22.2] - 2022-03-25
- Fixed: not working multicolumnwizard version conflict, now a exception is thrown if invalid version is used

## [3.22.1] - 2022-03-25
- Fixed: multicolumnwizard version conflict

## [3.22.0] - 2022-03-25
- Changed: due changes in contao, php and multicolumnwizard formhybrid is now only compatible to multicolumnwizard bundle
- Fixed: ContentFormHybridStart and ContentFormHybridStop not working properly with module fragment controllers
- Fixed: incompatible method signatures in FormMultiColumnWizard 

## [3.21.3] - 2022-03-23
- Fixed: possible contao manager version issues

## [3.21.2] - 2022-03-10
- Fixed: error due changes in contao 4.9.27 (renamed DC_Hybrid::reset() to DC_Hybrid::resetForm())

## [3.21.1] - 2022-02-14

- Fixed: array index issues in php 8+

## [3.21.0] - 2021-10-15
- Added: FormhybridModifyAsyncFormSubmitResponseEvent
- Added: blocks to formhybrid_default template

## [3.20.0] - 2021-08-31

- Added: php8 support

## [3.19.1] - 2021-07-22
- fixed FormhybridBeforeCreateWidgetEvent hook

## [3.19.0] - 2021-07-14

- added polish translation
- updated gitignore

## [3.18.1] - 2021-07-02

- fixed field and config callbacks to be arrays or callables

## [3.18.0] - 2021-06-04

- changes for privacy bundle

## [3.17.0] - 2021-04-08

- dispatch formhybrid_ajax_complete event after async form submit success

## [3.16.5] - 2021-03-19

- fixed array reference issue for hook

## [3.16.4] - 2021-01-28

- switched position of check for `removeAutoItemFromAction` -> don't run \Input::get('auto_item')
  before `removeAutoItemFromAction` has been checked since it removed `auto_item` from `\Input::$arrUnusedGet` which
  leads wrong `auto_items` being accepted (non-404) in regular pages!!

## [3.16.3] - 2020-10-29

- fixed unchecked checkbox value not correctly evaluated when editing existing entities

## [3.16.2] - 2020-10-20

- fixed bug concerning formHybridTransformGetParamsToHiddenFields

## [3.16.1] - 2020-09-14

- allow status_message 2.0

## [3.16.0] - 2020-09-09

- added formhybridBeforeRenderForm hook
- added DC_Hybrid::getInvalidFields()

## [3.15.1] - 2020-08-27

- added javascript event in `toggleSubpalette`

## [3.15.0] - 2020-08-17

- added formhybridBeforeCreateWidget hook
- bump minimum php version to 5.6

## [3.14.1] - 2020-08-07

- fixed formhybridBeforeCreateNotifications Hook

## [3.14.0] - 2020-07-14

- fixed error on non existent methods in DC_Hybrid

## [3.13.0] - 2020-06-05

- added formhybridOnCreateInstance hook

## [3.12.0] - 2020-03-31

- removed logging for non existing dca

## [3.11.0] - 2020-03-11

- fixed dc_multilingual related bug

## [3.10.7] - 2020-02-18

- fixed applying of email-attachments if they are not listed in the database

## [3.10.6] - 2020-02-14

- fixed pdf export for contao4 environments

## [3.10.5] - 2020-02-07

- added exception handling to DC_Hybrid::generateField

## [3.10.4] - 2020-02-04

- fixed field name assignment order in DC_Hybrid::generateField

## [3.10.3] - 2019-12-10

- fixed database issue in Form::findOptInModelInstance() when formHybridOptInModelRetrievalProperty not set

## [3.10.2] - 2019-11-26

- form action issue

## [3.10.1] - 2019-11-01

- fixed empty get field issue

## [3.10.0] - 2019-10-01

### Added

- custom form id suffix

## [3.9.4] - 2019-05-27

### Fixed

- issue with concatenated subpalettes (e.g. `source_external`) -> if selector isn't set, fields are now correctly
  removed from main palette

## [3.9.3] - 2019-03-20

### Fixed

- PHP 7.3 issue

## [3.9.2] - 2019-03-18

### Fixed

- missing dependency

## [3.9.1] - 2019-03-18

### Added

- fields and language for GET-Parameter configuration

## [3.9.0] - 2019-03-18

### Added

- optional setting form values by GET-parameter

## [3.8.2] - 2019-02-27

### Fixed

- form action issue

## [3.8.1] - 2019-02-22

### Fixed

- success message shown on next form visit if opt-in redirect page is set

## [3.8.0] - 2018-12-12

### Changed

- captcha render as `MadeYourDay\\Contao\\Form\\AntispamField` revoked, otherwise `email` or `url` named form fields
  will be overwritten (empty), not compatible right now with any other entity than `tl_form_field`

## [3.7.0] - 2018-12-19

### Added

- `formhybridBeforeCreateNotifications` Hook to Form class

## [3.6.0] - 2018-12-12

### Added

- captcha now renders as `MadeYourDay\\Contao\\Form\\AntispamField` if installed on contao 3

## [3.5.4] - 2018-12-11

### Fixed

- html_entity_decode for multi-dimensional array values

## [3.5.3] - 2018-12-07

### Fixed

- hash generation for dynamic IDs again

## [3.5.2] - 2018-12-07

### Fixed

- hash generation for dynamic IDs

## [3.5.1] - 2018-11-22

### Added

- `TL_COMPONENT` entry in config.php to disable formhybrid css & js on demand in page layout

## [3.5.0] - 2018-11-16

### Added

- option to filter fields that will be encoded within opt-in token, this fixes issues with too long links for outlook,
  when submission is very long

## [3.4.4] - 2018-11-12

### Added

- update timestamp on opt-in

## [3.4.3] - 2018-10-16

### Added

- privacy prefill data to opt-in token

## [3.4.2] - 2018-09-19

### Fixed

- bug in privacy activation

## [3.4.1] - 2018-09-19

### Added

- support for heimrichhannot/contao-privacy-api-bundle

## [3.4.0] - 2018-09-19

### Removed

- heimrichhannot/contao-multifileupload

## [3.3.0] - 2018-09-18

### Changed

- `Form::sendOptInNotification` $arrToken parameter is now reference

## [3.2.3] - 2018-09-17

### Fixed

- issues for privacy protocol

## [3.2.2] - 2018-07-03

### Fixed

- backend error when there is no message created for opt in message type

## [3.2.1] - 2018-06-27

### Fixed

- refactoring for privacy module

## [3.2.0] - 2018-06-26

### Added

- support for opt-in emails heimrichhannot/contao-privacy
- support for salutations in opt-in emails

## [3.1.0] - 2018-05-17

### Added

- support for heimrichhannot/contao-privacy

## [3.0.6] - 2018-03-06

### Fixed

- ajax redirect for IE

## [3.0.5] - 2018-03-02

### Fixed

- correctly update `$objWidget->value` from `save_callback`

## [3.0.4] - 2018-03-02

### Fixed

- `allowHtml` for widget now correctly renders `allowedTags` from widget `['eval']['allowedTags']` or fallback
  from `\Config::get('allowedTags')`

## [3.0.3] - 2018-03-02

### Fixed

- unsubscription for subscriber subscribed before activating opt-out was not possible due empty opt-out token field. Now
  opt-out token is set, if field is added to dca and opt-out not explicit enabled. **This won't added tokens to already
  existing database entries** (see readme for more information)**!**

## [3.0.2] - 2018-03-02

### Fixed

- wrong translation used for opt-out jumpto

## [3.0.1] - 2018-02-16

- load `tl_module` fields of type `pageTree` related pages `lazy`

## [3.0.0] - 2018-02-06

### Changed

- drop legacy submission and confirmation E-Mail support (including avisota submission and confirmation messages), use
  notification center instead (reduce `tl_module` column count)
- dopped following database columns:
    - formHybridSendSubmissionViaEmail
    - formHybridSubmissionMailSender
    - formHybridSubmissionMailRecipient
    - formHybridSubmissionMailSubject
    - formHybridSubmissionMailText
    - formHybridSubmissionMailTemplate
    - formHybridSubmissionMailAttachment
    - formHybridSendConfirmationViaEmail
    - formHybridConfirmationMailRecipientField
    - formHybridConfirmationMailSender
    - formHybridConfirmationMailSubject
    - formHybridConfirmationMailText
    - formHybridConfirmationMailTemplate
    - formHybridConfirmationMailAttachment
    - formHybridSubmissionAvisotaMessage
    - formHybridSubmissionAvisotaSalutationGroup
    - formHybridConfirmationAvisotaMessage
    - formHybridConfirmationAvisotaSalutationGroup

## [2.10.8] - 2018-01-24

### Changed

- *JumpTo load to lazy

## [2.10.7] - 2018-01-24

### Fixed

- `Cannot use object of type Closure as array` for `onload_callback` in `DC_Hybrid`

### Changed

- licence LGPL-3.0+ is now LGPL-3.0-or-later

## [2.10.6] - 2017-12-14

#### Changed

- added magic getter and setter to FrontendWidget to overwrite the automatic encription in Widget class

## [2.10.5] - 2017-12-11

### Changed

- moved `doIdDependentRedirectToEntity` (create()) after oncreate_callback (initialize())

## [2.10.4] - 2017-12-08

### Fixed

- if `includeBlankOption` is active and field is submitted via `toggleSubpalette` the blankOption value was not added to
  the valid options array and caused an `ResponseError`

## [2.10.3] - 2017-11-29

### Fixed

- typo in opt-in message

## [2.10.2] - 2017-11-21

### Added

- forceCreate check for oncreate callback in modal

## [2.10.1] - 2017-11-02

### Added

- submit button css class btn-lg

## [2.10.0] - 2017-11-02

### Added

- subpalette wrapper div

## [2.9.5] - 2017-08-21

### Changed

- set objModule as active record in removeSubscription and activeSubmission

## [2.9.4] - 2017-08-21

### Added

- Opt-in and opt-out jumpTo page selection and redirect

## [2.9.3] - 2017-08-16

### Changed

- moved token generation to own static method

## [2.9.2] - 2017-08-16

### Added

- dublicate entity flag

### Changed

- update readme

## [2.9.1] - 2017-08-14

### Changed

- updated languages (opt-out)

## [2.9.0] - 2017-08-14

### Added

- Opt-out function

### Changed

- opt-in/opt-out check in seperate functions (Form.php)
- moved set optinconfirmedproperty code to remove unnecessary database call
- updated readme
- updated english translation

## [2.8.19] - 2017-08-11

### Added

- OptInConfirmed property select

### Changed

- OptInToken deletion via model class (in `Form::activateSubmission()`)
- updated readme

## [2.8.18] - 2017-08-09

### Fixed

- DCA-Loading in Module class for Contao 4

## [2.8.17] - 2017-07-27

### Fixed

- auto_item removal from action

## [2.8.16] - 2017-07-25

### Added

- handling for modal ajax query params

## [2.8.15] - 2017-07-18

### Changed

- use `$arrRecipients` and `$arrSubmissionData` as reference in `Form::sendConfirmationEmail`
- use `$arrRecipients` and `$arrSubmissionData` as reference in `Form::sendConfirmationSubmission`

## [2.8.14] - 2017-07-18

### Changed

- added css class subpalette to subpalette template (templates/form/formhybrid_default_sub.html5)

## [2.8.13] - 2017-07-07

### Fixed

- `html_entity_decode` within `FrontendWidget::decodeEntities()` now ignores objects and only supports strings

## [2.8.12] - 2017-06-20

### Fixed

- updateWidget() signature

## [2.8.11] - 2017-06-20

### Fixed

- versions dep
- refactoring of tl_module

## [2.8.10] - 2017-05-09

### Fixed

- php 7 support

## [2.8.9] - 2017-05-03

### Fixed

- `FormReadonlyField` option value issue

## [2.8.8] - 2017-04-19

### Fixed

- do not escape values for `FormReadonlyField` if dca config contains  `options`, `options_callback` or `foreignKey`

## [2.8.7] - 2017-04-11

### Fixed

- do not escape entities for `FormReadonlyField` of type `multifileupload`

## [2.8.6] - 2017-04-11

### Fixed

- tested `tl_module.formHybridForcePaletteRelation` palette handling and fixed small bug

## [2.8.5] - 2017-04-11

### Changed

- `tl_module.formHybridForcePaletteRelation` field added (default: true) and force palette relation handling by default!

## [2.8.4] - 2017-04-10

### Fixed

- array|string handling exceptions

## [2.8.3] - 2017-04-06

### Changed

- changed "String" to "StringUtil" and "->$callback" to "->{$callback}"

## [2.8.2] - 2017-04-06

### Changed

- added PHP 7.0 support, fixed contao-core dependency

## [2.8.1] - 2017-03-31

### Fixed

- XSS related fixed within `FormReadonlyField`

## [2.8.0] - 2017-04-05

### Changed

- make usage of `HeimrichHannot\Request\Request` class for all Request data to have better Test handling

### Fixed

- numerous subpalette, typeselector fixes due to unit testing with `heimrichhannot\contao-formhybrid_tests`
- XSS related fixes due to usage of `HeimrichHannot\Request\Request` instead of `Contao\Input` as we have different
  allowedTags for inputs

## [2.7.2] - 2017-03-30

### Fixed

- redirect to same page without optIn token after activation

## [2.7.1] - 2017-03-29

### Fixed

- for no selector dca activePaletteFields were empty

## [2.7.0] - 2017-03-29

### Fixed

- multiple palette, subpalette bugs that did typeSelector, concatenated typeSelector and subPalette handling wrong and
  returned wrong number of fields
- if no palette is defined, and no typeSelector field is present in editable fields, access to all fields is possible (
  also usage of subpalette fields within palette, as long as subpalette selector is not present)
- if palette is defined, or typeSelector field is present, only fields within the appropriate palette are accessible

### Added

- palette and subpalette unit testing within `heimrichhannot/contao-formhybrid_tests`

## [2.6.26] - 2017-03-27

### Fixed

- on ajax request, when request token expired, nothin was returned, now we set the form data based on $_POST with new
  request token (provided by `heimrichhannot/contao-ajax`) to the new entity
- inactive fields from non active subpalettes were removed, uncared that a type selector palette is active, where the
  fields are present in the palette itself

## [2.6.25] - 2017-03-23

### Added

- optIn entity activation and notification handling added to module config and form creation process,
  add `formHybridAddOptIn` to your module configuration, create an opt-in notification and provide ##opt_in_link##
  inside text or html and add `\HeimrichHannot\FormHybrid\FormHybrid::addOptInFieldToTable([TABLE_NAME])` at the end of
  your DCA File

## [2.6.24] - 2017-03-23

### Changed

- asyncFormSubmit: disable submit fields, add dots `...` after text to indicate user that loading is in progress and set
  fields on submit to readonly

## [2.6.23] - 2017-03-14

### Fixed

- button smashing on subpalette selectors is not impossible

## [2.6.22] - 2017-03-10

### Fixed

- added property removeAutoItemFromAction

## [2.6.21] - 2017-03-02

### Fixed

- added updateAction()

## [2.6.20] - 2017-02-24

### Fixed

- DC_Hybrid::onUpdateCallback $arrOriginalRow argument type removed

## [2.6.19] - 2017-02-24

### Fixed

- missing fields due to type selector issues

## [2.6.18] - 2017-02-24

### Fixed

- position of export -> now before processing notifications so that eported files can now be attached
- transformed array() to []

## [2.6.17] - 2017-02-23

### Fixed

- set default values from DCA after modifyDC has run, and move oncreate_callbacks after setDefaults() within initialize

## [2.6.16] - 2017-02-22

### Fixed

- exporter now contains linkedTable for skip fields

## [2.6.15] - 2017-02-08

### Fixed

- $arrData for subFields is now set correctly

## [2.6.14] - 2017-02-07

### Added

- added ne submit labels unsubscribe & subscribe

## [2.6.13] - 2017-02-07

### Fixed

- set active record from default only if value === null

## [2.6.12] - 2017-02-03

### Fixed

- removed html2text from commposer.json since already contained in haste_plus

## [2.6.11] - 2017-01-31

### Added

- generation of export-submit button
- handling of export form frontend

## [2.6.10] - 2017-01-16

### Changed

- formHybridSingleSubmission default value changed to false

## [2.6.9] - 2017-01-10

### Changed

- html2text/html2text dependency updated to 4.x

## [2.6.8] - 2017-01-04

### Fixed

- fixed minor bugs

## [2.6.7] - 2016-12-05

### Fixed

- prevent redirectAfterSubmission for isFilterForm Config

## [2.6.6] - 2016-12-03

### Changed

- set autocomplete="off" for all forms by default, can be enabled with formHybridEnableAutoComplete in tl_module,
  autocomplete="off" prevents browser back button restore field values from last submission

## [2.6.5] - 2016-12-02

### Fixed

- clear $_SESSION['FILES'] after form submission

### Changed

- multifileupload 1.1.0 is now required and functionality moved to from DC_Hybrid to multifileupload module

## [2.6.4] - 2016-12-01

### Fixed

- FormConfiguration, when instance is created from FormConfiguration object

## [2.6.3] - 2016-11-24

### Fixed

- fixed exporter bug

## [2.6.2] - 2016-11-24

### Added

- added opportunity to export multiple files

## [2.6.1] - 2016-11-23

### Fixed

- restore renderStart, renderStop functionality
- redirectAfterSubmission, reset form if resetAfterSubmission was enabled but no redirect was initiated after submission

## [2.6.0] - 2016-11-21

### Added

- added support for exporter

## [2.5.60] - 2016-11-15

### Changed

- added 3rd parameter to addEditableField that will force field add, regardless of existence in active palette

## [2.5.59] - 2016-11-14

### Fixed

- catch DC_Hybrid::createVersion(), if Version::setFromModel returns null when for example `enableVersioning` for the
  DCA is not set true

## [2.5.58] - 2016-11-14

### Fixed

- set defaults from dca, also within FormHybrid::initialize(), when new entity was created within FormHybrid::create(),
  otherwise modifyDC/loadDC manipulation will not be considered

## [2.5.57] - 2016-11-11

### Added

- formHybridSingleSubmission flag, that will achieve that after successful submission only messages will get rendered,
  and no inputs or new entity will be generated

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
