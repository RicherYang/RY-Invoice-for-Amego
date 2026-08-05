<?php

namespace RY\Invoice\Amego\Admin\ListTable;

defined('ABSPATH') or exit;

use RY\Invoice\Amego\LinkProvider;
use RY\Invoice\Amego\Utils;
use RY\Invoice\V20260805\ListTable\Track as BaseTrack;

final class Track extends BaseTrack
{
    public function prepare_items()
    {
        $time = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $get_list[] = [$time->format('Y'), ceil($time->format('n') / 2) - 1];
        $time->add(new \DateInterval('P2M'));
        $get_list[] = [$time->format('Y'), ceil($time->format('n') / 2) - 1];

        foreach ($get_list as $get) {
            $result = LinkProvider::instance()->track_status($get[0], $get[1]);
            if ($result->code == '0') {
                foreach ($result->data as $status) {
                    $this->items[] = [
                        'year' => $get[0],
                        'term' => $get[1],
                        'code' => $status->data[0]->data[0]->code,
                        'start_no' => $status->data[0]->data[0]->start,
                        'end_no' => $status->data[0]->data[0]->end,
                        'now_no' => $status->data[0]->data[0]->now,
                        'trackcode' => $status->data[0]->data[0]->TrackApiCode,
                        'status' => $status->data[0]->data[0]->status,
                    ];
                }
            }
        }
    }

    protected function column_term($item)
    {
        return Utils::track_term_to_name($item['term']);
    }

    protected function column_status($item)
    {
        $info = '';
        if ($item['status'] == '1') {
            $info = '<span class="dashicons dashicons-saved"></span>';
        }
        return $info . Utils::track_status_to_name($item['status']);
    }
}
