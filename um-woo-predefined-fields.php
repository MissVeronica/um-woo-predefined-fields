<?php
/**
 * Plugin Name:     Ultimate Member - Woo Predefined Fields
 * Description:     Extension to Ultimate Member for using Woo Fields in the UM Forms Designer and User edit at the Account Page.
 * Version:         1.1.0 development
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;

class UM_WOO_Predefined_Fields {

    public $woo_meta_keys = array(  'billing_postcode', 'billing_city', 'billing_address_1', 'billing_address_2', 'billing_state', 'billing_country', 
                                    'shipping_postcode', 'shipping_city', 'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_country', 
                                    'paying_customer', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_phone', 'billing_email', 
                                    'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_phone' );

    function __construct( ) {

        add_filter( 'um_predefined_fields_hook',     array( $this, 'custom_predefined_fields_hook_woo' ), 10, 1 );
        add_filter( 'um_account_tab_general_fields', array( $this, 'um_account_predefined_fields_woo' ), 10, 2 );
        add_filter( 'um_settings_structure',         array( $this, 'um_settings_structure_predefined_fields_woo' ), 10, 1 );

    }

    public function custom_predefined_fields_hook_woo( $predefined_fields ) {      

        foreach( $this->woo_meta_keys as $woo_meta_key ) {

            $title = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key )), 'ultimate-member' );

            $predefined_fields[$woo_meta_key] = array(
                
                            'title'    => $title,
                            'metakey'  => $woo_meta_key,
                            'type'     => 'text',
                            'label'    => $title,
                            'required' => 0,
                            'public'   => 1,
                            'editable' => 1,
            );

            if ( strpos( $woo_meta_key, 'country' )) {

                $predefined_fields[$woo_meta_key]['type']        = 'select';
                $predefined_fields[$woo_meta_key]['placeholder'] = __('Choose a Country','ultimate-member');
                $predefined_fields[$woo_meta_key]['options']     =  UM()->builtin()->get( 'countries' );
            }
        }

        return $predefined_fields;
    }

    public function um_account_predefined_fields_woo( $args, $shortcode_args ) {

        $options = UM()->options()->get( 'um_custom_predefined_fields_woo' );

        if( ! empty( $options ) && is_array( $options )) {
            $args .= ',' . implode( ',', $options );
            $args = str_replace( ',single_user_password', '', $args ) . ',single_user_password';
        }

        return $args;
    }

    public function um_settings_structure_predefined_fields_woo( $settings_structure ) {

        $options = array();

        foreach( $this->woo_meta_keys as $woo_meta_key ) {
            $options[$woo_meta_key] = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key )), 'ultimate-member' );
        }

        $settings_structure['']['sections']['account']['fields'][] = array(
            'id'      => 'um_custom_predefined_fields_woo',
            'type'    => 'select',
            'multi'   => true,
            'options' => $options,
            'label'   => __( 'Predefined Fields Woo - Account Form Fields for User Edit', 'ultimate-member' ),
            'tooltip' => __( 'Select single or multiple Woo Fields for User Account Page Edit.', 'ultimate-member' )
        );

        return $settings_structure;
    }
}

new UM_WOO_Predefined_Fields();
