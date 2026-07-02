<?php defined('ABSPATH') or exit; ?>

<?php $api_info = RY_IFAMEGO_Invoice::instance()->get_info(); ?>

<h2 class="title"><?php esc_html_e('General options', 'ry-invoice-for-amego'); ?></h2>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row"><label for="abnormal_mode"><?php esc_html_e('Amount abnormal mode', 'ry-invoice-for-amego'); ?></label></th>
        <td>
            <select name="abnormal_mode" id="abnormal_mode">
                <option value=""><?php esc_html_e('No action', 'ry-invoice-for-amego'); ?></option>
                <option value="product"><?php esc_html_e('Add one product to match order amount', 'ry-invoice-for-amego'); ?></option>
                <option value="order"><?php esc_html_e('Change order total amount', 'ry-invoice-for-amego'); ?></option>
            </select>
        </td>
    </tr>
     <tr>
        <th scope="row"><label for="abnormal_product"><?php esc_html_e('Fix amount product name', 'ry-invoice-for-amego'); ?></label></th>
        <td><input name="abnormal_product" type="text" id="abnormal_product" value="<?php echo esc_attr($api_info['abnormal_product']); ?>" class="regular-text"></td>
    </tr>
</table>
