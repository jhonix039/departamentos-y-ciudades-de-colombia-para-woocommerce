<?php
/**
 * Plugin Name: Departamentos y Ciudades de Colombia para Woocommerce
 * Description: Plugin modificado con los departementos y ciudades de Colombia
 * Version: 1.1.13
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: departamentos-y-ciudades-de-colombia-para-woocommerce
 * Domain Path: /languages
 * WC tested up to: 3.5
 * WC requires at least: 2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('plugins_loaded','states_places_colombia_init',0);

function states_places_colombia_smp_notices($classes, $notice){
    ?>
    <div class="<?php echo $classes; ?>">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function states_places_colombia_init(){
    load_plugin_textdomain('departamentos-y-ciudades-de-colombia-para-woocommerce',
        FALSE, dirname(plugin_basename(__FILE__)) . '/languages');

    /**
     * Check if WooCommerce is active
     */
    if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

        require_once ('includes/states-places.php');
        /**
         * Instantiate class
         */
        $GLOBALS['wc_states_places'] = new WC_States_Places_Colombia(__FILE__);


        require_once ('includes/filter-by-cities.php');

        add_filter( 'woocommerce_shipping_methods', 'add_filters_by_cities_method' );

        function add_filters_by_cities_method( $methods ) {
            $methods['filters_by_cities_shipping_method'] = 'Filters_By_Cities_Method';
            return $methods;
        }

        add_action( 'woocommerce_shipping_init', 'filters_by_cities_method' );

        global $pagenow;

        $subs = __( '<strong>Te gustaria conectar tu tienda con las principales transportadoras del país ?.
        Sé uno de los primeros</strong> ', 'departamentos-y-ciudades-de-colombia-para-woocommerce' ) .
            sprintf(__('%s', 'departamentos-y-ciudades-de-colombia-para-woocommerce' ),
                '<a target="_blank" class="button button-primary" href="https://saulmoralespa.com/shipping-colombia.php">' .
                __('Suscribete Gratis', 'departamentos-y-ciudades-de-colombia-para-woocommerce') . '</a>' );

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($subs) {
                states_places_colombia_smp_notices('notice notice-info is-dismissible', $subs);
            });
        }

        $fields_billing = get_fields('billing');

        $priority_field_notice = __( '<strong>La experiencia de compra puede verse afectada, 
            encarecidamente se recomienda cambiar el orden de los campos en el checkout. 
            El departamento debe estar antes que ciudad</strong> ', 'departamentos-y-ciudades-de-colombia-para-woocommerce' ) .
            sprintf(__('%s', 'departamentos-y-ciudades-de-colombia-para-woocommerce' ),
                '<a target="_blank" class="button button-secondary" href="https://es.wordpress.org/plugins/woo-checkout-field-editor-pro/">' .
                __('Corregir campos', 'departamentos-y-ciudades-de-colombia-para-woocommerce') . '</a>' );

        if ( is_admin() &&  ! defined( 'DOING_AJAX' ) ) {
            if ($fields_billing['billing_city']['priority'] < $fields_billing['billing_state']['priority']){
                add_action('admin_notices', function() use($priority_field_notice) {
                    states_places_colombia_smp_notices('notice notice-warning is-dismissible', $priority_field_notice);
                });
            }
        }

    }
}

function get_fields($key){
    $fields = array_filter(get_option('wc_fields_'. $key, array()));

    if(empty($fields) || sizeof($fields) == 0){
        if($key === 'billing' || $key === 'shipping'){
            $fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');

        }
    }
    return $fields;
}