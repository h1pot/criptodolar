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
 * CryptoStatus.php
 *
 * The main class of the application.
 * 
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\CryptoStatusException;

use CryptoStatus\TwitterClient;
use CryptoStatus\CurlClient;
use CryptoStatus\CryptoClient;
use CryptoStatus\MailClient;

use Codebird\Codebird;

class CryptoStatus {

  /**
   * The Twitter client instance
   * 
   * @var TwitterClient $twitter_client
   */
  protected $twitter_client;

  /**
   * The Crypto client instance
   * 
   * @var CryptoClient $crypto_client
   */
  protected $crypto_client;

  /**
   * The Mail client instance
   *
   * @var MailClient $mail_client
   */
  protected $mail_client;

  /**
   * The Crypto data
   * 
   * @var array $dataset
   */
  protected $dataset;

  /**
   * The IDs of the Tweets which need to be deleted because of an error
   * 
   * @var array $failed_tweets
   */
  protected $failed_tweets = [];

  /**
   * Initialize application
   */
  public function init() {
    $this->twitter_client = new TwitterClient(Codebird::getInstance());

    $this->crypto_client = new CryptoClient(new CurlClient, [
      'api' => CRYPTO_API,
      'endpoint' => CRYPTO_API_ENDPOINT,
      'params' => [
        'limit' => CRYPTO_API_LIMIT
      ]
    ]);

    $this->mail_client = new MailClient([
      'smtp_server' => getenv('SMTP_SERVER'),
      'smtp_port' => getenv('SMTP_PORT'),
      'smtp_encryption' => getenv('SMTP_ENCRYPTION'),
      'smtp_username' => getenv('SMTP_USERNAME'),
      'smtp_password' => getenv('SMTP_PASSWORD')
    ]);
  }

  /**
   * Run the application
   */
  public function run() {
    $this->dataset = $this->getDataset();

    $this->formatData();

    $tweets = $this->createTweets();

// AD
//    var_dump($tweets);
//echo $tweets[0];

    if (!$this->postTweets($tweets)) {
      $this->deleteTweets($this->failed_tweets);
      $this->sendNotificationMail();
    }

    $this->postInsta();
    //if (!$this->postInsta()) {
      //$this->sendNotificationMail();
   // }

  }

  /**
   * Get the Crypto data
   * 
   * @return array
   */
  protected function getDataset() : array {
    return $this->crypto_client->getData();
  }

  /**
   * Format the Crypto data to an array of strings
   * 
   * @throws CryptoStatusException if Crypto data is missing
   */
  protected function formatData() {
    include('Localbitcoins.php');
    $this->dataset = array_filter(array_map(function (array $data) {
      if (isset($data['rank'], $data['symbol'], $data['name'], $data['price_usd'], $data['price_btc'], $data['percent_change_1h'])) {
        $data['name'] = $this->camelCase($data['name']);
        $data['price_usd'] = $this->removeTrailingZeros(number_format($data['price_usd'], 2));
        $data['price_btc'] = $this->removeTrailingZeros(number_format($data['price_btc'], 6));

// AD
        return ($data['symbol'] == 'BTC' || $data['symbol'] == 'ETH' || $data['symbol'] == 'DASH') ? "#{$data['symbol']} {$data['price_usd']} USD | {$data['percent_change_1h']}% " : '';
//        return "#{$data['rank']} #{$data['symbol']} (#{$data['name']}): {$data['price_usd']} USD | {$data['price_btc']} BTC | {$data['percent_change_1h']}% 1h";
      }

      throw new CryptoStatusException('Crypto data is missing', 1);
    }, $this->dataset));
// AD
    $this->datasetI = $this->dataset;
    if (isset($promUSD, $promVEFlbc, $promPAB, $promEUR)) {
//      $usdvef = $this->removeTrailingZeros(number_format($promVEFlbc / (($promUSD + $promPAB) / 2),0));      
      $usdvef = number_format($promVEFlbc / (($promUSD + $promPAB) / 2),0);
      $promVEF = number_format($promVEFlbc,0);
      $promPAB = $this->removeTrailingZeros(number_format($promPAB,2));
      $promUSD = $this->removeTrailingZeros(number_format($promUSD,2));
      $promEUR = $this->removeTrailingZeros(number_format($promEUR,2));
      array_push($this->dataset, "#PAB 🇵🇦 BTC {$promPAB} PAB ");
      array_push($this->datasetI, "#PAB BTC {$promPAB} PAB ");
      array_push($this->dataset, "#USD 🇺🇸 BTC {$promUSD} USD ");
      array_push($this->datasetI, "#USD BTC {$promUSD} USD ");
      array_push($this->dataset, "#EUR 🇪🇺 BTC {$promEUR} EUR ");
      array_push($this->datasetI, "#EUR BTC {$promEUR} EUR ");
      array_push($this->dataset, "#VES 🇻🇪 BTC {$promVEF} BS ");
      array_push($this->datasetI, "#VES BTC {$promVEF} BS ");
      array_push($this->dataset, "#VESUSD {$usdvef} BS  #VEN");
      array_push($this->datasetI, "#VESUSD {$usdvef} BS  #VEN");      
    } else {
        throw new CryptoStatusException('LBC data is missing', 1);
    }
  }
  
