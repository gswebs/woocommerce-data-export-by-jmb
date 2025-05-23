<?php
/**
 * Plugin Name: WooCommerce Orders Data Exporter By JMB
 * Plugin URI: https://github.com/gswebs/woocommerce-data-export-by-jmb
 * Description: Export data from WooCommerce orders as CSV.
 * Author: Gurjit Singh
 * Version: 0.1.0
 * Text Domain: jmb-woo-order-data-export
 * Requires Plugins: woocommerce
 */
 
defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/BaseExporter.php';
require_once plugin_dir_path(__FILE__) . 'includes/OrderDataExporter.php';

use WooJMBExporter\OrderDataExporter;

define( 'JMB_EXPORTER_PLUGIN_FILE_PATH', __FILE__ );

define( 'JMB_EXPORTERS_PLUGIN_BASENAME', plugin_basename( JMB_EXPORTER_PLUGIN_FILE_PATH ) );

final class Woo_JMB_Export_Plugin {

    public function __construct() {
        //add_action('admin_enqueue_scripts', [$this, 'jmb_export_enqueue']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_filter( 'plugin_action_links_' . JMB_EXPORTERS_PLUGIN_BASENAME, [$this, 'add_action_links'] );
        add_action('admin_post_export_orders_data', [$this, 'handle_export']);
    }
    
    public function fields(){
        $ar = array(
            'order_id' => 'Order Id',
            'email' => 'Email',
            'phone' => 'Phone',
            'name' => 'Name',
            'status' => 'Order Status',
            'products' => 'Products',
            'order_total' => 'Order Total',
            'order_date' => 'Order Date',
        );
        return apply_filters('export_fields', $ar);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Export Orders Data',
            'Export Orders Data',
            'manage_woocommerce',
            'export-orders-data',
            [$this, 'render_admin_page']
        );
    }

    public function add_action_links ( $links ) {
        $mylinks = array(
            '<a href="' . admin_url( 'admin.php?page=export-orders-data' ) . '" target="_blank">Export</a>',
        );
        return array_merge( $links, $mylinks );
    }

    public function render_admin_page() {
        $statuses = wc_get_order_statuses();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Export Orders Data', 'jmb-woo-order-data-export'); ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                
                <?php wp_nonce_field('order_export_action', 'order_export_nonce'); ?>
                
                <input type="hidden" name="action" value="export_orders_data">
                
                <table class="form-table">
                    <?php do_action('jmb_export_fields_rows'); ?>
                    <tr>
                        <th scope="row"><label for="date_from"><?php esc_html_e('From Date', 'jmb-woo-order-data-export'); ?></label></th>
                        <td><input type="date" id="date_from" name="date_from"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="date_to"><?php esc_html_e('To Date', 'jmb-woo-order-data-export'); ?></label></th>
                        <td><input type="date" id="date_to" name="date_to"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="order_status"><?php esc_html_e('Order Status', 'jmb-woo-order-data-export'); ?></label></th>
                        <td>
                            
                            <?php foreach ($statuses as $slug => $label): ?>
                                <label>
                                    <input type="checkbox" name="order_status[]" value="<?php echo esc_attr($slug); ?>">
                                    <?php esc_html_e($label, 'jmb-woo-order-data-export'); ?>
                                </label>
                            <?php endforeach; ?>
                                
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="order_status">Select Fields to Export</label></th>
                        <td>
                            
                            <?php foreach ($this->fields() as $slug => $label): ?>
                                <label>
                                    <input type="checkbox" name="export_fields[]" value="<?php echo esc_attr($slug); ?>">
                                    <?php esc_html_e($label, 'jmb-woo-order-data-export'); ?>
                                </label>
                            <?php endforeach; ?>
                            
                            <?php do_action('jmb_export_fields'); ?>
                            
                        </td>
                    </tr>
                    
                </table>
                    
                <input type="submit" name="export_orders" value="<?php esc_attr_e('Export Orders', 'jmb-woo-order-data-export'); ?>" class="button-primary">
                <?php do_action('jmb_after_export_btn'); ?>
            </form>
        </div>
        <?php
    }

    public function handle_export() {
        if (!isset($_POST['order_export_nonce']) || !wp_verify_nonce($_POST['order_export_nonce'], 'order_export_action')) {
            return;
        }
    
        // Check if export button was pressed
        if (isset($_POST['export_orders'])) {
            // Get selected fields (defaults to all fields if nothing selected)
            $selected_fields = isset($_POST['export_fields']) ? (array) $_POST['export_fields'] : array_keys($this->fields());
    
            // Pass the selected fields to the exporter
            $filter_args = [
                'selected_fields' => $selected_fields,
                'statuses'        => $_POST['order_status'] ?? [],
                'date_from'       => $_POST['date_from'] ?? '',
                'date_to'         => $_POST['date_to'] ?? ''
            ];
            
            $filters = apply_filters( 'jmb_export_order_data_filter_args', $filter_args );
    
            $exporter = new OrderDataExporter($filters);
            $exporter->export();
        }
    }

}

new Woo_JMB_Export_Plugin();