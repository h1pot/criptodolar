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
 * TwitterClient.php
 *
 * The client for interacting with the Twitter API.
 * 
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\TwitterClientException;

use Codebird\Codebird;

class TwitterClient {

  /**
   * A Codebird instance (a Twitter client library for PHP)
   * 
   * @var Codebird $client
   */
  protected $client;

  /**
   * The Twitter API keys
   * 
   * @var array $api_keys
   */
  protected $api_keys;

  /**
   * Constructor, initialization and authentication with Twitter API
   * 
   * @param Codebird $twitter_client A Cordbird instance
   * @throws TwitterClientException if authentication with Twitter API failed
   */
  public function __construct(Codebird $twitter_client) {
    $this->client = $twitter_client;

    $this->api_keys = $this->getApiKeys();

    if (!$this->authenticate()) {
      throw new TwitterClientException("Authentication with Twitter API failed", 2);
    }
  }

  /**
   * Post a Tweet
   * 
   * @param  array $params Parameters for Twitter API method statuses/update
   * @param  array $return Data to return from Twitter API reply
   * @return boolean (default) or array (when $return is specified)
   */
  public function postTweet(array $params, array $return = []) {
    $reply = $this->client->statuses_update($params);
    
    if ($reply->httpstatus == 200) {
      if (!empty($return)) {
        foreach ($return as $value) {
          $return_data[$value] = $reply->{$value};
        }

        return $return_data;
      } else {
        return true;
      }
    }

    return false;
  }

  /**
   * Delete a Tweet
   * 
   * @param string $id ID of the Tweet to delete
   * @return bool
   */
  public function deleteTweet(string $id) : bool {
    $reply = $this->client->statuses_destroy_ID([ 'id' => $id ]);

    if ($reply->httpstatus == 200) {
      return true;
    }

    return false;
  }

  /**
   * Get API keys from environment variables
   * 
   * @return array
   * @throws TwitterClientException if Twitter API keys could not be retrieved
   */
  protected function getApiKeys() : array {
    $api_keys = [
      'consumer_key' => getenv(TWITTER_API_CONSUMER_KEY),
      'consumer_secret' => getenv(TWITTER_API_CONSUMER_SECRET),
      'access_token' => getenv(TWITTER_API_ACCESS_TOKEN),
      'access_token_secret' => getenv(TWITTER_API_ACCESS_TOKEN_SECRET)
    ];
    
    if ( empty($api_keys['consumer_key']) || empty($api_keys['consumer_secret']) || empty($api_keys['access_token']) || empty($api_keys['access_token_secret']) ) {
      throw new TwitterClientException("Could not get Twitter API Keys", 1);
    }

    return $api_keys;
  }

  /**
   * Authenticate with Twitter API
   * 
   * @return bool
   */
  protected function authenticate() : bool {
    $this->client::setConsumerKey($this->api_keys['consumer_key'], $this->api_keys['consumer_secret']);

    $this->client->setToken($this->api_keys['access_token'], $this->api_keys['access_token_secret']);

    return true;
  }
  
}
