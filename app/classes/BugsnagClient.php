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
 * BugsnagClient.php
 *
 * The client for handling errors with Bugsnag.
 * 
 */

namespace CryptoStatus;

class BugsnagClient {

  /**
   * Constructor, initialization of Bugsnag's error handler
   */
  public function __construct() {
    $bugsnag = \Bugsnag\Client::make($this->getApiKey());
    \Bugsnag\Handler::register($bugsnag);
  }

  /**
   * Get API key from environment variables
   * 
   * @return string
   * @throws BugsnagClientException if Bugsnag API key could not be retrieved
   */
  protected function getApiKey() : string {
    $api_key = getenv(BUGSNAG_API_KEY);
    
    if (empty($api_key)) {
      throw new BugsnagClientException("Could not get Bugsnag API Key", 1);
    }

    return $api_key;
  }

}
