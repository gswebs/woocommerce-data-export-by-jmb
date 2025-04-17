<?php
namespace WooJMBExporter;

defined('ABSPATH') || exit;

abstract class BaseExporter {

    protected $data = [];

    abstract protected function fetch_data(): void;

    protected function output_csv(array $header, array $rows, string $filename = 'export.csv'): void {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment;filename={$filename}");
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, $header);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function export(): void {
        $this->fetch_data();
        
        $headers = [];
        
        if (in_array('order_id', $this->filters['selected_fields'])) {
            $headers[] = 'order_id';
        }
        if (in_array('email', $this->filters['selected_fields'])) {
            $headers[] = 'email';
        }
        if (in_array('name', $this->filters['selected_fields'])) {
            $headers[] = 'name';
        }
        if (in_array('phone', $this->filters['selected_fields'])) {
            $headers[] = 'phone';
        }
        if (in_array('products', $this->filters['selected_fields'])) {
            $headers[] = 'products';
        }
        if (in_array('status', $this->filters['selected_fields'])) {
            $headers[] = 'order_status';
        }
        if (in_array('order_total', $this->filters['selected_fields'])) {
            $headers[] = 'order_total';
        }
        if (in_array('order_date', $this->filters['selected_fields'])) {
            $headers[] = 'order_date';
        }
        
        $header_filter = apply_filters( 'jmb_export_order_data_header', $headers );
        
        $this->output_csv($header_filter, $this->data, 'order-emails.csv');
    }
    
}
