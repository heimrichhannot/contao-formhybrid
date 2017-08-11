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

## Usage

### Install

With composer and Contao 4 Managed Edition: 

```
composer require heimrichhannot/contao-formhybrid ~2.8
```

### Palette handling
- permanentFields must be declared within editableFields in order to get right field position
- a field declared in editableFields whose selector isnt active or is not part of editable fields itself is removed from the final field set
- fields from active selectors that are not within editableFields are removed from final fields

### Inserttags

- {{form::FIELDNAME}} returns the formatted value from the field (select value instead of key)
- {{form_value::FIELDNAME}} returns the value of the field
- {{form_submission::FIELDNAME}} returns "field-label : formated field value"
- {{if}}
- {{elseif}}
- {{else}}
- {{endif}}

## Developers

### Notification center tokens

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



### Config Callbacks

Type | Description
---- | -----------
onload_callback | Add a 3rd parameter with boolean true to your onload_callbacks to run through them in frontend mode.  


### Additional eval dca config parameters


Key | Default | Example | Description
|----------|:-------------:|:-------------:|------:|
|allowedTags | null | <br><span><p> | Allow specific html tags inside input that will not be escaped (allowHtml must be true). allowHtml will be true by default if preserveTags, rte is set true within eval config.

### Frontend Form
We recommend to use [Contao Frontendedit](https://github.com/heimrichhannot/contao-frontendedit). If you can't or need more advanced options:

* Create a module and add all palette fields you want (see config.php FORMHYBRID_PALETTE_DEFAULT and tl_module.php)
* add following code to your module::compile() method to render the form 

```
use HeimrichHannot\FormHybrid\Form;

[...]
    $objForm = new Form($this);
    $this->Template->form = $objForm->generate();
[...]
```

More advanced configurations can be archived by extending the Form class and overwrite methods.
Following methods are availiable to overwrite (no complete list, see Form and DC_Hybrid classes):

|Method                              |Description|
|------------------------------------|-----------|
|onSubmitCallback(\DataContainer $dc)|Called after submitting the form, before writing to the database and sending confirmations)|
|afterSubmitCallback(\DataContainer $dc)|Called after submitting the form and after saving enitity and sending confirmations|
