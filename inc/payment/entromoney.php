<?php
/**
 * Paygate SCI Class
 *
 * @version		1.1
 * @copyright	Copyright (c) 2013. https://entromoney.com
 */
class Paygate_Sci {
	
	// Sci url
	const URL_SCI = 'https://entromoney.com/payment/sci.html';
	
	// Sci query url
	const URL_SCI_QUERY = 'https://entromoney.com/payment/sci_query.html';
	
	// Account id
	protected $_sci_user;
	
	// Sci id
	protected $_sci_id;
	
	// Sci pass
	protected $_sci_pass;
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Constructor
	 * @param array $config		Parameters that initiate SCI
	 * The available parameters are:
	 * 	sci_user		Account id
	 * 	sci_id			Sci id
	 * 	sci_pass		Sci pass
	 */
	public function __construct(array $config)
	{
		foreach (array('sci_user', 'sci_id', 'sci_pass') as $p)
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
	 * Create hash
	 * @param array $data	Data that will be sent
	 */
	public function create_hash(array $data)
	{
		$hash = array($this->_sci_pass);
		
		foreach (array(
			'receiver', 'amount', 'desc', 'payment_id',
			'url_status', 'url_success', 'url_fail',
		) as $p)
		{
			$hash[] = (isset($data[$p])) ? $data[$p] : '';
		}
		
		$hash = implode('|', $hash);
		$hash = md5($hash);
		
		return $hash;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get transaction details from data return by sci callback
	 * @param array 	$input		Data received from sci callback
	 * @param string 	$error		Error received
	 * @return false || (object)Transaction details
	 */
	public function query(array $input, &$error = '')
	{
		// Get hash
		$hash = (isset($input['hash'])) ? $input['hash'] : '';
		if ( ! $hash)
		{
			$error = 'Invalid hash';
			return false;
		}
		
		// Create security
		$security = array($this->_sci_pass, $hash);
		$security = implode('|', $security);
		$security = md5($security);
		
		// Send data
		$data = array();
		$data['sci_user'] 	= $this->_sci_user;
		$data['sci_id'] 	= $this->_sci_id;
		$data['hash'] 		= $hash;
		$data['security'] 	= $security;
		$res = $this->_curl(self::URL_SCI_QUERY, $data);
		$res = @json_decode($res);
		
		// Error
		if (empty($res->status))
		{
			$error = (isset($res->result)) ? $res->result : 'Can not connect to SCI';
			return false;
		}
		
		return $res->result;
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

