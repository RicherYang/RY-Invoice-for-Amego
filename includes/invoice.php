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
            'BuyerAddress' => __('Taiwan', 'ry-invoice-for-amego'),
            'BuyerEmailAddress' => $invoice_data['email'],
            'MainRemark' => '#' . $invoice_data['no'],
            'CarrierType' => '',
            'CarrierId1' => '',
            'CarrierId2' => '',
            'NPOBAN' => '',
            'ProductItem' => [],
            'SalesAmount' => 0,
            'FreeTaxSalesAmount' => 0,
            'ZeroTaxSalesAmount' => 0,
            'TaxType' => 1,
            'TaxRate' => 0.05,
            'TaxAmount' => 0,
            'TotalAmount' => round($invoice_data['total'], 0),
            'DetailVat' => 1,
            'DetailAmountRound' => 0,
        ];

        switch ($invoice_data['type']) {
            case 'host':
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
                $post_args['DetailVat'] = 0;
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
            $qty = round($invoice_item['qty'], $general_info['count_precision']);
            $total = $invoice_item['total'];
            if ($post_args['DetailVat'] === 0) {
                $total = round($total / 1.05, 0);
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = round($unit_price * $qty, $general_info['count_precision']);
            } else {
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = round($unit_price * $qty, $general_info['count_precision']);
            }

            match($invoice_item['tax']) {
                1 => $post_args['SalesAmount'] += $total,
            };
            $post_args['ProductItem'][] = [
                'Description' => $name,
                'Quantity' => $qty,
                'Unit' => $unit,
                'UnitPrice' => $unit_price,
                'Amount' => $total,
                'TaxType' => $invoice_item['tax'],
            ];
        }

        $post_args['SalesAmount'] = round($post_args['SalesAmount'], 0);
        $amount = round($post_args['SalesAmount'] + $post_args['FreeTaxSalesAmount'] + $post_args['ZeroTaxSalesAmount'], 0);
        $post_args['TaxAmount'] = $post_args['TotalAmount'] - $amount;

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

        $general_info = array_merge([
            'count_precision' => 3,
            'amount_precision' => 7,
        ], $general_info);
        $general_info['count_precision'] = (int) $general_info['count_precision'];
        $general_info['amount_precision'] = (int) $general_info['amount_precision'];

        return $general_info;
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
