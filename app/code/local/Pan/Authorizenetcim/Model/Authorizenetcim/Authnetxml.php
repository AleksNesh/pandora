<?php

class Pan_Authorizenetcim_Model_Authorizenetcim_Authnetxml extends TinyBrick_Authorizenetcim_Model_Authorizenetcim_Authnetxml
{
    /**
     * __toString()
     *
     * AAI OVERRIDE TO ADD html_entity_decode FOR MORE READABLE OUTPUT
     *
     * @return string
     */
    public function __toString()
    {
        $output = '';
        $output .= '<table summary="Authorize.Net Results" id="authnet">' . "\n";
        $output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Class Parameters</b></th>' . "\n" . '</tr>' . "\n";
        $output .= '<tr>' . "\n\t\t" . '<td><b>API Login ID</b></td><td>' . $this->login . '</td>' . "\n" . '</tr>' . "\n";
        $output .= '<tr>' . "\n\t\t" . '<td><b>Transaction Key</b></td><td>' . $this->transkey . '</td>' . "\n" . '</tr>' . "\n";
        $output .= '<tr>' . "\n\t\t" . '<td><b>Authnet Server URL</b></td><td>' . $this->url . '</td>' . "\n" . '</tr>' . "\n";
        $output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Request XML</b></th>' . "\n" . '</tr>' . "\n";
        if (isset($this->xml) && !empty($this->xml))
        {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML(self::removeResponseXMLNS($this->xml));
            $outgoing_xml = $dom->saveXML();

            $output .= '<tr><td>' . "\n";
            $output .= 'XML:</td><td><pre>' . "\n";
            $output .= html_entity_decode(htmlentities($outgoing_xml)) . "\n";
            $output .= '</pre></td></tr>' . "\n";
        }
        if (!empty($this->response))
        {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML(self::removeResponseXMLNS($this->response));
            $response_xml = $dom->saveXML();

            $output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Response XML</b></th>' . "\n" . '</tr>' . "\n";
            $output .= '<tr><td>' . "\n";
            $output .= 'XML:</td><td><pre>' . "\n";
            $output .= html_entity_decode(htmlentities($response_xml)) . "\n";
            $output .= '</pre></td></tr>' . "\n";
        }
        $output .= '</table>';

        return $output;
    }
}
