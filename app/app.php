<?php

/**
 * A simple Twitter bot application which posts hourly status updates for the top 10 cryptocurrencies.
 *
 * PHP version >= 7.0 Alfonso Davila
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
 * app.php
 *
 * The main application file
 * 
 */

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('America/Caracas');

$hf = date('H');

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
	$dotenv->overload();
//$dotenv->load();	

use CryptoStatus\BugsnagClient;
use CryptoStatus\CryptoStatus;

//$hf2 = $hf / 2 ;
//if ($hf >= 7 && $hf <= 21 && date('N') < 7 ) {
if ($hf >= 7 && $hf <= 22) {
// Include the LocalBitcoins API
	include('_api_localbitcoins.php');

// initialize error handling
	$bugsnag_client = new BugsnagClient;

	$app = new CryptoStatus;

// initialize app
	$app->init();

// run app
	$app->run();
}
