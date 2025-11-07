<?php
/**
 * Plugin Name:     Ultimate Member - Woo Predefined Fields
 * Description:     Extension to Ultimate Member for using Woo Fields in the UM Forms Builder and User edit at the Account Page.
 * Version:         2.6.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Plugin URI:      https://github.com/MissVeronica/um-woo-predefined-fields
 * Update URI:      https://github.com/MissVeronica/um-woo-predefined-fields
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.10.6
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

    public $woo_directory_labels = false;
    public $woo_labels           = false;
    public $labels               = array();
    public $titles               = array();
    public $placeholders         = array();

    public $woo_address_types    = array(   'billing_',
                                            'shipping_',
                                            'paying_',
                                        );

    function __construct( ) {

        define( 'Plugin_Basename__WPF', plugin_basename( __FILE__ ));

        add_filter( 'um_predefined_fields_hook',          array( $this, 'custom_predefined_fields_hook_woo' ), 10, 1 );
        add_filter( 'um_account_tab_general_fields',      array( $this, 'um_account_predefined_fields_woo' ), 10, 2 );
        add_filter( 'um_settings_structure',              array( $this, 'um_settings_structure_predefined_fields_woo' ), 10, 1 );
        add_filter( 'um_submitted_data_value',            array( $this, 'um_submitted_data_value_woo' ), 10, 4 );
        add_filter( 'um_get_form_fields',                 array( $this, 'um_get_form_fields_woo' ), 9999, 2 );
        add_filter( 'um_user_edit_profile_fields',        array( $this, 'um_user_edit_profile_fields_woo' ), 10, 3 );
        add_action( 'um_registration_set_extra_data',     array( $this, 'um_registration_set_woo_country' ), 10, 3 );

        add_filter( 'um_has_dropdown_options_source__billing_country',    array( $this, 'options_source_woo_country' ), 10, 1 );
        add_filter( 'um_has_dropdown_options_source__shipping_country',   array( $this, 'options_source_woo_country' ), 10, 1 );
        add_filter( 'um_get_field__billing_country',                      array( $this, 'get_field_woo_country' ), 10, 1 );
        add_filter( 'um_get_field__shipping_country',                     array( $this, 'get_field_woo_country' ), 10, 1 );
        add_filter( 'um_custom_dropdown_options_parent__billing_state',   array( $this, 'dropdown_options_parent_billing_state' ), 10, 2 );
        add_filter( 'um_custom_dropdown_options_parent__shipping_state',  array( $this, 'dropdown_options_parent_shipping_state' ), 10, 2 );

        add_filter( 'um_ajax_get_members_data',                           array( $this, 'um_ajax_get_members_profile_card_woo_labels' ), 10, 3 );

        add_filter( 'plugin_action_links_' . Plugin_Basename__WPF,        array( $this, 'plugin_settings_link' ), 10, 1 );
    }

    function plugin_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&section=account';
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function um_registration_set_woo_country( $user_id, $args, $form_data ) {

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );

        if ( is_array( $country_selection ) && count( $country_selection ) == 1 ) {

            if ( isset( $args['billing_state'] )) {
                $country = sanitize_meta( 'billing_country', $country_selection[0], 'user' );
                update_user_meta( $user_id, 'billing_country', $country, 'user' );
            }

            if ( isset( $args['shipping_state'] )) {
                $country = sanitize_meta( 'shipping_country', $country_selection[0], 'user' );
                update_user_meta( $user_id, 'shipping_country', $country, 'user' );
            }
        }
    }

    public function um_user_edit_profile_fields_woo( $fields, $args, $form_data ) {

        if ( isset( $fields['billing_state'] ) && isset( $args['billing_country'] ) && ! empty( $args['billing_country'] )) {
            $fields['billing_state']['options'] = $this->get_woo_states( $args['billing_country'] );
        }

        if ( isset( $fields['shipping_state'] ) && isset( $args['shipping_country'] ) && ! empty( $args['shipping_country'] )) {
            $fields['shipping_state']['options'] = $this->get_woo_states( $args['shipping_country'] );
        }

        return $fields;
    }

// Give a parent (countries) to woo states
    public function um_get_form_fields_woo( $form_fields, $set_id ) {

        if ( is_array( $form_fields )) {

            $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );

            if ( array_key_exists( 'billing_state', $form_fields )) {

                if ( is_array( $country_selection ) && count( $country_selection ) != 1 ) {
                    $form_fields['billing_state']['parent_dropdown_relationship'] = 'billing_country';
                }

                if ( ! empty( um_user( 'billing_country' ) )) {
                    $form_fields['billing_state']['options'] = $this->get_woo_states( um_user( 'billing_country' ) );
                }
            }

            if ( array_key_exists( 'shipping_state', $form_fields )) {

                if ( is_array( $country_selection ) && count( $country_selection ) != 1 ) {
                    $form_fields['shipping_state']['parent_dropdown_relationship'] = 'shipping_country';
                }

                if ( ! empty( um_user( 'shipping_country' ) )) {
                    $form_fields['shipping_state']['options'] = $this->get_woo_states( um_user( 'shipping_country' ) );
                }
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

            case 'billing_country': $countries_woo = $this->get_woo_countries();
                                    $value = isset( $countries_woo[$value] ) ? $countries_woo[$value] : $value;
                                    break;

            case 'billing_state':  if ( ! empty( um_user( 'billing_country' ) )) {
                                        $states = $this->get_woo_states( um_user( 'billing_country' ) );
                                        $value = isset( $states[$value] ) ? $states[$value] : $value;
                                    }
                                    break;

            case 'shipping_country':$countries_woo = $this->get_woo_countries();
                                    $value = isset( $countries_woo[$value] ) ? $countries_woo[$value] : $value;
                                    break;

            case 'shipping_state':  if ( ! empty( um_user( 'shipping_country' ) )) {
                                        $states = $this->get_woo_states( um_user( 'shipping_country' ) );
                                        $value = isset( $states[$value] ) ? $states[$value] : $value;
                                    }
                                    break;
        }

        return $value;
    }

    public function dropdown_options_parent_billing_state( $parent, $form ) {

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );
        if ( is_array( $country_selection ) && count( $country_selection ) == 1 ) {
            return false;
        }

        return 'billing_country';
    }

    public function dropdown_options_parent_shipping_state( $parent, $form ) {

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );
        if ( is_array( $country_selection ) && count( $country_selection ) == 1 ) {
            return false;
        }

        return 'shipping_country';
    }

    public function options_source_woo_country( $bool ) {

        return 'um_get_field_woo_countries_dropdown';
    }

    public function options_source_woo_state( $bool ) {

        return 'um_get_field_woo_states_dropdown';
    }

    public function get_field_woo_country( $array ) {

        $countries_woo = $this->get_woo_countries();

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

    public function get_woo_um_form_labels() {

        if ( $this->woo_directory_labels === false ) {

            $this->woo_directory_labels = array();

            $um_forms     = get_posts( array( 'post_type' => 'um_form', 'numberposts' => -1, 'post_status' => array( 'publish' )));
            $woo_metakeys = array_merge( $this->woo_meta_keys, $this->woo_meta_keys_select );

            foreach ( $um_forms as $um_form ) {

                $um_form_mode = get_post_meta( $um_form->ID, '_um_mode', true  );
                if ( in_array( $um_form_mode, array( 'profile', 'register' ) )) {

                    $form_fields = get_post_meta( $um_form->ID, '_um_custom_fields', true );
                    foreach( $form_fields as $key => $form_field ) {

                        if ( in_array( $key, $woo_metakeys )) {
                            $this->woo_directory_labels[$key] = $form_field['label'];
                        }
                    }
                }
            }
        }
    }

    public function um_ajax_get_members_profile_card_woo_labels( $data_array, $user_id, $directory_data ) {

        if ( UM()->options()->get( 'um_custom_predefined_woo_directory' ) == 1 ) {

            $this->get_woo_um_form_labels();

            if ( ! empty( $this->woo_directory_labels )) {

                foreach( $this->woo_directory_labels as $metakey => $label ) {

                    if ( isset( $data_array["label_{$metakey}"] )) {
                        $data_array["label_{$metakey}"] = esc_html( $label );
                    }
                }
            }
        }

        return $data_array;
    }

    public function get_woo_defined_labels() {

        if ( $this->woo_labels === false ) {

            $this->woo_labels = array();

            if ( UM()->dependencies()->woocommerce_active_check() ) {

                $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );
                $country = ( is_array( $country_selection ) && count( $country_selection ) == 1 ) ? $country_selection[0] : '';

                foreach( $this->woo_address_types as $woo_address_type ) {
                    $woo_fields = WC()->countries->get_address_fields( $country, $woo_address_type );

                    foreach( $woo_fields as $woo_meta_key => $woo_field ) {

                        if ( isset( $woo_field['label'] ) && ! empty( $woo_field['label'] )) {
                            $this->woo_labels[$woo_meta_key]['label']       = esc_html( $woo_field['label'] );
                            $this->woo_labels[$woo_meta_key]['placeholder'] = ( isset( $woo_field['placeholder'] ) && ! empty( $woo_field['placeholder'] )) ? esc_html( $woo_field['placeholder'] ) : '';
                        }
                    }
                }
            }
        }
    }

    public function get_titles_and_labels()  {

        $this->get_woo_defined_labels();

        if ( empty( $this->label )) {

            foreach ( array_merge( $this->woo_meta_keys, $this->woo_meta_keys_select ) as $woo_meta_key ) {

                $this->labels[$woo_meta_key] = ( isset( $this->woo_labels[$woo_meta_key]['label'] ) && ! empty( $this->woo_labels[$woo_meta_key]['label'] )) ? $this->woo_labels[$woo_meta_key]['label'] : esc_html( ucwords( str_replace( '_', ' ', $woo_meta_key )));
                $this->labels[$woo_meta_key] = $this->labels[$woo_meta_key];

                $this->titles[$woo_meta_key] = sprintf( esc_html__( 'Woo  %s', 'ultimate-member' ), $this->labels[$woo_meta_key] ) . ' - ' . $woo_meta_key;
                $this->titles[$woo_meta_key] = $this->titles[$woo_meta_key];

                $this->placeholders[$woo_meta_key] = ( isset( $this->woo_labels[$woo_meta_key]['placeholder'] ) && ! empty( $this->woo_labels[$woo_meta_key]['placeholder'] ) ? $this->woo_labels[$woo_meta_key]['placeholder'] : '' );
            }
        }
    }

    public function custom_predefined_fields_hook_woo( $predefined_fields ) {

        $this->get_titles_and_labels();

        foreach ( $this->woo_meta_keys as $woo_meta_key ) {

            $predefined_fields[$woo_meta_key] = array(
                                                        'title'       => $this->titles[$woo_meta_key],
                                                        'metakey'     => $woo_meta_key,
                                                        'type'        => 'text',
                                                        'label'       => $this->labels[$woo_meta_key],
                                                        'placeholder' => $this->placeholders[$woo_meta_key],
                                                        'required'    => 0,
                                                        'public'      => 1,
                                                        'editable'    => true,
                                                    );

        }

        foreach ( $this->woo_meta_keys_select as $woo_meta_key ) {

            if ( in_array( $woo_meta_key, array( 'billing_country', 'shipping_country' ))) {

                $options = $this->get_woo_countries();
                $options_source = 'um_get_field_woo_countries_dropdown';
            }

            if ( in_array( $woo_meta_key, array( 'billing_state', 'shipping_state' ))) {

                $options = array( esc_html__( 'No states yet', 'ultimate-member' ));
                $options_source = 'um_get_field_woo_states_dropdown';
            }

            $predefined_fields[$woo_meta_key] = array(
                                                        'title'    => $this->titles[$woo_meta_key],
                                                        'metakey'  => $woo_meta_key,
                                                        'type'     => 'select',
                                                        'label'    => $this->labels[$woo_meta_key],
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

        $acoount_woo_options = UM()->options()->get( 'um_custom_predefined_fields_woo' );

        if ( is_array( $acoount_woo_options ) && ! empty( $acoount_woo_options )) {

            $acoount_woo_options = array_map( 'sanitize_key', $acoount_woo_options );
            $woo_metakeys = array_merge( $this->woo_meta_keys, $this->woo_meta_keys_select );

            foreach( $acoount_woo_options as $key => $acoount_woo_option ) {
                if ( ! in_array( $acoount_woo_option, $woo_metakeys )) {
                    unset( $acoount_woo_options[$key] );
                }
            }

            if ( ! empty( $acoount_woo_options ) ) {
                $args .= ',' . implode( ',', $acoount_woo_options );
                $args  = str_replace( ',single_user_password', '', $args ) . ',single_user_password';
            }
        }

        return $args;
    }

    public function get_possible_plugin_update( $plugin ) {

        $plugin_data = get_plugin_data( __FILE__ );

        $documention = sprintf( ' <a href="%s" target="_blank" title="%s">%s</a>',
                                        esc_url( $plugin_data['PluginURI'] ),
                                        esc_html__( 'GitHub plugin documentation and download', 'ultimate-member' ),
                                        esc_html__( 'Documentation', 'ultimate-member' ));

        $description = sprintf( esc_html__( 'Plugin "Woo Predefined Fields" version %s - Tested with UM 2.10.6 - %s', 'ultimate-member' ),
                                                                            $plugin_data['Version'], $documention );
        return $description;
    }

    public function um_settings_structure_predefined_fields_woo( $settings_structure ) {

// Possible performans improvement User select which metakeys to activate.

        $options = array();
        $prefix  = '&nbsp; * &nbsp;';

        $this->get_titles_and_labels();

        foreach ( $this->woo_meta_keys as $woo_meta_key ) {

            $options[$woo_meta_key] = $this->titles[$woo_meta_key];
        }

        $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );

        if ( is_array( $country_selection ) && count( $country_selection ) == 1 ) {

            $options['billing_state']  = $this->titles['billing_state'];
            $options['shipping_state'] = $this->titles['shipping_state'];
        }

        $settings_structure['']['sections']['account']['form_sections']['predefined_woo']['title']       = esc_html__( 'Woo Predefined Fields', 'ultimate-member' );
        $settings_structure['']['sections']['account']['form_sections']['predefined_woo']['description'] = $this->get_possible_plugin_update( 'um-woo-predefined-fields' );

        $settings_structure['']['sections']['account']['form_sections']['predefined_woo']['fields'][] = array(
                                'id'          => 'um_custom_predefined_fields_woo',
                                'type'        => 'select',
                                'multi'       => true,
                                'options'     => $options,
                                'label'       => $prefix . esc_html__( 'Account Form Woo Fields for User Edit', 'ultimate-member' ),
                                'description' => esc_html__( 'Select single or multiple Woo Fields (metakeys) for User Account Page Edit.', 'ultimate-member' )
                            );

        $settings_structure['']['sections']['account']['form_sections']['predefined_woo']['fields'][] = array(
                                'id'          => 'um_custom_predefined_woo_countries',
                                'type'        => 'select',
                                'multi'       => true,
                                'options'     => $this->get_woo_countries(),
                                'label'       => $prefix . esc_html__( 'Countries for User selection', 'ultimate-member' ),
                                'description' => esc_html__( 'Select single or multiple Woo Countries for User selection.', 'ultimate-member' )
                            );

        $settings_structure['']['sections']['account']['form_sections']['predefined_woo']['fields'][] = array(
                                'id'             => 'um_custom_predefined_woo_directory',
                                'type'           => 'checkbox',
                                'label'          => $prefix . esc_html__( 'Members Directory Labels', 'ultimate-member' ),
                                'checkbox_label' => esc_html__( "Click to activate Profile form custom Labels for the Woo fields in the Members Directory.", 'ultimate-member' ),
                            );

        return $settings_structure;
    }

    public function get_woo_countries() {

        $countries = array( esc_html__( 'Woo not active', 'ultimate-member' ));
        if ( UM()->dependencies()->woocommerce_active_check() ) {
            $countries = WC()->countries->get_allowed_countries();
        }

        return $countries;
    }

    public function get_woo_states( $country ) {

        $states = array( esc_html__( 'Woo not active', 'ultimate-member' ));
        if ( UM()->dependencies()->woocommerce_active_check() ) {
            $states = WC()->countries->get_states( $country );
        }

        return $states;
    }


}

new UM_WOO_Predefined_Fields();



    function um_get_field_woo_countries_dropdown() {

        if ( UM()->dependencies()->woocommerce_active_check() ) {

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

        } else {
            return array( esc_html__( 'Woo not active', 'ultimate-member' ) );
        }
    }

    function um_get_field_woo_states_dropdown( $options = false ) {

        if ( UM()->dependencies()->woocommerce_active_check() ) {

            $country_selection = UM()->options()->get( 'um_custom_predefined_woo_countries' );

            if ( is_array( $country_selection ) && count( $country_selection ) == 1 ) {
                $country = sanitize_text_field( $country_selection[0] );
                $states = WC()->countries->get_states( $country );

                return $states;
            }

            if ( is_array( $options )) {

                return $options;
            }

            if (( isset( $_POST['parent_option_name'] ) && in_array( $_POST['parent_option_name'], array( 'billing_country', 'shipping_country' )))
                    ||
                ( isset( $_POST['action'] ) && $_POST['action'] === 'um_select_options' )) {

                if ( isset( $_POST['parent_option'] ) && ! empty( $_POST['parent_option'] ) ) {

                    $country = sanitize_text_field( $_POST['parent_option'] );

                    if( in_array( $country, $country_selection )) {
                        $states = WC()->countries->get_states( $country );
                        return $states;
                    }
                }
            }

        } else {
            return array( esc_html__( 'Woo not active', 'ultimate-member' ) );
        }

        return array();
    }

