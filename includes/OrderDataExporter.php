<?php
namespace WooJMBExporter;

use WC_Order;

defined('ABSPATH') || exit;

class OrderDataExporter extends BaseExporter {

    protected $filters = [];

    public function __construct($filters = []) {
        $this->filters = $filters;
    }
    
    protected function fetch_data(): void {
        $args = [
            'limit'  => -1,
            'return' => 'ids',
        ];
    
        // Ensure status filter is passed as a string
        if (!empty($this->filters['statuses'])) {
            $args['status'] = implode(',', array_map('sanitize_text_field', $this->filters['statuses']));
        }
    
        // Add date range filters if provided
        if (!empty($this->filters['date_from'])) {
            $args['date_created']['after'] = $this->filters['date_from'] . ' 00:00:00';
        }
    
        if (!empty($this->filters['date_to'])) {
            $args['date_created']['before'] = $this->filters['date_to'] . ' 23:59:59';
        }
        
        $args_ar = apply_filters('jmb_fetch_data_args', $args);
    
        // Ensure we only get real orders and not refunds
        $order_ids = wc_get_orders(array_merge($args_ar, [
            'type' => 'shop_order',  // Only actual orders (not refunds)
        ]));
    
        $export_data = [];
        
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;
            
            $products = [];
            
            // Prepare the data based on selected fields
            $order_data = [];
            
            if (in_array('order_id', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_id();
            }
            
            if (in_array('email', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_billing_email();
            }
    
            if (in_array('name', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            }
    
            if (in_array('phone', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_billing_phone();
            }
    
            if (in_array('products', $this->filters['selected_fields'])) {
                foreach ($order->get_items() as $item) {
                    $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
                }
                
                $order_data[] = implode(', ', $products);
            }
    
            if (in_array('status', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_status();
            }
    
            if (in_array('order_total', $this->filters['selected_fields'])) {
                $currency_code   = get_woocommerce_currency();
                $currency = html_entity_decode(get_woocommerce_currency_symbol($currency_code));
                $total = $currency . number_format((float) $order->get_total(), 2);
                $order_data[] = $total;
            }
    
            if (in_array('order_date', $this->filters['selected_fields'])) {
                $order_data[] = $order->get_date_created()->format('Y-m-d H:i:s');
            }
    
            // Add the data to the export array
            $export_data[] = $order_data;
            
        }
    
        $this->data = apply_filters('jmb_export_data_array', $export_data);
    }

}
