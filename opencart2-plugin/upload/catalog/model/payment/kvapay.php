<?php

class ModelPaymentKvapay extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('payment/kvapay');


        $method_data = array(
            'code' => 'kvapay',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('kvapay_sort_order')
        );

        return $method_data;
    }
}