  /**
   * Convert string to camel case notation.
   *
   * @param string $str
   * @return string
   */
  protected function camelCase(string $str) : string {
    $camel_case_str = '';
    $capitalize = false;
    
    foreach (str_split($str) as $char) {
      if (ctype_space($char)) {
        $capitalize = true;
        continue;
      } else if ($capitalize) {
        $char = strtoupper($char);
        $capitalize = false;
      }

      $camel_case_str .= $char;
    }
    
    return $camel_case_str;
  }
  
  /**
   * Remove trailing zeros after decimal point from number
   *
   * @param string $number
   * @return string
   */
  protected function removeTrailingZeros(string $number) : string {
    $number_arr = array_reverse(str_split($number));
    
    foreach ($number_arr as $key => $value) {
      if (is_numeric($value) && $value == 0) {
        unset($number_arr[$key]);
      } else {
        if (!is_numeric($value)) {
          unset($number_arr[$key]);
        }

        break;
      }
    }
    
    $number = implode(array_reverse($number_arr));
    
    return $number;
  }

  /**
   * Create the Tweets with Crypto data and return them as an array
   * 
   * @return array
   */
  protected function createTweets() : array {
    $tweets = [];
    $start_rank = 1;
    $end_rank = 3;
    $length = 8;
// AD 
// Create the image
    $im = imagecreatetruecolor(1080, 1080);
// Create some colors    
    $white = imagecolorallocate($im, 255, 255, 255);
    $grey = imagecolorallocate($im, 128, 128, 128);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 1079, 1349, $white);
// The text to draw
    $font = __DIR__ . '/../Roboto-Regular.ttf';
//imagettftext($our_image, $size, $angle, $left, $top, $white_color, $font_path, $text);    
      $i = 0;    
      $tweets[$i] = "#CriptoDolar #Bitcoin" . "\n";
//      $tweets[$i] .= date('d-m-Y h:m:s A') . "\n\n"; 

      $tweets[$i] .= "#" . date('dM') . "  " . date('h:m:s A') . "\n\n";       
//      $tweets[$i] .= <img src='path/to/myphoto.jpg' alt='photo of me' /> . "\n\n";
      $tweets[$i+1] = $tweets[$i] . implode("\n", array_slice($this->datasetI, $start_rank - 1, $length));
      $tweets[$i] .= implode("\n", array_slice($this->dataset, $start_rank - 1, $length));

      imagettftext($im, 40, 0, 201, 151, $black, $font, $tweets[$i+1]);
      imagettftext($im, 40, 0, 200, 150, $black, $font, $tweets[$i+1]);
// Using imagepng() results in clearer text compared with imagejpeg()
      imagejpeg($im, __DIR__ . '/../Insta.jpg');
// Clear Memory
      imagedestroy($im);

//    for ($i = 0; $i < 3; $i++) {
//      $tweets[$i] = "#HourlyCryptoStatus (#{$start_rank} to #{$end_rank}):\n\n";
//      $tweets[$i] .= implode("\n\n", array_slice($this->dataset, $start_rank - 1, $length));

//      $start_rank += 3;
//      $end_rank += 3;

//      if ($i == 1) {
//        $end_rank++;
//        $length++;
//      }
//    }

