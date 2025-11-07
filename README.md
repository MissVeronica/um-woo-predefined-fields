# Ultimate Member - Woo Predefined Fields
Extension to Ultimate Member for using Woo Fields in the UM Forms Builder and User Account Page edit of Woo fields.

## UM Settings -> General -> Account
* Account Form Woo Fields for User Edit - Select single or multiple Woo Fields (metakeys) for User Account Page Edit.
* Countries for User selection - Select single or multiple Woo Countries for User selection.
* Members Directory Labels - Click to activate Profile form custom Labels for the Woo fields in the Members Directory.
* Note! Single country will not require a select field for the country at Registration/Profile pages and State can be edited in the Account page

## Woo meta_keys as UM predefined fields
* billing_address_1
* billing_address_2
* billing_city
* billing_company
* billing_country
* billing_email
* billing_first_name
* billing_last_name
* billing_phone
* billing_postcode
* billing_state
* paying_customer
* shipping_address_1
* shipping_address_2
* shipping_city
* shipping_company
* shipping_country
* shipping_first_name
* shipping_last_name
* shipping_phone
* shipping_postcode
* shipping_state

## Translations or Text changes
Use the "Say What?" plugin with text domain ultimate-member.

## References
Woo Account Endpoints https://github.com/MissVeronica/um-woo-account-endpoints

## Updates
### Version 2.0.0
* Country and State dropdowns with Registration and Profile forms but not with the User Account page.
* Selection of Country subset.

### Version 2.1.0
* Code improvement for WP User Info modal with UM submitted Info display.

### Version 2.3.0
* Single country will not require a select field for the country at Registration/Profile pages and State can be edited in the Account page

### Version 2.3.1/2.3.2/2.3.4/2.3.5
* Test if WooCommerce is activated to avoid PHP errors 
* Code improvements
* "All countries" requires selecting all with shift-include in dropdown at UM Settings -> General -> Account

### Version 2.4.0
* Updated for UM 2.8.3

### Version 2.5.0/2.5.1/2.5.2
* Code improvements

### Version 2.6.0
* Improved backend User interface title format: Woo {woo label} - {woo meta_key}
* Titles, Labels and some Placeholders updated from WooCommerce source code incl language translations if required.
* Members Directory user selectable either Profile Form custom Labels or WooCommerce Labels.
* Some code and security improvements

## Installation and Updates
* Install and update by downloading the plugin ZIP file via the green "Code" button
* Install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
* Activate the Plugin: Ultimate Member - Woo Predefined Fields
