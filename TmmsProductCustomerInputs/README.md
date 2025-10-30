# TmmsProductCustomerInputs

## An extension for up to 5 different customer inputs at order line items for [Shopware 6](https://github.com/shopware/platform).

### Description

A shopware 6 extension for _up to 5 different customer inputs at order line items_. 

The customer input can be done on the offcanvas cart page, in the shopping cart or on the order confirm page. However, it is also possible in the QuickView from Shopware and on the product detail page (also when assigning a product page layout) under the aforementioned restrictions.

For each product you can set the following things in the custom fields area for each custom field set:
- whether an input should be possible 
- the field type of the input (a single or multi-line input field, a number field, a checkbox field, a date time field, a date field, a time field or a selection field)
- the label before the input
- the placeholder for the input
- whether the field is a required field.

A start date, an end date, dates to be excluded or a start and end time can also be set for the date and / or time field. The values for the selection field are separated by commas and set in the corresponding field. At the number field a minimum value, a maximum value and the steps can also be specified. At the single-line and multi-line input field the maximum number of characters can also be specified.

### Possible Configurations for the product detail page
- show the input fields on product detail page
- select if an information message should be shown under the customer inputs

### Possible Configurations for the checkout
- select if the input should be shown in an accordion area in the shopping cart and on the confirm page
- select if a dividing line should be shown between product and input
- show the input on offcanvas cart page
- show the input fields on cart page
- show the input fields on confirm page
- show empty input fields
- select if the input can be changed

### Possible Configurations for the customer account
- select if the repeat order button should be shown
- select if the repeat order function should take over the customer input

### Possible Configurations for the date and time field
- select the date format
- select the date and time format
- select if manual input in the input field is allowed
- select if weeks numbers should be shown
- select if the start date or time should be set as the default value
- select if a language-dependent calendar based on localization should be used

### Possible Configurations for the required field
- select if required fields can be changed in the shopping cart (however, it is not possible to intercept the sending of the form)
- select if required fields can be changed on the confirm page
- select if empty required fields are saved in the checkout
- select if the required field should be highlighted in color

### Additional Configurations
- select if a detail button should be shown in the navigation, when the input has been activated for a product
- show the input fields in the quickview from shopware
- set the number of rows for the multi-line input field
- select if the enter key for the field types single-line input field, number field, date and time field, date field and time field should be blocked
- select if the unselected checkbox fields should be transferred as a value

The inputs are displayed for each line item both on the finish page and in the customer account in the frontend as well as in the line items in the administration and on the documents. 

In addition to the label, the actual input and, in the case of a checkbox field, the placeholder for each line item is also adopted. In the case of a checkbox field, the text after the checkbox is set using the placeholder. 

Intercepting the sending of the form, insofar as the input is a required field, is only possible on the product detail page and on the confirm page, because a corresponding form is only available on these pages and the shopping cart can be skipped, for example by the customer with a customer account and the quickview from shopware already preloads all products with its forms. 

As soon as the customer makes an input in the field, the change is saved. The input is saved in the session so that the input is available to the customer until the customer completes the purchase or clears the browser cache.

### Available snippets for customizing
- titleLabel
- placeholderLabel
- openingRoundBracket
- closedRoundBracket
- selectedValue
- unselectedValue
- requiredLabel
- validityNumberStepsLabel
- validityNumberLabel
- dateTimeFormat
- dateFormat
- informationMessage
- accordionHeadingLabel
