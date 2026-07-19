<?php defined('ABSPATH') or exit; ?>

<?php
use RY\General\Logs;

?>

<?php $api_info = RY_IFAMEGO_Invoice::instance()->get_api_info(); ?>

<h2 class="title"><?php esc_html_e('API credentials', 'ry-invoice-for-amego'); ?></h2>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row"><?php esc_html_e('Debug log', 'ry-invoice-for-amego'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text"><span><?php esc_html_e('Debug log', 'ry-invoice-for-amego'); ?></span></legend>
                <label for="log"><input name="log" type="checkbox" id="log" value="yes" <?php checked(RY_IFAMEGO::get_option('log', 'no') === 'yes'); ?>>
                    <?php esc_html_e('Enable log', 'ry-invoice-for-amego'); ?></label>
                <p class="description">
                    <?php echo wp_kses(
                        __('<strong>Note:</strong> The log may contain personal information.', 'ry-invoice-for-amego'),
                        ['strong' => []]
                    ); ?>
                </p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Sandbox', 'ry-invoice-for-amego'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text"><span><?php esc_html_e('Sandbox', 'ry-invoice-for-amego'); ?></span></legend>
                <label for="testmode"><input name="testmode" type="checkbox" id="testmode" value="yes" <?php checked($api_info['testmode']); ?>>
                    <?php esc_html_e('Enable sandbox', 'ry-invoice-for-amego'); ?></label>
                <p class="description"><?php esc_html_e('Note: For developers use ONLY.', 'ry-invoice-for-amego'); ?></p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="invoice"><?php esc_html_e('Tax ID number', 'ry-invoice-for-amego'); ?></label></th>
        <td><input name="invoice" type="text" id="invoice" value="<?php echo esc_attr($api_info['invoice']); ?>" class="regular-text"></td>
    </tr>
    <tr>
        <th scope="row"><label for="AppKey"><?php esc_html_e('App Key', 'ry-invoice-for-amego'); ?></label></th>
        <td><input name="AppKey" type="text" id="AppKey" value="<?php echo esc_attr($api_info['AppKey']); ?>" class="regular-text"></td>
    </tr>
</table>
