<?php

/**
 * A simple Twitter bot application which posts hourly status updates for the top 10 cryptocurrencies.
 *
 * PHP version >= 7.0
 *
 * LICENSE: MIT, see LICENSE file for more information
 *
 * @author JR Cologne <kontakt@jr-cologne.de>
 * @copyright 2018 JR Cologne
 * @license https://github.com/jr-cologne/CryptoStatus/blob/master/LICENSE MIT
 * @version v0.3.0
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * CurlClient.php
 *
 * The cURL client for making simple requests to an API.
 * 
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\CurlClientException;

class CurlClient {

  /**
   * The cURL handle of the current cURL session
   * 
   * @var resource $ch
   */
  protected $ch;

  /**
   * The result of a performed cURL request
   * 
   * @var mixed $result
   */
  protected $result;

  /**
   * Constructor, initialize cURL
   */
  public function __construct() {
    $this->ch = curl_init();
  }

  /**
   * Perform a cURL request and retrieve the result
   * 
   * @param string $url The URL for the cURL request
   * @return self
   * @throws CurlClientException if cURL request failed
   */
  public function get(string $url) : self {
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_URL, $this->sanitizeUrl($url));

    $this->result = curl_exec($this->ch);
    curl_close($this->ch);

    if ($this->result === false) {
      throw new CurlClientException('cURL request failed', 1);
    }

    return $this;
  }

  /**
   * Json-decode result/return data of cURL request
   * 
   * @param bool $array = true Return json-decoded data as array
   * @return array (default) or object
   * @throws CurlClientException if cURL return data could not be json-decoded
   */
  public function json(bool $array = true) {
    $json = json_decode($this->result, $array);

    if ($json === null) {
      throw new CurlClientException('cURL return data could not be json-decoded', 3);
    }

    return $json;
  }

  /**
   * Sanitize and validate URL for cURL request
   * 
   * @param string $url The URL for the cURL request
   * @return string
   * @throws CurlClientException if an invalid URL is given
   */
  protected function sanitizeUrl(string $url) : string {
    $url = filter_var($url, FILTER_SANITIZE_URL);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      throw new CurlClientException('Invalid URL', 2);
    }

    return $url;
  }
  
}
