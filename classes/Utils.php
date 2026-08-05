<?php

namespace RY\Invoice\Amego;

defined('ABSPATH') or exit;

final class Utils
{
    public static function track_term_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                '0' => _x('Jan - Feb', 'track term', 'ry-invoice-for-amego'),
                '1' => _x('Mar - Apr', 'track term', 'ry-invoice-for-amego'),
                '2' => _x('May - Jun', 'track term', 'ry-invoice-for-amego'),
                '3' => _x('Jul - Aug', 'track term', 'ry-invoice-for-amego'),
                '4' => _x('Sep - Oct', 'track term', 'ry-invoice-for-amego'),
                '5' => _x('Nov - Dec', 'track term', 'ry-invoice-for-amego'),
            ];
        }

        return $list[$value] ?? $value;
    }

    public static function track_status_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                '1' => _x('Use', 'track status', 'ry-invoice-for-amego'),
                '2' => _x('Disable', 'track status', 'ry-invoice-for-amego'),
                '3' => _x('Expired', 'track status', 'ry-invoice-for-amego'),
                '9' => _x('Used', 'track status', 'ry-invoice-for-amego'),
            ];
        }

        return $list[$value] ?? $value;
    }
}
