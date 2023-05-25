# Ultimate Member - Woo Predefined Fields
Extension to Ultimate Member for using Woo Fields in the UM Forms Designer and User edit at the Account Page.

## UM Settings
UM Settings -> General -> Account
Select single or multiple Woo Fields for User Account Page Edit.

## Translations or Text changes
Use the "Say What?" plugin with text domain ultimate-member.
1. Original string: Woo Shipping Phone
2. Text domain: ultimate-member
3. Replacement string: Shipping Mobile Phone Number

## Updates
### Version 1.2.0
Country and State dropdowns with Woo sources and excluded from UM predefined fields to make Country and State fields supporting "Choices Callback Feature in UM 2.1+" https://docs.ultimatemember.com/article/1539-choices-callback-feature-in-um-2-1.

1. Choices Callback for the Country meta_keys (billing_country, shipping_country) is um_get_field_woo_countries_dropdown
2. Choices Callback for the State meta_keys (billing_state, shipping_state) is um_get_field_woo_states_dropdown and select your Woo Country title as "Parent Option" for the state.
3.  Country and State dropdowns can not be edited in the User Account page.

### Version 1.2.0
PHP code improvements

## Installation
Download the zip file and install as a WP Plugin, activate the plugin.
