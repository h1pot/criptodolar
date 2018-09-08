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
 * MailClient.php
 *
 * The Mail client for sending simple emails with a SMTP server.
 * 
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\MailClientException;

use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;

class MailClient {

  /**
   * The Swift_Mailer instance (a mailing library for PHP)
   * 
   * @var Swift_Mailer $client
   */
  protected $client;

  /**
   * The created Swift_Message instance
   * 
   * @var Swift_Message $message
   */
  protected $message;

  /**
   * Constructor, initialize mail client, authenticate with SMTP server
   * 
   * @param array $config SMTP server config including 'smtp_server', 'smtp_port',
   *                      'smtp_encryption', 'smtp_username', and 'smtp_password'
   * @throws MailClientException if some configs are missing/invalid
   */
  public function __construct(array $config) {
    $smtp_server = $config['smtp_server'] ?? null;
    $smtp_port = $config['smtp_port'] ?? null;
    $smtp_encryption = $config['smtp_encryption'] ?? null;
    $smtp_username = $config['smtp_username'] ?? null;
    $smtp_password = $config['smtp_password'] ?? null;

    if ( !$smtp_server || !$smtp_port || !$smtp_encryption || !$smtp_username || !$smtp_password ) {
      throw new MailClientException('Invalid/Missing mail configs');
    }

    $transport = (new Swift_SmtpTransport($smtp_server, $smtp_port))
      ->setEncryption($smtp_encryption)
      ->setUsername($smtp_username)
      ->setPassword($smtp_password);

    $this->client = new Swift_Mailer($transport);
  }

  /**
   * Set message settings
   * 
   * @param  array $settings The message settings including 'from', 'to', 'subject', and 'body'
   * @return self
   * @throws MailClientException if message settings are invalid/missing
   */
  public function message(array $settings) : self {
    $from = $settings['from'] ?? null;
    $to = $settings['to'] ?? null;
    $subject = $settings['subject'] ?? null;
    $body = $settings['body'] ?? null;

    if ( !$from || !$to || !$subject || !$body ) {
      throw new MailClientException('Invalid/Missing message settings');
    }

    $this->message = (new Swift_Message($subject))
      ->setFrom($from)
      ->setTo($to)
      ->setBody($body);

    return $this;
  }

  /**
   * Send mail
   * 
   * @return bool
   */
  public function send() : bool {
    if ( $this->message && $this->client->send($this->message) === 1 ) {
      return true;
    }

    return false;
  }
  
}
