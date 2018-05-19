<?php

/*********************************************************************************************************************
 *
 * A quick and dirty implementation of OST Kit Alpha v1 in PHP.
 *
 * Uses object oriented cURL from here: https://github.com/php-mod/curl/blob/master/src/Curl/Curl.php
 *
 * OST_API_KEY, OST_SECRET, and OST_BASE_URL are set as constants.
 * 
 * 
 * 
 * !! BE SURE TO READ THE KNOWN ISSUES SECTION IN THE README !!
 * 
 *
 ********************************************************************************************************************/




namespace OST;
use Curl\Curl as Curl;

Class OST{
	
	/***************************************************
	 *
	 * Users
	 *
	 ***************************************************/
	
	
	
	/**
	 * @param string $name - must be >= 3 and <= 20 characters - a-z 0-9 spaces only.
	 * @return mixed
	 */
	public static function create_user($name){
		$endpoint = '/users';
		$uts = time();
		
		$params = [
			'name' => $name
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //create_user
	
	
	
	
	/**
	 * @param string $uuid - the uuid of the user to update
	 * @param string $name - must be >= 3 and <= 20 characters - a-z 0-9 spaces only.
	 * @return mixed
	 */
	public static function update_user($uuid, $name){
		$endpoint = '/users/' . $uuid;
		$uts = time();
		
		$params = [
			'name' => $name
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //update_user
	
	
	
	
	/**
	 * @param string $uuid - the uuid of the user to retrieve
	 * @return mixed
	 */
	public static function retrieve_user($uuid){
		$endpoint = '/users/' . $uuid;
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //retrieve_user
	
	
	
	
	/**
	 * Important note: The optional filter "name" does not work as expected. issue raised with devs
	 *
	 * @param array $list_options - options passed. if no options are passed the following defaults are used:
	 * 		page_no = 1
	 * 		airdropped = 'true' (other values: 'false'. NOTE: this should be passed as a string. also, this is a daft option. it should be an optional filter, because if you want ALL users who have the name "bob" you have to make two requests: airdropped true, and airdropped false)
	 * 		order_by = 'created' (other values: 'name')
	 * 		order = 'desc' (other values: 'asc')
	 * 		limit = 10 (other values: any int >= 1 and <= 100)
	 *
	 * 		optional filters:
	 * 			id = comma separated string of UUIDs. maximum of 100
	 * 			name = comma separated string of names. maxmimum 100. !!! this does not currently work at the moment !!!
	 *
	 * @return mixed
	 */
	public static function list_users($list_options = []){
		$endpoint = '/users';
		return self::list_endpoint($endpoint, $list_options);
	} //list_users
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Airdrops
	 *
	 ***************************************************/
	
	
	/**
	 * NOTE: see truth table for how the params affect eachother:
	 * https://dev.ost.com/docs/api_airdrop_execute.html#interdependency-of-parameters
	 *
	 * @param float $amount - the amount of tokens to airdrop
	 * @param string $has_airdropped - string representation of boolean value. true targets users who have received an airdrop.
	 * @param string $user_ids - comma separated string of UUIDs.
	 * @return mixed
	 */
	public static function execute_airdrop($amount, $has_airdropped, $user_ids){
		$endpoint = '/airdrops';
		$uts = time();
		
		$params = [
			'amount' => $amount,
			'airdropped' => $has_airdropped,
			'user_ids' => $user_ids
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //execute_airdrop
	
	
	
	
	/**
	 * @param string $uuid - the airdrop UUID to retrieve
	 * @return mixed
	 */
	public static function retrieve_airdrop($uuid){
		$endpoint = '/airdrops/' . $uuid;
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //retrieve_airdrop
	
	
	
	
	/**
	 * @param array $list_options - options passed. if no options are passed the following defaults are used:
	 * 		page_no = 1
	 * 		order_by = 'created' (no other values) - not sure why this is an option...
	 * 		order = 'desc' (other values: 'asc')
	 * 		limit = 10 (other values: any int >= 1 and <= 100)
	 *
	 * 		optional filters:
	 * 			id = comma separated string of airdrop UUIDs. maximum of 100
	 * 			current_status = comma separated string of names. possible values: incomplete, pending, failed, complete.
	 *
	 * @return mixed
	 */
	public static function list_airdrops($list_options = []){
		$endpoint = '/airdrops';
		return self::list_endpoint($endpoint, $list_options);
	} //list_airdrops
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Actions
	 *  - an action becomes a transaction when it is executed. i think...
	 *
	 ***************************************************/
	
	
	/**
	 * @param string $name - the name of the transaction. this must be unique
	 * @param string $kind - can be user_to_company, company_to_user, or user_to_user
	 * @param string $currency_type - can be either 'BT' or 'USD'
	 * @param string $is_arbitrary_value - string representation of a boolean value(i.e. 'true' or 'false')
	 * @param float $currency_value - the amount in the currency. USD min: 0.01, USD max: 100. BT min: 0.00001, BT max: 100
	 * @param string $is_arbitrary_commission - string representation of a boolean value(i.e. 'true' or 'false')
	 * @param float $commission_pc - the fixed amount for commission.
	 * @return mixed
	 */
	public static function create_action($name, $kind, $currency_type, $is_arbitrary_amount, $amount, $is_arbitrary_commission = 'false', $commission_pc = '0'){
		$endpoint = '/actions';
		$uts = time();
		
		$params = [
			'name' => $name,
			'kind' => $kind,
			'currency' => $currency_type,
			'arbitrary_amount' => $is_arbitrary_amount
		];
		
		if ($is_arbitrary_amount == 'false'){
			$params['amount'] = $amount;
		}
		
		if ($kind == 'user_to_user'){
			if ($is_arbitrary_commission == 'false'){
				$params['commission_percent'] = $commission_pc;
			}
			$params['arbitrary_commission'] = $is_arbitrary_commission;
		}
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //create_action
	
	
	
	
	/**
	 * @param int $action_id - the action id
	 * @param string $name - the name of the transaction. this must be unique
	 * @param string $kind - can be user_to_company, company_to_user, or user_to_user
	 * @param string $currency_type - can be either 'BT' or 'USD'
	 * @param string $is_arbitrary_value - string representation of a boolean value(i.e. 'true' or 'false')
	 * @param float $currency_value - the amount in the currency. USD min: 0.01, USD max: 100. BT min: 0.00001, BT max: 100
	 * @param string $is_arbitrary_commission - string representation of a boolean value(i.e. 'true' or 'false')
	 * @param float $commission_pc - the fixed amount for commission.
	 * @return mixed
	 */
	public static function update_action($action_id, $name, $kind, $currency_type, $is_arbitrary_amount, $amount, $is_arbitrary_commission = 'false', $commission_pc = '0'){
		$endpoint = '/actions/' . $action_id;
		$uts = time();
		
		$params = [
			'name' => $name,
			'kind' => $kind,
			'currency' => $currency_type,
			'arbitrary_amount' => $is_arbitrary_amount
		];
		
		if ($is_arbitrary_amount == 'false'){
			$params['amount'] = $amount;
		}
		
		if ($kind == 'user_to_user'){
			if ($is_arbitrary_commission == 'false'){
				$params['commission_percent'] = $commission_pc;
			}
			$params['arbitrary_commission'] = $is_arbitrary_commission;
		}
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //edit_tx
	
	
	
	
	/**
	 * @param int $action_id - the id of the action
	 * @return mixed
	 */
	public static function retrieve_action($action_id){
		$endpoint = '/actions/' . $action_id;
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //retrieve_action
	
	
	
	
	/**
	 * @param array $list_options - options passed. if no options are passed the following defaults are used:
	 * 		page_no = 1
	 * 		order_by = 'created' (other values: 'name')
	 * 		order = 'desc' (other values: 'asc')
	 * 		limit = 10 (other values: any int >= 1 and <= 100)
	 *
	 * 		optional filters:
	 * 			id = comma separated string of action UUIDs. maximum of 100
	 * 			name = comma separated string of action names. maximum of 100
	 * 			kind = the kind of action. can be 'user_to_user', 'user_to_company', 'company_to_user'. not sure if can pass multiple.
	 * 			arbitrary_amount = filter out actions that only have an arbitrary amount set to 'true' or 'false'
	 * 			arbitrary_commission = filter out actions that only have an arbitrary commission set to 'true' or 'false'
	 *
	 * @return mixed
	 */
	public static function list_actions($list_options = []){
		$endpoint = '/actions';
		return self::list_endpoint($endpoint, $list_options);
	} //list_transactions
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Transactions
	 *  - silly naming i think. i guess the logic is:
	 *    actions become transactions when they are executed.
	 *
	 ***************************************************/
	
	
	/**
	 * @param string $from - UUID of the sender
	 * @param string $to - UUID of the recipient
	 * @param int $action_id - the id of the action to execute
	 * @param float|bool $amount - if provided, this is the transaction amount. only applicable for u2u transactions with arbitrary values
	 * @param float|bool $commission_percent - if provided, this is the commission percentage. obviously. only applicable for u2u transactions with arbitrary commission values
	 * @return mixed
	 */
	public static function execute_action($from, $to, $action_id, $arbitrary_amount = false, $commission_percent = false){
		$endpoint = '/transactions';
		$uts = time();
		
		$params = [
			'from_user_id' => $from,
			'to_user_id' => $to,
			'action_id' => $action_id
		];
		
		if ($arbitrary_amount !== false){
			$params['amount'] = $arbitrary_amount;
		}
		
		if ($commission_percent !== false){
			$params['commission_percent'] = $commission_percent;
		}
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //execute_action
	
	
	
	
	/**
	 * @param string $transaction_id - the uuid of the transaction to retrieve
	 * @return mixed
	 */
	public static function retrieve_transaction($transaction_id){
		$endpoint = '/transactions/' . $transaction_id;
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //retrieve_transaction
	
	
	
	
	/**
	 * @param array $list_options
	 * 		page_no = can be any positive int. defaults to 1.
	 * 		order_by = 'created' (this is the only valid value to pass, so dont even bother including it)
	 * 		order = 'desc' (other values: 'asc')
	 * 		limit = min: 1, max: 100, default: 10
	 *
	 * 		optional filters:
	 * 			id = string of comma separated transaction UUIDs
	 * @return mixed
	 */
	public static function list_transactions($list_options = []){
		$endpoint = '/transactions';
		return self::list_endpoint($endpoint, $list_options);
	} //list_transactions
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Transfers (OST Prime)
	 *
	 ***************************************************/
	
	
	/**
	 * @param string $to_address - the public ETH address, ideally controlled by YOU - ensure you have the private key of this address
	 * @param $amount - value in Wei: should be between 0 and 10^20.
	 * @return mixed
	 */
	public static function create_transfer($to_address, $amount){
		$endpoint = '/transfers';
		$uts = time();
		
		$params = [
			'to_address' => $to_address,
			'amount' => $amount
		];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params);
	} //create_transfer
	
	
	
	
	/**
	 * @param string $transfer_id - the UUID of the transfer to retrieve
	 * @return mixed
	 */
	public static function retrieve_transfer($transfer_id){
		$endpoint = '/transfers/'.$transfer_id;
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //retrieve_transfer
	
	
	
	
	/**
	 * @param array $list_options
	 * 		page_no = can be any positive int. defaults to 1.
	 * 		order_by = 'created' (this is the only valid value to pass, so dont even bother including it)
	 * 		order = 'desc' (other values: 'asc')
	 * 		limit = min: 1, max: 100, default: 10
	 *
	 * 		optional filters:
	 * 			id = string of comma separated transfer UUIDs
	 * @return mixed
	 */
	public static function list_transfers($list_options = []){
		$endpoint = '/transfers';
		return self::list_endpoint($endpoint, $list_options);
	} //list_transfers
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Token details
	 *
	 ***************************************************/
	
	
	/**
	 * @return mixed
	 */
	public static function retrieve_token_details(){
		$endpoint = '/token';
		$uts = time();
		
		$params = [];
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	} //token_details
	
	
	
	
	
	
	
	/***************************************************
	 *
	 * Helpers
	 *
	 ***************************************************/
	
	
	public static function list_endpoint($endpoint, $list_options){
		$uts = time();
		
		$params = [];
		foreach ($list_options as $k => $v){
			$params[$k] = $v;
		}
		
		$qs = self::make_querystring($endpoint, $params, $uts);
		$signature = self::create_signature($qs);
		$request_params = self::make_request_params($endpoint, $params, $signature, $uts);
		
		return self::curl_request($request_params, 'get');
	}
	
	
	/**
	 * @param string $endpoint - the endpoint to request...
	 * @param array $params - the payload to send
	 * @param int $timestamp - unix timestamp
	 * @return string
	 */
	public static function make_querystring($endpoint, $params, $timestamp){
		$params["api_key"] = OST_API_KEY;
		$params["request_timestamp"] = $timestamp;
		ksort($params);
		return $endpoint . '?' . http_build_query($params);
		
	} //make_querystring
	
	
	
	
	/**
	 * @param string $endpoint - the endpoint to the request
	 * @param array $params - the payload to send
	 * @param string $signature - the signature to sign the request with
	 * @param int $timestamp - unix timestamp
	 * @return array
	 */
	public static function make_request_params($endpoint, $params, $signature, $timestamp){
		$params['api_key'] = OST_API_KEY;
		$params['request_timestamp'] = $timestamp;
		$params['signature'] = $signature;
		return array(
			'request_url' => OST_BASE_URL . $endpoint,
			'params' => $params
		);
	} //make_request_params
	
	
	
	
	/**
	 * @param string $qs - the constructed query string to hash
	 * @return string
	 */
	public static function create_signature($qs){
		return hash_hmac('sha256', $qs, OST_SECRET);
	} // create_signature
	
	
	
	
	/**
	 * @param array $request_params
	 * @param string $method - post (default) or get
	 * @return mixed
	 */
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