    return $tweets;
  }

  /**
   * Post the specified Tweets
   * 
   * @param array $tweets The Tweets to post
   * @return bool
   */
  protected function postTweets(array $tweets) : bool {
    $last_tweet_id = null;
    $tweet_ids = [];

// AD $i < 3
    for ($i = 0; $i < 1; $i++) {
      if ($last_tweet_id) {
        $tweet = $this->twitter_client->postTweet([
          'status' => '@' . TWITTER_SCREENNAME . ' ' . $tweets[$i],
          'in_reply_to_status_id' => $last_tweet_id
        ], [ 'id' ]);
      } else {
        $tweet = $this->twitter_client->postTweet([
          'status' => $tweets[$i]
        ], [ 'id' ]);
      }

      if (isset($tweet['id'])) {
        $tweet_ids[] = $last_tweet_id = $tweet['id'];
      } else {
        break;
      }
    }

    if (!empty($tweet_ids) && count($tweet_ids) == 1) {
      return true;
    } else {
      $this->failed_tweets = $tweet_ids;

      return false;
    }
  }

  /**
   * Delete the specified Tweets
   * 
   * @param array $tweet_ids The IDs of the Tweets to delete
   * @throws CryptoStatusException if Tweets could not be deleted
   */
  protected function deleteTweets(array $tweet_ids) {
    $deleted_counter = 0;

    foreach ($tweet_ids as $tweet_id) {
      $deleted = $this->twitter_client->deleteTweet($tweet_id);

      if ($deleted) {
        $deleted_counter++;
      }
    }

    if ($deleted_counter != count($tweet_ids)) {
      throw new CryptoStatusException('Deleting Tweets failed', 2);
    }
  }

  /**
   * Send notification mail
   *
   * @throws CryptoStatusException if notification mail could not be sent
   * @return bool
   */
  protected function sendNotificationMail() : bool {
    return $this->mail_client->message([
      'from' => getenv('NOTIFICATION_MAIL_FROM'),
      'to' => getenv('NOTIFICATION_MAIL_TO'),
      'subject' => getenv('NOTIFICATION_MAIL_SUBJECT'),
      'body' => getenv('NOTIFICATION_MAIL_BODY')
    ])->send();
  }

  protected function postInsta() {

$username = getenv('INSTA_USERNAME');
$password = getenv('INSTA_PASSWORD');
$debug = getenv('INSTA_DEBUG');
$truncatedDebug = getenv('INSTA_TRUNC');
//$img = scandir('mallorka/img');
//$photoFilename = trim(__DIR__.'/../mallorka/img/mgps' . random_int(1, 675) . '.jpg');
$photoFilename = __DIR__.'/../Insta.jpg';

if (intval(date('H'))%2 == 0) {
  $news = $this->NewsTop();  
} else {
  $news = $this->NewsEvery();
}  

$Text = array(
  1 => '@criptodolar', '#criptodolar');

$newss = '';
$po = random_int(0, 6);
if (isset($news->status)) {
  $newss = $news->articles[$po]->title . '. 
  ' . $news->articles[$po]->description . '. 
  ' . $news->articles[$po]->source->name . ' 
  ';
  
//  $newss = $news->articles[0]->description . '\n' .  $news->articles[0]->source->name . '\n\n';
//var_dump($news);
//  echo $newss . '<br>' . $po;
//echo $res->articles[0]->description;
//echo '<br>';
//echo $res->articles[0]->url;
//echo $res->articles[0]->urlToImage;
}


$captionText = $newss . $Text[random_int(1, count($Text))] . '

#' . date('dM') . ' 
#crypto
#trading
#forex 
#cryptocurrency
#bitcoin
#local
';
//  echo $captionText;
// Eliminar esta opcion
//\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);
    $ig->timeline->uploadPhoto($photoFilename, ['caption' => $captionText]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    $this->sendNotificationMail();    
}

// Delete File    
unlink(__DIR__ . '/../Insta.jpg');

  }

/**
* NewsApi PHP API
* @author: Davilav
* @donation: 
*
**/

  protected function QueryNews($url) {

    $API_KEY  = getenv('NEWS_API_KEY');
    // Init curl
    static $ch = null; $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; '.php_uname('s').'; PHP/'.phpversion().')');

    curl_setopt($ch, CURLOPT_URL, 'https://newsapi.org'. $url . $API_KEY);    
    $res1 = curl_exec($ch);

    // website/api error ?
    if ($res1 === false)
      throw new Exception('Could not get reply: '.curl_error($ch));

    // return result
    return json_decode($res1);
  }

  protected function NewsTop() {
    return $this->QueryNews('/v2/top-headlines?country=ve&category=business&apiKey=');
  }

  protected function NewsEvery() {
    return $this->QueryNews('/v2/everything?q=bitcoin&sortBy=publishedAt&apiKey=');
  } 

}

