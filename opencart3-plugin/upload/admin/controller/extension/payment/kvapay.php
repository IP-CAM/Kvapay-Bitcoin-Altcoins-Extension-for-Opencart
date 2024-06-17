<?php

use KvaPay\Client;

require_once(DIR_SYSTEM . 'library/kvapay/kvapay-php/init.php');

class ControllerExtensionPaymentkvapay extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/kvapay');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/geo_zone');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_kvapay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['action'] = $this->url->link('extension/payment/kvapay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/kvapay', 'user_token=' . $this->session->data['user_token'], true)
        );

        $fields = $this->getFields();

        foreach ($fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }

        $data['payment_kvapay_sort_order'] = isset($this->request->post['payment_kvapay_sort_order']) ?
            $this->request->post['payment_kvapay_sort_order'] : $this->config->get('payment_kvapay_sort_order');


        $data['payment_kvapay_api_key'] = isset($this->request->post['payment_kvapay_api_key']) ?
            $this->request->post['payment_kvapay_api_key'] : $this->config->get('payment_kvapay_api_key');

        $data['payment_kvapay_api_secret'] = isset($this->request->post['payment_kvapay_api_secret']) ?
            $this->request->post['payment_kvapay_api_secret'] : $this->config->get('payment_kvapay_api_secret');

        $data['callback_url'] = $this->getUrl('extension/payment/kvapay/callback');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/kvapay', $data));
    }

    private function getFields()
    {
        return [
            'payment_kvapay_status',
            'payment_kvapay_api_key',
            'payment_kvapay_api_secret',
            'payment_kvapay_order_status_id',
            'payment_kvapay_confirming_status_id',
            'payment_kvapay_paid_status_id',
            'payment_kvapay_expired_status_id',
            'payment_kvapay_total',
            'payment_kvapay_geo_zone_id',
            'payment_kvapay_test_mode',
        ];
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/kvapay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!class_exists('KvaPay\Client')) {
            $this->error['warning'] = $this->language->get('error_composer');
        }

        return !$this->error;
    }


    public function install()
    {
        $this->load->model('extension/payment/kvapay');

        $this->model_extension_payment_kvapay->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/kvapay');

        $this->model_extension_payment_kvapay->uninstall();
    }

    private function getUrl($route)
    {
        $protocol = ($_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];

        return "$protocol://$host/index.php?route=$route";
    }
}
