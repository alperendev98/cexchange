<?php
/**
 * Paygate API Class
 *
 * @version		1.1
 * @copyright	Copyright (c) 2013. https://entromoney.com
 */
class Paygate_Api {
	
	// Api url
	const URL_API = 'https://entromoney.com/api/';
	
	// Account id
	protected $_api_user;
	
	// Api id
	protected $_api_id;
	
	// Api pass
	protected $_api_pass;
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Constructor
	 * @param array $config		Parameters that initiate API
	 * The available parameters are:
	 * 	api_user		Account id
	 * 	api_id			Api id
	 * 	api_pass		Api pass
	 */
	public function __construct(array $config)
	{
		foreach (array('api_user', 'api_id', 'api_pass') as $p)
		{
			if ( ! isset($config[$p]))
			{
				 throw new Paygate_Exception("This param is required - {$p}");
			}
			else 
			{
				$this->{'_'.$p} = $config[$p];
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get account name
	 * @param string	$purse	Account purse
	 */
	public function acc_name($purse)
	{
		$params = array();
		$params['purse'] = $purse;
		
		return $this->_exec('acc_name', $params);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get balance of purse
	 * @param string	$purse	Purse num
	 */
	public function balance($purse = '')
	{
		$params = array();
		$params['purse'] = $purse;
		
		return $this->_exec('balance', $params);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get history
	 * @param array $params		History parameters
	 * The available parameters are:
	 * 	batch			Transaction id
	 * 	date_from		Date from (Y-m-d)
	 * 	date_to			Date to (Y-m-d)
	 * 	purse			Your purse
	 * 	account_purse	Account purse
	 * 	type			Transaction type (transfer || transfer_api || transfer_sci)
	 * 	status			Transaction status (pending || completed || cancel)
	 * 	payment_id		Payment id
	 * 	rows_per_page	How many transactions to see per page (default is 10)
	 * 	current_page	Current page
	 */
	public function history(array $params = array())
	{
		return $this->_exec('history', $params);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Send money to other purse
	 * @param string 	$sender_purse	Sender purse
	 * @param string 	$receiver_purse	Receiver purse
	 * @param float 	$amount			Amount
	 * @param string 	$desc			Transfer description
	 * @param string 	$payment_id		Payment id
	 */
	public function transfer($sender_purse, $receiver_purse, $amount, $desc = '', $payment_id = '')
	{
		$params = array();
		$params['sender_purse'] 	= $sender_purse;
		$params['receiver_purse'] 	= $receiver_purse;
		$params['amount'] 			= $amount;
		$params['desc'] 			= $desc;
		$params['payment_id'] 		= $payment_id;
		
		return $this->_exec('transfer', $params);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Send data to the API end received response
	 * @param string	$act		Api action
	 * @param array		$params		Params that will be send
	 */
	protected function _exec($act, array $params = array())
	{
		// Set api config
		$params['api_user'] = $this->_api_user;
		$params['api_id'] 	= $this->_api_id;
		$params['api_pass'] = $this->_api_pass;
		
		// Request to api
		$url = self::URL_API . $act . '.html';
		$res = $this->_curl($url, $params);
		$res = @json_decode($res);
		
		// Error
		if ( ! isset($res->status))
		{
			$res = array();
			$res['status'] = 0;
			$res['result']['error_code'] = 404;
			$res['result']['error_message'] = 'Can not connect to api';
			$res = json_decode(json_encode($res));
		}
		
		return $res;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Send data to the server end received response
	 * @param string 	$url	URL send request
	 * @param array 	$data	Data that will be send
	 */
	protected function _curl($url, array $data = array())
	{
		// Check curl library
		if ( ! function_exists('curl_init'))
		{
			exit('Curl library not installed.');
		}
		
		// Set options
    	$opts = array();
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31';
        $opts[CURLOPT_HEADER] = false;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_TIMEOUT] = 15;
        
        if (count($data))
        {
        	$opts[CURLOPT_POST] = true;
        	$opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
		
	  	if (preg_match('#^https:#i', $url))
        {
     		$opts[CURLOPT_SSL_VERIFYPEER] = FALSE;
        	$opts[CURLOPT_SSL_VERIFYHOST] = 0;
        	//$opts[CURLOPT_SSLVERSION] = 3;
        }
        
		// Init curl
		$curl = curl_init();
		curl_setopt_array($curl, $opts);
		$res = curl_exec($curl);
		if (
			curl_errno($curl) ||
			curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200
		)
		{
			return false;
		}
		
		return $res;
	}
	
}


/**
 * Paygate Exception class
 */
if ( ! class_exists('Paygate_Exception'))
{
	class Paygate_Exception extends Exception {}
}

