<?php

/*********************************************************************************************************************
 *
 * A quick and dirty implementation of OST Kit Alpha in PHP.
 *
 * Uses object oriented cURL from here: https://github.com/php-mod/curl/blob/master/src/Curl/Curl.php
 *
 * OST_API_KEY, OST_SECRET, and OST_BASE_URL are set as constants.
 *
 ********************************************************************************************************************/




namespace OST;
use Curl\Curl as Curl;

Class OST{
	
	
	/*
	 * Users
	 */
	
	/*
	 * Name: 3 - 20 characters
	 */
	public static function create_user($name){
		$endpoint = '/users/create';
		$uts = time();
		
		$params = [
			'name' => $name
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params);
		if ($result["success"] === false){
			return false;
		}
		return $result["data"];
	} //create_user
	
	
	
	/*
	 * Name: 3 - 20 characters
	 */
	public static function edit_user($uuid, $name){
		$endpoint = '/users/edit';
		$uts = time();
		
		$params = [
			'uuid' => $uuid,
			'name' => $name
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params);
		if ($result["success"] === false){
			var_dump($result);
			return false;
		}
		return $result["data"];
	} //edit_user
	
	
	
	
	public static function list_users($page = 1, $filter = 'all', $order_by = 'creation_time', $order_dir = 'desc'){
		$endpoint = '/users/list';
		$uts = time();
		
		$params = [
			'page_no' => $page,
			'filter' => $filter,
			'order_by' => $order_by,
			'order' => $order_dir
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params, 'get');
		if ($result["success"] === false){
			return false;
		}
		return $result["data"];
	} //list_users
	
	
	
	
	
	
	
	/*
	 * Airdrop
	 */
	
	
	/*
	 * list_type can be 'all' or 'never_airdropped'
	 */
	public static function airdrop_drop($amount, $type){
		$endpoint = '/users/airdrop/drop';
		$uts = time();
		
		$params = [
			'amount' => $amount,
			'list_type' => $type
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$response = self::curl_request($request_params);
		if ($response["success"] === false){
			return false;
		}
		return $response["data"];
	} //airdrop_drop
	
	
	
	
	public static function airdrop_status($uuid){
		$endpoint = '/users/airdrop/status';
		$uts = time();
		
		$params = [
			'airdrop_uuid' => $uuid
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$response = self::curl_request($request_params, 'get');
		if ($response === false){
			return false;
		}
		return $response["data"];
	} //airdrop_status
	
	
	
	
	
	
	/*
	 * Transactions
	 */
	
	public static function create_tx($name, $kind, $currency_type, $currency_value, $commission_pc){
		$endpoint = '/transaction-types/create';
		$uts = time();
		
		$params = [
			'name' => $name,
			'kind' => $kind,
			'currency_type' => $currency_type,
			'currency_value' =>$currency_value,
			'commission_percent' => $commission_pc
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params);
		if ($result["success"] === false){
			return false;
		}
		return $result["data"];
	} //create_tx
	
	
	
	
	public static function edit_tx($tx_id, $name, $kind, $currency_type, $currency_value, $commission_pc){
		$endpoint = '/transaction-types/edit';
		$uts = time();
		
		$params = [
			'client_transaction_id' => $tx_id,
			'name' => $name,
			'kind' => $kind,
			'currency_type' => $currency_type,
			'currency_value' => $currency_value,
			'commission_percent' => $commission_pc
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params);
		if ($result["success"] === false){
			return false;
		}
		return $result["data"];
	} //edit_tx
	
	
	
	
	public static function list_tx(){
		$endpoint = '/transaction-types/list';
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params, 'get');
		if ($result["success"] === false){
			return false;
		}
		return $result["data"];
	} //list_transactions
	
	
	
	
	public static function execute_tx($from, $to, $name){
		$endpoint = '/transaction-types/execute';
		$uts = time();
		
		$params = [
			'from_uuid' => $from,
			'to_uuid' => $to,
			'transaction_kind' => $name
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		$result = self::curl_request($request_params);
		if ($result["success"] === false){
			return false;
		}
		return $result["data"]["transaction_uuid"];
	} //execute_tx
	
	
	
	/*
	 * Note: This only supports a single transaction UUID.
	 * TODO: fix this up to lookup multiple tx UUIDS.
	 */
	public static function tx_status($tx_uuids){
		$endpoint = '/transaction-types/status';
		$uts = time();
		
		$params = [
			'transaction_uuids' => $tx_uuids
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		$response = self::curl_request($request_params);

		if ($response["success"] === false){
			return false;
		}
		return $response["data"];
	} //tx_status
	
	
	
	
	
	
	/*
	 * Helpers
	 */
	
	public static function make_querystring($endpoint, $fields, $timestamp){
		$fields["api_key"] = OST_API_KEY;
		$fields["request_timestamp"] = $timestamp;
		ksort($fields);
		return $endpoint . '?' . http_build_query($fields);
		
	} //make_querystring
	
	
	
	
	public static function make_request_params($endpoint, $params, $signature, $timestamp){
		$params['api_key'] = OST_API_KEY;
		$params['request_timestamp'] = $timestamp;
		$params['signature'] = $signature;
		return array(
			'request_url' => OST_BASE_URL . $endpoint,
			'params' => $params
		);
	} //make_request_params
	
	
	
	
	public static function create_signature($qs){
		return hash_hmac('sha256', $qs, OST_SECRET);
	} // create_signature
	
	
	
	
	public static function curl_request($request_params, $method = 'post'){
		$curl = new Curl();
		if ($method == 'post'){
			$output = $curl->post($request_params["request_url"], $request_params["params"]);
		}
		else{
			$output = $curl->get($request_params["request_url"], $request_params["params"]);
		}
		return json_decode($output->response, true);
	} //curl_post
	
	
	
	
} //class
