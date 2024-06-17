<?php

class ModelExtensionPaymentKvapay extends Model
{
    public function install() {
        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['kvapay_test_mode'] = 0;
        $defaults['kvapay_order_status_id'] = 1;
        $defaults['kvapay_confirming_status_id'] = 1;
        $defaults['kvapay_paid_status_id'] = 2;
        $defaults['kvapay_expired_status_id'] = 14;
        $defaults['kvapay_sort_order'] = 0;

        $this->model_setting_setting->editSetting('kvapay', $defaults);
    }
}