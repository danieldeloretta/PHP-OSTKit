<?php

/*********************************************************************************************************************
 *
 * A quick and dirty implementation of OST Kit Alpha 0.9.2 in PHP.
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
	 * Name restrictions: 3 - 20 characters
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
		
		return self::curl_request($request_params);
	} //create_user
	
	
	
	/*
	 * Name restrictions: 3 - 20 characters
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
		
		return self::curl_request($request_params);
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
		
		return self::curl_request($request_params, 'get');
	} //list_users
	
	
	
	
	
	
	
	/*
	 * Airdrop
	 */
	
	
	/*
	 * list_type ($type) can be 'all' or 'never_airdropped'
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
		
		return self::curl_request($request_params);
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
		
		return self::curl_request($request_params, 'get');
	} //airdrop_status
	
	
	
	
	
	
	/*
	 * Transactions
	 */
	
	public static function create_tx($name, $kind, $currency_type, $currency_value, $commission_pc = 0.0){
		$endpoint = '/transaction-types/create';
		$uts = time();
		
		$params = [
			'name' => $name,
			'kind' => $kind,
			'currency_type' => $currency_type,
			'currency_value' =>$currency_value
		];
		
		if ($kind == 'user_to_user'){
			$params['commission_percent'] = $commission_pc;
		}
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
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
		
		return self::curl_request($request_params);
	} //edit_tx
	
	
	
	
	public static function list_tx(){
		$endpoint = '/transaction-types/list';
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
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
		
		return self::curl_request($request_params);
	} //execute_tx
	
	
	
	/*
	 * $tx_uuids must be an array.
	 *
	 */
	public static function tx_status($tx_uuids){
		$endpoint = '/transaction-types/status';
		$uts = time();
		
		$params = [
			'transaction_uuids[]' => $tx_uuids
		];
		
		$qs = self::make_querystring($endpoint, '', $uts);
		$parsed_uuids = self::tx_status_uuid_helper($tx_uuids);
		$signature = self::create_signature($qs . $parsed_uuids);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //tx_status
	
	
	
	
	
	
	/*
	 * Helpers
	 */
	
	public static function tx_status_uuid_helper($uuids){
		$str = '';
		foreach ($uuids as $u){
			$str .= '&transaction_uuids[]=' . $u;
		}
		return $str;
	} //tx_status_uuid_helper
	
	
	
	
	public static function make_querystring($endpoint, $params, $timestamp){
		$params["api_key"] = OST_API_KEY;
		$params["request_timestamp"] = $timestamp;
		ksort($params);
		return $endpoint . '?' . http_build_query($params);
		
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
