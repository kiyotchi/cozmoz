<?php
/*
 * INTER-Mediator Ver.3.6 Released 2013-07-05
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_AppendPrefix
{

    var $appendStr;

    function __construct($str = '')
    {
        $this->appendStr = $str;
    }

    function converterFromDBtoUser($str)
    {
        return $this->appendStr . $str;
    }

    function converterFromUserToDB($str)
    {
        if (strpos($str, $this->appendStr) === 0) {
            return substr($str, strlen($this->appendStr));
        }
        return $str;
    }
}

?>
