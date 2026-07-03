<?php

defined('ABSPATH') or exit;

final class RY_IFAMEGO_Invoice extends RY_IFAMEGO_Abstract_Invoice
{
    protected static ?self $_instance = null;

    private array $api_test_url = [
        'get' => 'https://invoice-api.amego.tw/json/f0401',
        'invalid' => 'https://invoice-api.amego.tw/json/f0501',
    ];

    private array $api_url = [
        'get' => 'https://invoice-api.amego.tw/json/f0401',
        'invalid' => 'https://invoice-api.amego.tw/json/f0501',
    ];

    public static function instance(): RY_IFAMEGO_Invoice
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void {}

    public function get_invoice($invoice_data, $object_ID)
    {
        $general_info = $this->get_info();
        $api_info = $this->get_api_info();

        $post_args = [
            'OrderId' => $this->generate_trade_no($object_ID, $invoice_data['prefix']),
            'TrackApiCode' => $invoice_data['trackcode'],
            'BuyerIdentifier' => '0000000000',
            'BuyerName' => __('Customer', 'ry-invoice-for-amego'),
            'BuyerAddress' => $invoice_data['address'],
            'BuyerEmailAddress' => $invoice_data['email'],
            'MainRemark' => '#' . $invoice_data['no'],
            'CarrierType' => '',
            'CarrierId1' => '',
            'CarrierId2' => '',
            'NPOBAN' => '',
            'ProductItem' => [],
            'SalesAmount' => round($invoice_data['total'], 0),
            'FreeTaxSalesAmount' => 0,
            'ZeroTaxSalesAmount' => 0,
            'TaxType' => 1,
            'TaxRate' => 0.05,
            'TaxAmount' => -1,
            'TotalAmount' => round($invoice_data['total'], 0),
            'DetailAmountRound' => 1,
        ];

        switch ($invoice_data['type']) {
            case 'amego_host':
                $post_args['CarrierType'] = 'amego';
                $post_args['CarrierId1'] = $invoice_data['email'];
                $post_args['CarrierId2'] = $invoice_data['email'];
                break;
            case 'MOICA':
                $post_args['CarrierType'] = 'CQ0001';
                $post_args['CarrierId1'] = $invoice_data['moica_no'];
                $post_args['CarrierId2'] = $invoice_data['moica_no'];
                break;
            case 'phone_barcode':
                $post_args['CarrierType'] = '3J0002';
                $post_args['CarrierId1'] = $invoice_data['phone_barcode'];
                $post_args['CarrierId2'] = $invoice_data['phone_barcode'];
                break;
            case 'company':
                $post_args['SalesAmount'] = round($post_args['TotalAmount'] / 1.05, 0);
                $post_args['TaxAmount'] = $post_args['TotalAmount'] - $post_args['SalesAmount'];
                $post_args['DetailVat'] = 1;
                $post_args['BuyerIdentifier'] = $invoice_data['tax_no'];
                $post_args['BuyerName'] = $invoice_data['tax_name'];
                if (empty($post_args['BuyerName'])) {
                    $post_args['BuyerName'] = $post_args['BuyerIdentifier'];
                }
                break;
            case 'donate':
                $post_args['NPOBAN'] = $invoice_data['donate_no'];
                break;
        }

        foreach ($invoice_data['item'] as $invoice_item) {
            if ($invoice_item['qty'] == 0 && $invoice_item['total'] == 0) {
                continue;
            }
            if ($invoice_item['qty'] == 0) {
                $invoice_item['qty'] = 1;
            }

            $name = mb_strimwidth(str_replace('|', '', strip_tags($invoice_item['name'])), 0, 80, '');
            $unit = mb_strimwidth(str_replace('|', '', strip_tags($invoice_item['unit'])), 0, 6, '');
            $qty = round($invoice_item['qty'], 3);
            $unit_price = round($invoice_item['total'] / $qty, 6);
            $total = round($qty * $unit_price, 0);

            $post_args['ProductItem'][] = [
                'Description' => $name,
                'Quantity' => $qty,
                'Unit' => $unit,
                'UnitPrice' => $unit_price,
                'Amount' => $total,
                'TaxType' => 1,
            ];
        }

        $item_total = array_sum(array_column($post_args['ProductItem'], 'Amount'));
        if ($item_total !== $post_args['TotalAmount']) {
            switch ($general_info['abnormal_mode']) {
                case 'order':
                    $post_args['TotalAmount'] = $item_total;
                    if ($post_args['TaxAmount'] !== -1) {
                        $post_args['SalesAmount'] = round($post_args['TotalAmount'] / 1.05, 0);
                        $post_args['TaxAmount'] = $post_args['TotalAmount'] - $post_args['SalesAmount'];
                    }
                    break;
                case 'product':
                    $name = mb_strimwidth(str_replace('|', '', strip_tags($general_info['abnormal_product'])), 0, 80, '');
                    $unit = apply_filters('ry_invoice-item_unit_name', __('parcel', 'ry-invoice-for-amego'), $object_ID, 'abnormal');
                    $unit = mb_strimwidth(str_replace('|', '', $unit), 0, 6, '');

                    $post_args['ProductItem'][] = [
                        'Description' => $name,
                        'Quantity' => 1,
                        'Unit' => $unit,
                        'UnitPrice' => $post_args['TotalAmount'] - $item_total,
                        'Amount' => $post_args['TotalAmount'] - $item_total,
                        'TaxType' => 1,
                    ];
                    break;
            }
        }

        if ($post_args['TaxAmount'] === -1) {
            $post_args['TaxAmount'] = 0;
        }
        $post_args['MainRemark'] = apply_filters('ry_invoice-main_remark', $post_args['MainRemark'], $object_ID);
        $post_args['MainRemark'] = mb_strimwidth(strip_tags($post_args['MainRemark']), 0, 200, '');

        foreach ($post_args as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    if (is_int($sub_value) || is_float($sub_value)) {
                        $post_args[$key][$sub_key] = (string) $sub_value;
                    }
                }
            }
            if (is_int($value) || is_float($value)) {
                $post_args[$key] = (string) $value;
            }
        }

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['get'];
        } else {
            $post_url = $this->api_url['get'];
        }

        do_action('ry_invoice_amego-pre_get_invoice', $post_args, $object_ID);
        RY_Logs::log('amego-invoice', 'info', 'Get LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['invoice'], $api_info['AppKey']);
        if ($result) {
            RY_Logs::log('amego-invoice', 'info', 'Get response #' . $object_ID, $result);
            do_action('ry_invoice_amego-post_get_invoice', $post_args, $result, $object_ID);
        }
    }

    public function invalid_invoice($invoice_data, $object_ID = null)
    {
        $api_info = $this->get_api_info();

        $post_args = [
            [
                'CancelInvoiceNumber' => $invoice_data['no'],
            ],
        ];

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['invalid'];
        } else {
            $post_url = $this->api_url['invalid'];
        }

        do_action('ry_invoice_amego-pre_invalid_invoice', $post_args, $object_ID);
        RY_Logs::log('amego-invoice', 'info', 'Invalid LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['invoice'], $api_info['AppKey']);
        if ($result) {
            RY_Logs::log('amego-invoice', 'info', 'Invalid response #' . $object_ID, $result);
            do_action('ry_invoice_amego-post_invalid_invoice', $post_args, $result, $object_ID);
        }
    }

    public function get_info()
    {
        $general_info = RY_IFAMEGO::get_option('general', []);
        if (!is_array($general_info)) {
            $general_info = [];
        }

        return array_merge([
            'abnormal_mode' => '',
            'abnormal_product' => __('Discount', 'ry-invoice-for-amego'),
        ], $general_info);
    }

    public function get_api_info()
    {
        $api_info = RY_IFAMEGO::get_option('apiinfo', []);
        if (!is_array($api_info)) {
            $api_info = [];
        }
        $api_info = array_merge([
            'testmode' => 'no',
            'invoice' => '',
            'AppKey' => '',
        ], $api_info);
        $api_info['testmode'] = $api_info['testmode'] === 'yes';

        if ($api_info['testmode'] === true) {
            $api_info['invoice'] = '12345678';
            $api_info['AppKey'] = 'sHeq7t8G1wiQvhAuIM27';
        }

        return $api_info;
    }
}
