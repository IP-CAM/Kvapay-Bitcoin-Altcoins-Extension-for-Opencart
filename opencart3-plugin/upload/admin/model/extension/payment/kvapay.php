<?php

class ModelExtensionPaymentKvapay extends Model {
    public function install() {
        $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kvapay_order` (
        `kvapay_order_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `cg_invoice_id` VARCHAR(120),
        `token` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`kvapay_order_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
    ");

        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['payment_kvapay_test_mode'] = 0;
        $defaults['payment_kvapay_order_status_id'] = 1;
        $defaults['payment_kvapay_confirming_status_id'] = 1;
        $defaults['payment_kvapay_paid_status_id'] = 2;
        $defaults['payment_kvapay_expired_status_id'] = 14;
        $defaults['payment_kvapay_sort_order'] = 0;

        $this->model_setting_setting->editSetting('payment_kvapay', $defaults);
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "kvapay_order`;");
    }
}
