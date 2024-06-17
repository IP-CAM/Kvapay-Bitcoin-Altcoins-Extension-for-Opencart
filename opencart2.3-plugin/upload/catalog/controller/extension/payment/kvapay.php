<?php

use kvapay\Client;

require_once(DIR_SYSTEM . 'library/kvapay/kvapay-php/init.php');
require_once(DIR_SYSTEM . 'library/kvapay/version.php');

class ControllerExtensionPaymentKvapay extends Controller
{

    /** @var array */
    protected $requestData;

    public function index()
    {
        $this->load->language('extension/payment/kvapay');
        $this->load->model('checkout/order');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['action'] = $this->url->link('extension/payment/kvapay/checkout', '', true);

        return $this->load->view('extension/payment/kvapay', $data);
    }

    public function checkout()
    {
        $kvapayClient = $this->getKvapayClient();
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/kvapay');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $token = md5(uniqid(rand(), true));

        $firstName = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
        $lastName = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');

        $params = [
            "symbol" => $order_info['currency_code'],
            "amount" => (float)number_format($order_info['total'] * $this->currency->getvalue($order_info['currency_code']), 2, '.', ''),
            "currency" => $order_info['currency_code'],
            "variableSymbol" => (string)$order_info['order_id'],
            'failUrl' => $this->url->link('extension/payment/kvapay/cancel', '', true),
            'successUrl' => $this->url->link('extension/payment/kvapay/success', array('cg_token' => $token), true),
            'timestamp' => time(),
            'name' => $firstName . ' ' . $lastName,
            'email' => $order_info['email'],
        ];

        $jsonResponse = [
            'state' => 'ok',
        ];

        try {
            $cg_order = $kvapayClient->payment->createPaymentShortLink($params);

        } catch (\Exception $e) {
            $jsonResponse['state'] = 'error';
            $jsonResponse['error'] = $e->getMessage();
            $this->log->write("[Kvapay]".$e->getMessage());
        }


        if (isset($cg_order)) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('kvapay_order_status_id'));

            $jsonResponse['url'] = $cg_order->shortLink;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($jsonResponse));
    }

    public function cancel()
    {
        $this->response->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/kvapay');

        $order = $this->model_extension_payment_kvapay->getOrder($this->session->data['order_id']);

        if (empty($order) || strcmp($order['token'], $this->request->get['cg_token']) !== 0) {
            $this->response->redirect($this->url->link('common/home', '', true));
        } else {
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }
    }

    public function callback()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/kvapay');

        $kvapayClient = $this->getkvapayClient();

        $this->request = file_get_contents('php://input');
        $headers = $this->get_ds_headers();
        if (!array_key_exists("XSignature", $headers)) {
            $error_message = 'KvaPay X-SIGNATURE: not found';
            $this->log->write($error_message);
            exit;
        }

        $signature = $headers["XSignature"];

        $this->requestData = json_decode($this->request, true);
        if (false === $this->checkIfRequestIsValid()) {
            $error_message = 'KvaPay Request: not valid request data';
            $this->log->write($error_message);
            exit;
        }

        if ($this->requestData['type'] !== 'PAYMENT') {
            $error_message = 'KvaPay Request: not valid request type';
            $this->log->write($error_message);
            exit;
        }

        $token = $kvapayClient->generateSignature($this->request, $this->config->get('kvapay_api_secret'));

        if (empty($signature) || strcmp($signature, $token) !== 0) {
            $error_message = 'KvaPay X-SIGNATURE: ' . $signature . ' is not valid';
            $this->log->write($error_message);
            exit;
        }

        $order_id = (int)$this->requestData['variableSymbol'];

        $this->requestData = json_decode($this->request, true);

        if (isset($this->requestData['state'])) {
            switch ($this->requestData['state']) {
                case 'SUCCESS':
                    $cg_order_status = 'kvapay_paid_status_id';
                    break;
                case 'WAITING_FOR_PAYMENT':
                    $cg_order_status = 'kvapay_pending_status_id';
                    break;
                case 'WAITING_FOR_CONFIRMATION':
                    $cg_order_status = 'kvapay_confirming_status_id';
                    break;
                case 'EXPIRED':
                    $cg_order_status = 'kvapay_expired_status_id';
                    break;
                default:
                    $cg_order_status = NULL;
            }

            if (!is_null($cg_order_status)) {
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get($cg_order_status));
            }
        }


        $this->response->addHeader('HTTP/1.1 200 OK');
    }

    private function getKvapayClient()
    {
        Client::setAppInfo('OpenCart', KVAPAY_OPENCART_EXTENSION_VERSION);

        return new Client(
            $this->config->get('kvapay_api_key'),
            $this->config->get('kvapay_test_mode') == 1
        );
    }

    private function checkIfRequestIsValid()
    {

        return true;
    }

    private function get_ds_headers()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
