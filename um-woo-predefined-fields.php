<?php
/**
 * Plugin Name:     Ultimate Member - Woo Predefined Fields
 * Description:     Extension to Ultimate Member for using Woo Fields in the UM Forms Designer and User edit at the Account Page.
 * Version:         2.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.8.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_WOO_Predefined_Fields {

    public $woo_meta_keys = array(  'billing_postcode',  'billing_city',       'billing_address_1',   'billing_address_2',
                                    'shipping_postcode', 'shipping_city',      'shipping_address_1',  'shipping_address_2',
                                    'paying_customer',   'billing_first_name', 'billing_last_name',   'billing_company',
                                    'billing_phone',     'billing_email',      'shipping_first_name', 'shipping_last_name',
                                    'shipping_company',  'shipping_phone',
                                );

    public $woo_meta_keys_select = array( 'billing_country',
                                          'billing_state',
                                          'shipping_country',
                                          'shipping_state',
                                        );

    function __construct( ) {

        add_filter( 'um_predefined_fields_hook',     array( $this, 'custom_predefined_fields_hook_woo' ), 10, 1 );
        add_filter( 'um_account_tab_general_fields', array( $this, 'um_account_predefined_fields_woo' ), 10, 2 );
        add_filter( 'um_settings_structure',         array( $this, 'um_settings_structure_predefined_fields_woo' ), 10, 1 );
        add_filter( 'um_get_form_fields',            array( $this, 'um_get_form_fields_woo' ), 9999, 2 );
        add_filter( 'um_user_edit_profile_fields',   array( $this, 'um_user_edit_profile_fields_woo' ), 10, 3 );


        add_filter( 'um_has_dropdown_options_source__billing_country',    array( $this, 'options_source_woo_country' ), 10, 1 );
        add_filter( 'um_has_dropdown_options_source__shipping_country',   array( $this, 'options_source_woo_country' ), 10, 1 );
        add_filter( 'um_get_field__billing_country',                      array( $this, 'get_field_woo_country' ), 10, 1 );
        add_filter( 'um_get_field__shipping_country',                     array( $this, 'get_field_woo_country' ), 10, 1 );
        add_filter( 'um_custom_dropdown_options_parent__billing_state',   array( $this, 'dropdown_options_parent_billing_state' ), 10, 2 );
        add_filter( 'um_custom_dropdown_options_parent__shipping_state',  array( $this, 'dropdown_options_parent_shipping_state' ), 10, 2 );
    }

    public function um_user_edit_profile_fields_woo( $fields, $args, $form_data ) {

        if ( isset( $fields['billing_state'] ) && isset( $args['billing_country'] ) && ! empty( $args['billing_country'] )) {
            $fields['billing_state']['options'] = WC()->countries->get_states( $args['billing_country'] );
        }

        if ( isset( $fields['shipping_state'] )) {
            $fields['shipping_state']['options'] = WC()->countries->get_states( $args['shipping_country'] );
        }

        return $fields;
    }

// Give a parent (countries) to woo states
    public function um_get_form_fields_woo( $form_fields, $set_id ) {

        if ( array_key_exists( 'billing_state', $form_fields )) {
            $form_fields['billing_state']['parent_dropdown_relationship'] = 'billing_country';

            if ( ! empty( um_user( 'billing_country' ) )) {
                $form_fields['billing_state']['options'] = WC()->countries->get_states( um_user( 'billing_country' ) );
            }
        }

        if ( array_key_exists( 'shipping_state', $form_fields )) {
            $form_fields['shipping_state']['parent_dropdown_relationship'] = 'shipping_country';

            if ( ! empty( um_user( 'shipping_country' ) )) {
                $form_fields['shipping_state']['options'] = WC()->countries->get_states( um_user( 'shipping_country' ) );
            }
        }

        return $form_fields;
    }

    public function um_submitted_data_value_woo( $value, $key, $data, $style ) {

        if ( in_array( $key, $this->woo_meta_keys_select ) && ! empty( $value ) && strlen( $value ) == 2 ) {
            $value = $this->convert_woo_to_um( $value, $key );
        }

        return $value;
    }

    public function convert_woo_to_um( $value, $key ) {

        switch( $key ) {

            case 'billing_country': $countries_woo = WC()->countries->get_allowed_countries();
                                    $value = isset( $countries_woo[$value] ) ? $countries_woo[$value] : $value;
                                    break;

            case 'billing_state':  if ( ! empty( um_user( 'billing_country' ) )) {
                                        $states = WC()->countries->get_states( um_user( 'billing_country' ) );
                                        $value = isset( $states[$value] ) ? $states[$value] : $value;
                                    }
                                    break;

            case 'shipping_country':$countries_woo = WC()->countries->get_allowed_countries();
                                    $value = isset( $countries_woo[$value] ) ? $countries_woo[$value] : $value;
                                    break;

            case 'shipping_state':  if ( ! empty( um_user( 'shipping_country' ) )) {
                                        $states = WC()->countries->get_states( um_user( 'shipping_country' ) );
                                        $value = isset( $states[$value] ) ? $states[$value] : $value;
                                    }
                                    break;
        }

        return $value;
    }

    public function dropdown_options_parent_billing_state( $parent, $form ) {

        return 'billing_country';
    }

    public function dropdown_options_parent_shipping_state( $parent, $form ) {

        return 'shipping_country';
    }

    public function options_source_woo_country( $bool ) {

        return 'um_get_field_woo_countries_dropdown';
    }

    public function options_source_woo_state( $bool ) {

        return 'um_get_field_woo_states_dropdown';
    }

    public function get_field_woo_country( $array ) {

        $countries_woo = WC()->countries->get_allowed_countries();

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );
        $array['options'] = $countries_woo;

        if ( ! empty( $country_selection )) { 
            $array['options'] = array();
            foreach( $countries_woo as $key => $country ) {
                if ( in_array( $key, $country_selection )) {
                    $array['options'][$key] = $country;
                }
            }
        }

        return $array;
    }

    public function custom_predefined_fields_hook_woo( $predefined_fields ) {

        foreach ( $this->woo_meta_keys as $woo_meta_key ) {

            $title = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key ) ), 'ultimate-member' );

            $predefined_fields[$woo_meta_key] = array(
                                                    'title'    => $title,
                                                    'metakey'  => $woo_meta_key,
                                                    'type'     => 'text',
                                                    'label'    => $title,
                                                    'required' => 0,
                                                    'public'   => 1,
                                                    'editable' => true,
                                                );
        }

        foreach ( $this->woo_meta_keys_select as $woo_meta_key ) {

            $title = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key ) ), 'ultimate-member' );

            if ( in_array( $woo_meta_key, array( 'billing_country', 'shipping_country' ))) {

                $options = WC()->countries->get_allowed_countries();
                $options_source = 'um_get_field_woo_countries_dropdown';
            }

            if ( in_array( $woo_meta_key, array( 'billing_state', 'shipping_state' ))) {

                $options = array( __( 'No states yet', 'ultimate-member' ));
                $options_source = 'um_get_field_woo_states_dropdown';
            }

            $predefined_fields[$woo_meta_key] = array(
                                                    'title'    => $title,
                                                    'metakey'  => $woo_meta_key,
                                                    'type'     => 'select',
                                                    'label'    => $title,
                                                    'required' => 0,
                                                    'public'   => 1,
                                                    'editable' => true,
                                                    'options'  => $options,
                                                    'custom_dropdown_options_source' => $options_source,
                                                );
        }

        return $predefined_fields;
    }

    public function um_account_predefined_fields_woo( $args, $shortcode_args ) {

        $options = UM()->options()->get( 'um_custom_predefined_fields_woo' );

        if ( ! empty( $options ) && is_array( $options ) ) {
            $args .= ',' . implode( ',', $options );
            $args = str_replace( ',single_user_password', '', $args ) . ',single_user_password';
        }

        return $args;
    }

    public function um_settings_structure_predefined_fields_woo( $settings_structure ) {

        $options = array();

        foreach ( $this->woo_meta_keys as $woo_meta_key ) {
            $options[$woo_meta_key] = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key ) ), 'ultimate-member' );
        }

        //foreach ( $this->woo_meta_keys_select as $woo_meta_key ) {
        //    $options[$woo_meta_key] = __( 'Woo ' . ucwords( str_replace( '_', ' ', $woo_meta_key ) ), 'ultimate-member' );
        //}

        $settings_structure['']['sections']['account']['fields'][] = array(
                                'id'      => 'um_custom_predefined_fields_woo',
                                'type'    => 'select',
                                'multi'   => true,
                                'options' => $options,
                                'label'   => __( 'Predefined Fields Woo - Account Form Fields for User Edit', 'ultimate-member' ),
                                'tooltip' => __( 'Select single or multiple Woo Fields for User Account Page Edit.', 'ultimate-member' )
                            );

        $settings_structure['']['sections']['account']['fields'][] = array(
                                'id'      => 'um_custom_predefined_woo_countries',
                                'type'    => 'select',
                                'multi'   => true,
                                'options' => WC()->countries->get_allowed_countries(),
                                'label'   => __( 'Predefined Fields Woo - Countries for User selection', 'ultimate-member' ),
                                'tooltip' => __( 'Select single or multiple Woo Countries for User selection. No selection use all available countries.', 'ultimate-member' )
                            );

        return $settings_structure;
    }

}

new UM_WOO_Predefined_Fields();

    function um_get_field_woo_countries_dropdown() {

        $countries_woo = WC()->countries->get_allowed_countries();

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );
        $countries = $countries_woo;

        if ( ! empty( $country_selection )) { 
            $countries = array();
            foreach( $countries_woo as $key => $country ) {
                if ( in_array( $key, $country_selection )) {
                    $countries[$key] = $country;
                }
            }
        }

        return $countries;     
    }

    function um_get_field_woo_states_dropdown( $options = false ) {

        if ( is_array( $options )) {

            return $options;
        }

        if ( isset( $_POST['parent_option_name'] ) && in_array( $_POST['parent_option_name'], array( 'billing_country', 'shipping_country' ))) {
            if ( isset( $_POST['parent_option'] ) && ! empty( $_POST['parent_option'] ) ) {

                $country = sanitize_text_field( $_POST['parent_option'] );
                $states = WC()->countries->get_states( $country );

                return $states;
            }
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] === 'um_select_options' ) {

            if ( isset( $_POST['parent_option'] ) && ! empty( $_POST['parent_option'] ) ) {

                $country = sanitize_text_field( $_POST['parent_option'] );
                $states = WC()->countries->get_states( $country );

                return $states;
            }
        }
    }
