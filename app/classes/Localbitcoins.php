<?php

	$Lbc_Public = new LocalBitcoins_Public_API();
	
	$res = $Lbc_Public->Bitcoinaverage();
//    if (isset($res->USD->avg_1h, $res->VEF->avg_1h, $res->PAB->avg_1h, $res->EUR->avg_1h)) {
//		$promUSD = $res->USD->avg_6h;			
//		$promUSD = $res->USD->rates->last;		
//		$promUSD = $res->USD->avg_1h;		
		$promVEFlbc = $res->VES->avg_1h;
//		$promPAB = $res->PAB->avg_1h;		
		$promEUR = $res->EUR->avg_1h;
//		print_r($res);
//    } else {
//        throw new CryptoStatusException('LBC data is missing', 1);
//    }

//sleep (5);
// Posicion desde donde comienza a promediar
	$p = 2;
	$pagination 		= 1;
//	$arguments = array(
//	'currency'			=> 'VEF',
//	'payment_method'	=> 'transfers-with-specific-bank'
//	);
//	$res = $Lbc_Public->BuyBitcoinsOnline($pagination,$arguments);
//    if (isset($res->data->ad_list[$p]->data->profile->username)) {
//		$promVEF = floatval(($res->data->ad_list[$p]->data->temp_price + $res->data->ad_list[$p+1]->data->temp_price + $res->data->ad_list[$p+2]->data->temp_price + $res->data->ad_list[$p+3]->data->temp_price + $res->data->ad_list[$p+4]->data->temp_price)) / 5;
//	} else {
//    	throw new CryptoStatusException('LBC Venezuela data is missing', 1);
//	}

sleep (5);

	$arguments = array(
	'currency'			=> 'PAB',
	'payment_method'	=> 'transfers-with-specific-bank'		
//	'countrycode'		=> 'pa',
//	'country_name'		=> 'panama',
//	'payment_method'	=> 'transfers-with-specific-bank'		
	);
	$res = $Lbc_Public->BuyBitcoinsOnline($pagination,$arguments);
//    if (isset($res->data->ad_list[$p]->data->profile->username)) {
//		echo $res->data->ad_list[2]->data->profile->username . '<br>';
		$promPAB = floatval(($res->data->ad_list[$p]->data->temp_price + $res->data->ad_list[$p+1]->data->temp_price + $res->data->ad_list[$p+2]->data->temp_price + $res->data->ad_list[$p+3]->data->temp_price + $res->data->ad_list[$p+4]->data->temp_price)) / 5;
//	} else {
//    	throw new CryptoStatusException('LBC Panama data is missing', 1);
//	}

sleep (5);
	$arguments = array(
	'countrycode'		=> 'us',
	'country_name'		=> 'united-states',
	'payment_method'	=> 'transfers-with-specific-bank'	
	);
	$res = $Lbc_Public->BuyBitcoinsOnline($pagination,$arguments);
//    if (isset($res->data->ad_list[$p]->data->profile->username)) {
		$promUSD = floatval(($res->data->ad_list[$p]->data->temp_price + $res->data->ad_list[$p+1]->data->temp_price + $res->data->ad_list[$p+2]->data->temp_price + $res->data->ad_list[$p+3]->data->temp_price + $res->data->ad_list[$p+4]->data->temp_price)) / 5;
//	} else {
//    	throw new CryptoStatusException('LBC United States data is missing', 1);
//	}
