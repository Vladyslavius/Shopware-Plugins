<?php

namespace ImportBooks;

use ZipArchive;

class ApiClient
{
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_DELETE = 'DELETE';
    protected $validMethods = [
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE,
    ];
    protected $dateToImport;
    protected $apiUrl;
    protected $cURL;
    protected $headers = [
        'Accept: text/html',
        'Apikey: 1138b628-cdc5-4e41-9ded-ec8a80cfcfc1',
        'Userauth: 163a6498-0e69-487d-b8c6-e5e350aab0fc',
    ];
    protected $url = "https://api.booklink.de/basic/getImage/";
    protected $urlZip = "https://api.booklink.de/basic/getCatalog/";

    public function __construct()
    {
        //$config = Shopware()->Container()->get("shopware.plugin.config_reader")->getByPluginName("ImportBooks");
        $this->dateToImport = date('Ymd');
        $this->cURL = curl_init();
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, $this->headers);
    }

    public function call($imageName)
    {
        $tempUrl = $this->url . $imageName;
        curl_setopt($this->cURL, CURLOPT_URL, $tempUrl);
        $result = curl_exec($this->cURL);
        if (curl_errno($this->cURL)) {
            echo 'Error:' . curl_error($this->cURL);
        }
        curl_close($this->cURL);
        $test = fopen($imageName, "w");
        fwrite($test, $result);
        fclose($test);
    }

    public function callZip()
    {
        $output_filename = "test.zip";
        $host = 'https://api.booklink.de/basic/getCatalog/163a6498-0e69-487d-b8c6-e5e350aab0fc_f_' . $this->dateToImport . '.zip';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Userauth: 163a6498-0e69-487d-b8c6-e5e350aab0fc',
            'Apikey: 1138b628-cdc5-4e41-9ded-ec8a80cfcfc1'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $fp = fopen($output_filename, 'w');
        fwrite($fp, $result);
        fclose($fp);
        $zip = new ZipArchive;
        $res = $zip->open('test.zip');
        if ($res === true) {
            $zip->extractTo(__DIR__);
            $zip->close();
        }
        unlink("test.zip");
    }
}
