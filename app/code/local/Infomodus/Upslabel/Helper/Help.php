<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.02.12
 * Time: 16:07
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Helper_Help extends Mage_Core_Helper_Abstract
{
    public $error = true;
    private $ch;

    static public function escapeXML($string)
    {
        $string = preg_replace('/&/is', '&amp;', $string);
        $string = preg_replace('/</is', '&lt;', $string);
        $string = preg_replace('/>/is', '&gt;', $string);
        $string = preg_replace('/\'/is', '&#39;', $string);
        $string = preg_replace('/"/is', '&quot;', $string);
        $string = str_replace(array('ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ü', 'ò', 'è', 'à', 'ì'), array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z', 'u', 'o', 'e', 'a', 'i'), $string);
        return mb_encode_numericentity(trim($string), array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
    }

    public function curlSend($url, $data = NULL)
    {
        $this->error = true;
        $result = $this->curlSetOption($url, $data);
        $ch = $this->ch;
        if ($result) {
            $result1 = $result;
            $result = strstr($result, '<?xml');
            if ($result === FALSE) {
                $result = $result1;
            }
            curl_close($ch);
            $this->error = false;
            return $result;
        } else {
            $error = '<h1>Error</h1> <ul>';
            $error .= '<li>Error Severity : Hard</li>';
            $error .= '<li>Error Description : ' . curl_errno($ch) . ' - ' . curl_error($ch) . '</li>';
            $error .= '</ul>';
            $error .= '<textarea>' . curl_errno($ch) . ' - ' . curl_error($ch) . '</textarea>';
            $error .= '<textarea>' . $data . '</textarea>';
            curl_close($ch);
            $this->error = true;
            return array('errordesc' => 'Server Error (cUrl)', 'error' => $error);
        }
    }

    public function curlSetOption($url, $data = NULL)
    {
        $sslV = curl_version();
        $ch = curl_init($url);
        if ($data != NULL) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (strpos($sslV['ssl_version'], 'NSS/') === FALSE) {
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        }
        if ($data !== NULL) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $this->ch = $ch;
        return curl_exec($ch);
    }

    public function sendPrint($data, $storeId = NULL)
    {
        $ip = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_ip'));
        $port = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_port'));
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            Mage::log("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error());
        } else {
            $result = socket_connect($socket, $ip, $port);
            if ($result === false) {
                Mage::log("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)));
                echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
            } else {
                socket_write($socket, $data, strlen($data));
            }
            socket_close($socket);
        }
    }

    
}
