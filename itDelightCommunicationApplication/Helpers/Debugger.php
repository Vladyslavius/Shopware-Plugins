<?php

namespace itDelightCommunicationApplication\Helpers;

class Debugger
{
    public function insertLog($data, $action)
    {

        error_log(print_r('==============================================================', true));
        error_log(print_r('Action - '.$action, true));
        error_log(print_r('time - '.date('H:i:s Y/m/d').'', true));
        error_log(print_r($data, true));
        error_log(print_r('==============================================================', true));

    }

    public function pr($data) {

        echo '<pre>';
        print_r($data);
        echo '</pre>';

    }

}