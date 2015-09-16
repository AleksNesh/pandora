<?php
define('MAGENTO_ROOT', dirname(__FILE__));
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;

umask(0);

if ( empty($_GET['store']) ) {
	$_GET['store'] = '';
}
Mage::app( $_GET['store'] );
$config = Mage::getStoreConfig('shoppersettings', $_GET['store']);

require_once("app/code/community/Queldorei/ShopperSettings/classes/twitteroauth.php"); //Path to twitteroauth library

$twitteruser        = $config['social']['twitter'];
$notweets           = $config['social']['tweets_num'];
$consumerkey        = $config['social']['consumerkey'];
$consumersecret     = $config['social']['consumersecret'];
$accesstoken        = $config['social']['accesstoken'];
$accesstokensecret  = $config['social']['accesstokensecret'];

function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}

$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);

header('Content-Type: application/json');
echo json_encode($tweets);
