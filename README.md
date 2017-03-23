# Formhybrid

Contains functionality for handling form submissions in the contao frontend.

Usage is simple: Include the default palette (_FORMHYBRID_PALETTE_DEFAULT_) in config.php into your module's tl_module file and remove the fields you don't need for your module.

-> Click [here](docs/formhybrid.png) for a diagram visualizing the interaction between the modules [formhybrid](https://github.com/heimrichhannot/contao-formhybrid), [formhybrid_list](https://github.com/heimrichhannot/contao-formhybrid_list), [frontendedit](https://github.com/heimrichhannot/contao-frontendedit) and [submissions](https://github.com/heimrichhannot/contao-submissions).

## Features

- form validation
- transforming of special field's values depending on their dca properties (e.g. date)
- ajax handling
- store submissions using submissions module if necessary
- optIn entity activation and notification handling added to module config and form creation process, add `formHybridAddOptIn` to your module configuration, create an opt-in notification and provide ##opt_in_link## inside text or html and add `\HeimrichHannot\FormHybrid\FormHybrid::addOptInFieldToTable([TABLE_NAME])` at the end of your DCA File

## Tokens

Formhybrid is notification center ready. It is possible to send 2 E-Mails on Form Submission to the sender (confirmation notification) and one to receiver (submission notification).
The following tokens are provided for usage:

| Tag   |      Example      |  Description |
|----------|:-------------:|------:|
| ##formsubmission_all## 	|  Firstname: Max\ | Contains the complete submission (contains also hidden form values like tstamp) formated as label and value.  |
|       									|  Lastname: Mustermann\ | |
|       									|  Zeitstempel: 1458030977 | |
| ##formsubmission## |  Firstname: Max\ | Contains the complete submission (without hidden fields) formatted as label and value.  |
|                    |  Lastname: Mustermann | |
| ##form_submission_*## | ##form_submission_country## -> Country: Germany | Contains the submission of a single field formatted as label and value (value takes options into consideration and return its corresponding value). |
| ##form_value_*## | ##form_value_country## -> de  | Contains submitted value of a field (no value is transformed by its  reference). |
| ##form_plain_*## | Input: <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p> \ | Strips html entities from the submitted value (helpful for plain text e-mails and tinymce textareas) |
|       					 | Output: -> ##form_value_text## -> Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. | |
| ##form_*## | ##form_county## -> Germany | Contains the formatted value of the submission  (value takes options into consideration and return its corresponding value). |
| ##opt_in_token## | ##opt_in_token## -> [TOKEN] / Generates only the opt-in the token. 
| ##opt_in_link## | ##opt_in_link## -> http://mywebsite.com/linkto-form?token=[TOKEN] | Generates the opt-in activation link.


## Inserttags

- {{form::FIELDNAME}} returns the formatted value from the field (select value instead of key)
- {{form_value::FIELDNAME}} returns the value of the field
- {{form_submission::FIELDNAME}} returns "field-label : formated field value"
- {{if}}
- {{elseif}}
- {{else}}
- {{endif}}


### Config Callbacks

Type | Description
---- | -----------
onload_callback | Add a 3rd parameter with boolean true to your onload_callbacks to run through them in frontend mode.  

