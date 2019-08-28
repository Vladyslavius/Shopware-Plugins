<?php

namespace nfxActiveCampaignSchnittstelle\ApiAuthentication;

class ApiAuthentication
{
    private $apiToken;
    private $apiUrl;
    private $channel;
    private $type;

    public function __construct()
    {

        $config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName('nfxActiveCampaignSchnittstelle');

        $this->apiToken = $config['apiToken'];
        $this->apiUrl = $config['apiUrl'];

    }

    public function call()
    {

    }

    public function post($params, $type)
    {

        $this->channel = curl_init();
        curl_setopt($this->channel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->channel, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = 'Api-Token: ' . $this->apiToken;
        curl_setopt($this->channel, CURLOPT_HTTPHEADER, $headers);
        $params = json_encode($params, true);
        curl_setopt($this->channel, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->channel, CURLOPT_URL, $this->apiUrl . '' . $type);
        $result = curl_exec($this->channel);
        if (curl_errno($this->channel)) {
            error_log(print_r('CURL ERROR', true));
            error_log(print_r(curl_error($this->channel), true));
        }
        error_log(print_r($result, true));
        curl_close($this->channel);

        return $result;

    }

    public function get($params, $type)
    {

        $this->channel = curl_init();

        curl_setopt($this->channel, CURLOPT_URL, $this->apiUrl . '' . $type);
        curl_setopt($this->channel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->channel, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Api-Token: ' . $this->apiToken;
        curl_setopt($this->channel, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($this->channel);
        if (curl_errno($this->channel)) {
            echo 'Error:' . curl_error($this->channel);
        }
        curl_close($this->channel);

        return $result;

    }

    public function put($params, $type)
    {

        $this->channel = curl_init();

        curl_setopt($this->channel, CURLOPT_URL, $this->apiUrl . '' . $type);
        curl_setopt($this->channel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->channel, CURLOPT_CUSTOMREQUEST, 'PUT');
        $params = json_encode($params, true);
        curl_setopt($this->channel, CURLOPT_POSTFIELDS, $params);

        $headers = array();
        $headers[] = 'Api-Token: ' . $this->apiToken;
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($params);
        curl_setopt($this->channel, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($this->channel);
        if (curl_errno($this->channel)) {
            echo 'Error:' . curl_error($this->channel);
        }
        curl_close($this->channel);

        return $result;

    }

    public function delete($params, $type)
    {

        $this->channel = curl_init();

        curl_setopt($this->channel, CURLOPT_URL, $this->apiUrl . '' . $type);
        curl_setopt($this->channel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->channel, CURLOPT_CUSTOMREQUEST, 'DELETE');


        $headers = array();
        $headers[] = 'Api-Token: ' . $this->apiToken;
        curl_setopt($this->channel, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($this->channel);
        if (curl_errno($this->channel)) {
            echo 'Error:' . curl_error($this->channel);
        }
        curl_close($this->channel);

        return $result;

    }

}