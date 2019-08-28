<?php

namespace itDelightCommunicationApplication\Helpers;

class CurlData
{

    public function postDvsn($params)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://teleropa.de/widgets/DvsnArticleSubscription/save");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch, CURLOPT_POSTFIELDS, http_build_query(
                $params
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close ($ch);
        return $output;

    }

}