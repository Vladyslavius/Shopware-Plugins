<?php

namespace itDelightCommunicationApplication\Helpers;

class JsonData
{
    public function returnJsonData($data)
    {
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    public function returnError()
    {

        $data = array(
            'status' => '404'
        );

        echo json_encode($data, JSON_UNESCAPED_UNICODE);

    }
}