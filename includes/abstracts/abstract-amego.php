<?php

abstract class RY_IFAMEGO_Abstract_Invoice
{
    protected function generate_trade_no($object_ID, $order_prefix = '')
    {
        $trade_no = $order_prefix . $object_ID . 'TS' . random_int(0, 9) . strrev((string) time());
        $trade_no = apply_filters('ry_invoice_amego-trade_no', $trade_no);

        return substr($trade_no, 0, 18);
    }

    protected function link_server(string $url, array $args, string $invoice, string $AppKey, int $timeout = 30)
    {
        wc_set_time_limit(40);

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
        $post_data = [
            'invoice' => $invoice,
            'data' => wp_json_encode($args, JSON_UNESCAPED_UNICODE),
            'time' => $now->getTimestamp(),
        ];
        $post_data['sign'] = hash('md5', $post_data['data'] . $post_data['time'] . $AppKey);
        $response = wp_remote_post($url, [
            'timeout' => $timeout,
            'body' => $post_data,
            'user-agent' => apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version')),
        ]);

        if (is_wp_error($response)) {
            RY_Logs::log('amego-invoice', 'error', 'Link failed', $response->get_error_messages());
            return;
        }

        if (wp_remote_retrieve_response_code($response) != 200) {
            RY_Logs::log('amego-invoice', 'error', 'Link HTTP status error', ['status' => wp_remote_retrieve_response_code($response)]);
            return;
        }

        $result = @json_decode(wp_remote_retrieve_body($response));

        if (!is_object($result)) {
            RY_Logs::log('amego-invoice', 'error', 'Link response parse failed', ['response' => wp_remote_retrieve_body($response)]);
            return;
        }

        return $result;
    }
}
