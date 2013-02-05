<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API CURL connection.
 *
 * @author knplabs.com
 */
class Jirafe_HttpConnection_Curl extends Jirafe_HttpConnection_Connection
{
    private $ssl; 
    private $base;
    private $timeout;
    private $useragent;

    /**
     * Initializes CURL connection.
     *
     * @param   string  $base       api url
     * @param   integer $timeout    connection timeout
     * @param   string  $useragent  client user-agent
     */
    public function __construct($base, $timeout = 10, $useragent = 'jirafe-php-client')
    {
        $this->ssl       = strpos($base, 'https://') === 0;
        $this->base      = rtrim($base, '/') . '/';
        $this->timeout   = intval($timeout);
        $this->useragent = $useragent;
    }

    /**
     * Make HTTP request.
     *
     * @param   string  $method     HTTP method name
     * @param   string  $path       path to request
     * @param   array   $query      query parameters
     * @param   array   $parameters post parameters
     */
    protected function makeRequest($method, $path, array $query = array(), array $parameters = array())
    {
        $curlOpts = array();

        $queryString      = http_build_query($query);
        $parametersString = http_build_query($parameters);

        if (!empty($query)) {
            $path .= (false === strpos($path, '?') ? '?' : '&') . $queryString;
        }

        if (!empty($parameters)) {
            $curlOpts += array(
                CURLOPT_POSTFIELDS      => $parametersString,
                //CURLOPT_POSTFIELDSIZE   => strlen($parametersString)
            );
        }

        $curlOpts += array(
            CURLOPT_URL             => $this->base . ltrim($path, '/'),
            CURLOPT_USERAGENT       => $this->useragent,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => $this->timeout,
            CURLOPT_HEADER          => 0
        );
        
        if ($this->ssl) {
            // Add custom certificates
            $curlOpts += array(
                CURLOPT_CAINFO      => dirname(__FILE__).'/etc/curl-ca-bundle.crt'
            );
        }

        $response = null;

        switch ($method) {
            case 'GET':
                $response = $this->makeCurlRequest($curlOpts);
                break;
            case 'HEAD':
                $curlOpts += array(CURLOPT_NOBODY => true);
                $response = $this->makeCurlRequest($curlOpts);
                break;
            case 'POST':
                $curlOpts += array(CURLOPT_POST => true);
                $response = $this->makeCurlRequest($curlOpts);
                break;
            case 'PUT':
                fwrite($putData = tmpfile(), $parametersString);
                fseek($putData, 0);
                $curlOpts += array(
                    CURLOPT_PUT         => true,
                    CURLOPT_INFILE      => $putData,
                    CURLOPT_INFILESIZE  => strlen($parametersString)
                );
                $response = $this->makeCurlRequest($curlOpts);
                fclose($putData);
                break;
            case 'DELETE':
                $curlOpts += array(CURLOPT_CUSTOMREQUEST => 'DELETE');
                $response = $this->makeCurlRequest($curlOpts);
                break;
        }

        return $response;
    }

    /**
     * Makes CURL request with specified options.
     *
     * @param   array   $options    curl options
     *
     * @return  array
     */
    private function makeCurlRequest(array $options)
    {
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $body         = curl_exec($curl);
        $headers      = curl_getinfo($curl);
        $errorCode    = curl_errno($curl);
        $errorMessage = curl_error($curl);

        if (0 === $errorCode && 200 !== $headers['http_code']) {
            $errorCode    = $headers['http_code'];
            $errorMessage = 'HTTP error';
        }
        
        $response = $this->initializeResponse($body, $headers, $errorCode, $errorMessage);

        curl_close($curl);
        return $response;
    }
}
