<?php

spl_autoload_unregister(array('YiiBase', 'autoload'));
require_once(dirname(__FILE__).'/../external/vendor/autoload.php');
spl_autoload_register(array('YiiBase', 'autoload'));

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\Parser\Client\ClientParserAbstract;

class ClicklogController extends Controller
{

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('index', 'tracking', 'v', 'testpassback'),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array('updateClicksData', 'updateQuery', 'storage'),
				'ips'=>array('54.88.85.63'),
			),
			array('allow', 
				'actions'=>array('updateClicksData', 'admin', 'updateQuery', 'storage', 'test', 'csv'),
				'roles'=>array('admin', 'account_manager_admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Record a click stamp and redirect
	 * to the appropriate landing
	 * @return [type] [description]
	 */
	public function actionTracking($id=null, $vid=null)
	{
		$this->actionIndex($id, $vid);
	}

	public function actionTest()
	{

		if (isset($_GET["user_agent"])) {
			$user_agent = $_GET["user_agent"];
		} else {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		echo $user_agent  . "<hr/>";

		$dd = new DeviceDetector();	
		$dd->setUserAgent( $user_agent );
		$dd->parse();
		$br = $dd->getClient();
		$os = $dd->getOs();

		$os_type		 = isset($os['name']) ? $os['name'] : null;
		$os_version      = isset($os['version']) ? $os['version'] : null;
		$browser_type    = isset($br['name']) ? $br['name'] : null;
		$browser_version = isset($br['version']) ? $br['version'] : null;
		$device_type 	 = $dd->getDeviceName() ? ucfirst($dd->getDeviceName()) : 'other';

		echo "device_type = ". $device_type . "<br/>";
		echo "brand_name = " . $dd->getBrandName() . "<br/>";
		echo "device_model = ". $dd->getModel() . "<br/>";
		echo "device_os = " . $os_type . "<br/>";
		echo "device_os_version = " . $os_version . "<br/>";
		echo "advertised_browser = " . $browser_type . "<br/>";
		echo "advertised_browser_version =" . $browser_version . "<br/>";
	}


	public function actionTestpassback ( $id = null, $vid = null )
	{
		$uri = $_SERVER['REQUEST_URI'];
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		$time = new CDbExpression('NOW()');
		$command = Yii::app()->db->createCommand();

		$command->insert( 'test_passback', array( 
			'uri' => $uri,
			'ref' =>  $ref,
			'time' => $time, 
		) );

		$tmltoken = isset($_GET['tmltoken']) ? $_GET['tmltoken'] : '';

		// echo 'No creative found';
		header('Location: http://tmlbox.co/clicklog/tracking/2034?tmltoken='.$tmltoken);
	}

	private static function qsReplace($url, $qs_array){
		
		foreach ($qs_array as $key => $value) {
			if(preg_match('/QS_/', $key)){
				// $macro = substr($key, 3);
				$qs_macros[] = '{'.$key.'}';
				$qs_values[] = $value;
			}
		}

		if(isset($qs_macros))
			return str_replace($qs_macros, $qs_values, $url);
		else
			return $url;

	}

	private function saveMacros($clickID, $get){

		$saved = 0;

		if(count($get)>1){

			foreach ($get as $key => $value) {
				
				$macro = new ClickMacros();
				
				if($macro->isValidMacro($key)){
					if(isset($this->id)){
						$macro->clicks_log_id = $clickID;
						$macro->name = $key;
						$macro->value = $value;
						if($macro->save())
							$saved++;
						else
							echo json_encode($macro->getErrors());
					}
				}
			}

		}

		return $saved;
	}
	
	/**
	 * Process Generic Request
	 * @param  [type] $id  [description]
	 * @param  [type] $vid [description]
	 * @return [type]      [description]
	 */
	public function actionIndex($id=null, $vid=null)
	{
		// detecting if is postback click
		if(isset($_GET['tmltoken'])){
			$tmltoken = $_GET['tmltoken'];
			$pbClick = ClicksLog::model()->findByAttributes(array('tid'=>$tmltoken));
			// var_dump($pbClick);die('<hr>End');
		}

		isset( $_GET['ts'] ) ? $test = true : $test = false;

		$ts['request'] = $_SERVER['REQUEST_TIME'];
		$ts['start'] = microtime(true);

		// if is new format
		if(isset($id)){
			$cid = $id;
			$nid = NULL;
		}else{
			// Get Request
			if( isset( $_GET['cid'] ) ){
				$cid = $_GET['cid'];
				//print "cid: ".$cid." - nid: ".$nid."<hr/>";
			}else{
				//print "cid: null || nid: null<hr/>";
				//Yii::app()->end();
				$cid = NULL;
			}
			if( isset( $_GET['nid'] ) ){
				$nid = $_GET['nid'];
				//print "cid: ".$cid." - nid: ".$nid."<hr/>";
			}else{
				$nid = NULL;
			}
		}

		if ( isset($_GET['ntoken'] )) {
			// get ntoken if exists
			$ntoken = $_GET['ntoken'];
		} else if (isset($_GET['kp'] )) {
			// get kimia token if exists
			$ntoken = $_GET['kp'];
		} else if (isset($_GET['gclid'] )) {
			// get google token if exists
			$ntoken = $_GET['gclid'];
		} else {
			// unset ktoken
			$ntoken = null;
		}

		// Get Campaign
		if($cid){
			if($campaign = Campaigns::model()->findByPk($cid)){
				$redirectURL          = $campaign->url;
				if($nid==NULL){
					$nid              = $campaign->providers_id;
				}
				$ts['campaign']       = microtime(true);
				
				$s2s = $campaign->opportunities->server_to_server ? $campaign->opportunities->server_to_server : NULL;
				$ts['s2s']            = microtime(true);
			}else{
				//print "campaign: null<hr/>";
				//Yii::app()->end();
				$cid = NULL;
				$nid = NULL;
			}
		}else{

		}

		//print_r($campaign);
		//print "url: ".$redirectURL."<hr/>";

		// Write down a log

		if(isset($pbClick))
			$model = $pbClick;//update passback click
		else
			$model = new ClicksLog();//new click

		//$model->id         = 2;
		$model->campaigns_id = $cid;
		$model->providers_id = $nid;
		$model->tid = 0;
		// $model->date       = date("Y-m-d H:i:s");
		$model->date       = new CDbExpression('NOW()');

		// Get custom parameters
		
		if ( Providers::model()->findByPk($nid)->has_s2s ) {
			foreach ($_GET as $key => $value) {
				$ignore_params = array('g_net', 'g_key', 'g_cre', 'g_pla', 'g_mty', 'ntoken', 'nid', 'cid', 'ts', 'id');
				if ( !in_array($key, $ignore_params) ) {
					$model->custom_params != NULL ? $model->custom_params .= '&' : NULL ;
					$model->custom_params .= $key . '=' . $value;
				}
			}
		}

		// Get visitor parameters
		
		$model->server_ip    = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;
		$model->ip_forwarded = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : null;
		$model->user_agent   = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$model->languaje     = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
		$model->referer      = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		$model->app          = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
		$model->redirect_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

		// get macros if exists
 
		$model->network_type = isset($_GET["g_net"]) ? $_GET["g_net"] : null;
		$model->keyword      = isset($_GET["g_key"]) ? $_GET["g_key"] : null;
		$model->creative     = isset($_GET["g_cre"]) ? $_GET["g_cre"] : null;
		$model->placement    = isset($_GET["g_pla"]) ? $_GET["g_pla"] : null;
		$model->match_type   = isset($_GET["g_mty"]) ? $_GET["g_mty"] : null;

		// get query if exists

		$tmp = array();
		if (preg_match('/q=[^\&]*/', $model->referer, $tmp)) {
			$model->query = urldecode(substr($tmp[0], 2));
		}


		$ts['model']         = microtime(true);
		
		
		// if($test || $campaign->post_data == '1'){
		
			// Get ip data

			$ip = isset($model->ip_forwarded) ? $model->ip_forwarded : $model->server_ip;
			if(isset($ip)){
				// $binPath      = YiiBase::getPathOfAlias('application') . "/data/ip2location.BIN";
				$binPath        = Yii::app()->params['ipDbFile'];
				$location       = new IP2Location($binPath, IP2Location::FILE_IO);
				$ipData         = $location->lookup($ip, IP2Location::ALL);
				//$model->country = $ipData->countryName;
				$model->country = $ipData->countryCode;
				$model->city    = $ipData->cityName;
				$model->carrier = $ipData->mobileCarrierName;
			}

			$ts['ip2location']  = microtime(true);

			// Get userAgent data
			// .example:
			// Mozilla/5.0 (Linux; Android 4.4.2; GT-I9500 Build/KOT49H) 
			// AppleWebKit/537.36 (KHTML, like Gecko)
			// Chrome/36.0.1985.131 Mobile Safari/537.36
			// .example:
			// Mozilla/5.0 (Linux; U; Android 4.1.1; es-ar; HTC One X Build/JRO03C) 
			// AppleWebKit/534.30 (KHTML, like Gecko) 
			// Version/4.0 Mobile Safari/534.30

			if(isset($model->user_agent)){
				/*
				$wurfl = WurflManager::loadWurfl();
				$device = $wurfl->getDeviceForUserAgent($model->user_agent);
				$model->device          = $device->getCapability('brand_name');
				$model->device_model    = $device->getCapability('marketing_name');
				$model->os              = $device->getCapability('device_os');
				$model->os_version      = $device->getCapability('device_os_version');
				$model->browser         = $device->getVirtualCapability('advertised_browser');
				$model->browser_version = $device->getVirtualCapability('advertised_browser_version');

				if ($device->getCapability('is_tablet') == 'true')
					$model->device_type = 'Tablet';
				else if ($device->getCapability('is_wireless_device') == 'true')
					$model->device_type = 'Mobile';
				else
					$model->device_type = 'Desktop';
				*/
				$dd = new DeviceDetector();
				$dd->setUserAgent( $model->user_agent );
				$dd->parse();
				$br = $dd->getClient();
				$os = $dd->getOs();

				$model->device          = $dd->getBrandName();
				$model->device_model    = $dd->getModel();
				$model->os              = isset($os['name']) ? $os['name'] : null;
				$model->os_version      = isset($os['version']) ? $os['version'] : null;
				$model->browser         = isset($br['name']) ? $br['name'] : null;
				$model->browser_version = isset($br['version']) ? $br['version'] : null;
				$model->device_type 	 = $dd->getDeviceName() ? ucfirst($dd->getDeviceName()) : 'other';
			}

			$ts['wurfl'] = microtime(true);

		// }
		

		//var_dump($model);
		//print "<hr/>";

		//Yii::app()->end();

		// Save active record and redirect
		
		if($model->save()){

			// if click is from a vector, log it
			if(isset($vid)){
				$modelVL = new VectorsLog();
				$modelVL->clicks_log_id = $model->id;
				$modelVL->vectors_id = $vid;
				$modelVL->save();
			}
			// else{
				// if click has incoming macros, log it
				// not working with vectors
				// $this->saveMacros($model->id, $_GET);		
			// }


			// if($ntoken){
			// 	$tmltoken = $ntoken;
			// }else{
			// 	$tmltoken = md5($model->id);
			// }
				

			/*
			// descomentar para habilitar log

			$headers = var_export($_SERVER, true);
			$headers.= var_export($_COOKIE, true);
			$headers.= var_export(apache_response_headers(), true);
			$headers.= var_export(apache_request_headers(), true);
			// genero un log de headers para identificar
			// el origen del click
			$gc_log = fopen( "log/clicks.log", "a");
			fwrite($gc_log, "---------------------------"."\n\r");
			fwrite($gc_log, $ktoken."\n\r");
			fwrite($gc_log, "---------------------------"."\n\r");
			fwrite($gc_log, $headers."\n\r") ? null : fwrite($gc_log, "error"."\n\r");
			fwrite($gc_log, "---------------------------"."\n\r");
			fclose($gc_log);

			 */
			//die($headers);
			//die($headers);


			//print "guardado - tid: ".$tmltoken;
			//print "<hr/>";
			
			$tmltoken = md5($model->id);
			$model->tid = $tmltoken;
			$model->ext_tid = $ntoken;
			$model->save();

			// Guardo los datos en cookies (Expira en 1 hora)
			//setcookie('tmltoken', $tmltoken, time() + 1 * 1 * 60 * 60, '/');

			if($cid){
				if($s2s){
					if( strpos($redirectURL, "?") ){
						$redirectURL.= "&";
					} else {
						$redirectURL.= "?";
					}
					$redirectURL.= $s2s."=".$tmltoken;
				}
			}

			// replace qs macros
			$redirectURL = self::qsReplace($redirectURL, $_GET);

			// send outgoing macros
			if($model->haveMacro($redirectURL))
				$redirectURL = $model->replaceMacro($redirectURL);
			
			if($campaign->post_data == '1'){

				$dataQuery['os']      = $model->os."-".$model->os_version;
				$dataQuery['device']  = $model->device."-".$model->device_model;
				$dataQuery['country'] = $model->country;
				$dataQuery['carrier'] = $model->carrier;
				$dataQuery['referer'] = $model->referer;
				$dataQuery['app']     = $model->app;
				$dataQuery['keyword'] = $model->keyword;

				$redirectURL.= '&'.http_build_query($dataQuery, null, '&', PHP_QUERY_RFC3986);
			}
			
			
			// testing
			/*
			echo $redirectURL;
			echo "<hr/>";
			echo "time: ". (microtime(true) - $timestampStart);
			//var_dump($_SERVER);
			Yii::app()->end();
			*/
			
			if($cid){
				$ts['redirect'] = microtime(true);

				// redirect to campaign url
				if($test){
					echo json_encode($ts);
					die($redirectURL);
				}else{
					//$this->redirect($redirectURL);
					header("Location: ".$redirectURL);
				}
			}else{
				logError("no redirect");
			}
				
				
		}else{
			logError("no guardado");
		}

	}

	/**
	 * Process Vector Request
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function actionV($id=null)
	{
		// get vectors ID
		$v = isset($_GET['v']);
		// get associated campaigns
		$vhc    = VectorsHasCampaigns::model()->findAll('vectors_id=:vid', array(':vid'=>$id));
		//get campaigns connection type, carrier and frequency
		foreach ($vhc as $cmp) {
			$cid = $cmp->campaigns_id;
			$type = $cmp->campaigns->opportunities->wifi;
			$freq = $cmp->freq;
			
			if($type != 'Specific Carrier')
				// repeat campaigns in the array as freq number 
				for($i=0;$i<$freq;$i++){
					$campaigns[$type][] = $cid;
				}
			else
				for($i=0;$i<$freq;$i++){
					$campaigns[$type][strtoupper($cmp->campaigns->opportunities->carriers->mobile_brand)][] = $cid;
				}
		}

		if($v){
			echo json_encode($campaigns, JSON_PRETTY_PRINT);
			echo '<hr>';
		}

		// REQUEST 
		
		if(isset($_GET['ip']))
			$ip = $_GET['ip'];
		else
			$ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;
		
		// get user location and carrier
		$binPath  = Yii::app()->params['ipDbFile'];
		$location = new IP2Location($binPath, IP2Location::FILE_IO);
		$ipData   = $location->lookup($ip, IP2Location::ALL);
		$country  = $ipData->countryCode;
		$carrier  = strtoupper($ipData->mobileCarrierName);
		
		// HARCODE CARRIER
		if(isset($_GET['c'])) $carrier = strtoupper($_GET['c']);


		// not used
		if($carrier == '-')
			$connection_type = 'WIFI';
		else
			$connection_type = '3G';
		$campaign = $carrier != '-' ? $carrier : 'WIFI';
		// not used

		if($v){
			echo json_encode($ipData, JSON_PRETTY_PRINT);
			echo '<hr>';
		}

		// if user has carrier and vector has campaigns for this carrier
		if( $carrier != '-' && isset( $campaigns['Specific Carrier'] ) && isset( $campaigns['Specific Carrier'][$carrier] ) ){

			// set campaign/s target
			$target = $campaigns['Specific Carrier'][$carrier];
			
			if($v){
				echo 'Showing campaign for: '.$carrier;
				echo '<hr>';
			}

		}

		// if user has carrier and vector doesn't have campaigns for this carrier
		if( $carrier != '-' && isset( $campaigns['Specific Carrier'] ) && !isset( $campaigns['Specific Carrier'][$carrier] ) ){

			// find posible name equivalence
			$carrier_eq = CarriersEquivalence::model()->findByAttributes(array('country'=>$country, 'alias'=>$carrier));
			
			// if there is an equivalence
			if(isset($carrier_eq)){

				// if vector has campaign for this equivalence
				if(isset( $campaigns['Specific Carrier'][$carrier_eq->name] )){

					// set campaign/s target
					$target = $campaigns['Specific Carrier'][$carrier_eq->name];

					if($v){
						echo 'Showing campaign for: '.$carrier.' | Alias of: '.$carrier_eq->name;
						echo '<hr>';
					}
				}else{
					if($v){
						echo 'No campaigns found for carrier: '.$carrier.' | Alias of: '.$carrier_eq->name;
						echo '<hr>';
					}
				}
			}else{
				if($v){
					echo 'No campaigns or equivalence found for carrier: '.$carrier;
					echo '<hr>';
				}
				Yii::log('Carrier not fount: '.$carrier.' | Country: '.$country, 'error', 'system.model.clicksLog.v');
			}
		}


		// if user are wifi or carrier doesn't match
		if(!isset($target) && isset($campaigns['Open'])){

			// set open campaign/s target
			$target = $campaigns['Open'];
			
			if($v){
				echo 'Showing generic campaign';
				echo '<hr>';
			}
		}

		if(!isset($target)){
			die('No campaign match');
		}


		// echo json_encode($target);
		// echo '<hr>';

		// if there are 2 target campaigns or more 
		// target campaigns rotate depends on the freq
		$count  = count($target);
		$random = mt_rand(0, $count - 1);
		$cid = $target[$random];

		if($v){
			echo 'Showing campaign: '.$cid;
		}else{
			// send vector id
			$this->actionIndex($cid, $id);
		}
		
	}



	/**
	 * Update columns for every register in clicks_log from date = $_GET['hourFrom'],
	 * between $_GET['hourFrom'] and $_GET['hourTo'].
	 * 
	 * If $_GET['hourFrom'] and $_GET['hourTo'] are not provided update last from last hour o'clock
	 * to current time.
	 */
	public function actionUpdateClicksData() 
	{
		date_default_timezone_set('UTC');
		set_time_limit(1000000);

		$date = date('Y-m-d', strtotime('today'));
		if (isset($_GET['date']))
			$date = $_GET['date'];

		$hourTo = date('H:i', strtotime('now'));
		if (isset($_GET['hourFrom']) && isset($_GET['hourTo'])) {
			$hourFrom = $_GET['hourFrom'];
			$hourTo   = $_GET['hourTo'];
		}

		$tmp           = new DateTime($date . ' ' . $hourTo . ':00');
		$timestampTo   = clone $tmp;
		if ( isset($hourFrom) )
			$timestampFrom = new DateTime($date . ' ' . $hourFrom . ':00');
		else
			$timestampFrom = $tmp->sub(new DateInterval('PT1H' . $timestampTo->format('i') . 'M'));

		$criteria=new CDbCriteria;
		$criteria->compare('date', '>=' . $timestampFrom->format('Y-m-d H:i:s'));
		$criteria->compare('date', '<=' . $timestampTo->format('Y-m-d H:i:s'));
		$dataProvider = new CActiveDataProvider("ClicksLog", array(
			'criteria' => $criteria,
			'pagination' => array(
                'pageSize' => 100,
            ),
		));
		$iterator = new CDataProviderIterator($dataProvider);

		// initializing tools 
		//$wurfl    = WurflManager::loadWurfl();
		// $binPath      = YiiBase::getPathOfAlias('application') . "/data/ip2location.BIN";
		$binPath  = Yii::app()->params['ipDbFile'];
		$location = new IP2Location($binPath, IP2Location::FILE_IO);
		$dd = new DeviceDetector();

		echo 'total: '.count($iterator).'<hr/>';
		$timeBegin = time();
		$countClicks = 0;
		foreach ($iterator as $click) {

			$countClicks++;
			if ( 
				$click->country         !== NULL && 
				$click->city            !== NULL && 
				$click->carrier         !== NULL && 
				$click->browser         !== NULL && 
				$click->browser_version !== NULL && 
				$click->device_type     !== NULL && 
				$click->os              !== NULL && 
				$click->os_version      !== NULL && 
				$click->device          !== NULL &&
				$click->device_model    !== NULL 
				)
			{
				echo $countClicks . " - " . $click->date . " - " . $click->id . "<br/>";
				continue;
			}

			$ip                     = $click->ip_forwarded != NULL ? $click->ip_forwarded : $click->server_ip;
			$ipData                 = $location->lookup($ip, IP2Location::ALL);
			$click->country         = $ipData->countryCode;
			$click->city            = $ipData->cityName;
			$click->carrier         = $ipData->mobileCarrierName;
			
			//$device                 = $wurfl->getDeviceForUserAgent($click->user_agent);
			$dd->setUserAgent( $click->user_agent );
			$dd->parse();
			$br = $dd->getClient();
			$os = $dd->getOs();

			$click->device          = $dd->getBrandName();
			$click->device_model    = $dd->getModel();
			$click->os              = isset($os['name']) ? $os['name'] : null;
			$click->os_version      = isset($os['version']) ? $os['version'] : null;
			$click->browser         = isset($br['name']) ? $br['name'] : null;
			$click->browser_version = isset($br['version']) ? $br['version'] : null;
			$click->device_type 	 = $dd->getDeviceName() ? ucfirst($dd->getDeviceName()) : 'other';

			$tmp = array();
			if (preg_match('/q=[^\&]*/', $click->referer, $tmp)) {
				$click->query = urldecode(substr($tmp[0], 2));
			}
			
			$click->device          === NULL ? $click->device = "" : null;
			$click->device_model    === NULL ? $click->device_model = "" : null;
			$click->os              === NULL ? $click->os = "" : null;
			$click->os_version      === NULL ? $click->os_version = "" : null;
			$click->browser         === NULL ? $click->browser = "" : null;
			$click->browser_version === NULL ? $click->browser_version = "" : null;

			/*
			if ($device->getCapability('is_tablet') == 'true')
				$click->device_type = 'Tablet';
			else if ($device->getCapability('is_wireless_device') == 'true')
				$click->device_type = 'Mobile';
			else
				$click->device_type = 'Desktop';
			*/
		
			$click->save();
			echo $countClicks . " - " . $click->date . " - " . $click->id . " - updated<br/>";
		}
		echo "Execution time: " . (time() - $timeBegin) . " seg <br>";

	}

	public function actionUpdateQuery() 
	{
		set_time_limit(1000000);

		if (isset($_GET['useUTC'])) {
			date_default_timezone_set('UTC');
		}

		if (isset($_GET['date']) && isset($_GET['hourFrom']) && isset($_GET['hourTo'])) {
			$date     = $_GET['date'];
			$hourFrom = $_GET['hourFrom'];
			$hourTo   = $_GET['hourTo'];
		} else {
			echo "Missing parameters: date, hourFrom, hourTo";
			return;
		}

		$timestampFrom = new DateTime($date . ' ' . $hourFrom . ':00');
		$timestampTo   = new DateTime($date . ' ' . $hourTo . ':00');

		$criteria=new CDbCriteria;
		$criteria->compare('date', '>=' . $timestampFrom->format('Y-m-d H:i:s'));
		$criteria->compare('date', '<=' . $timestampTo->format('Y-m-d H:i:s'));
		$criteria->compare('providers_id', '4');
		// $criteria->addCondition('query IS NULL');
		$dataProvider = new CActiveDataProvider("ClicksLog", array(
			'criteria'   => $criteria,
			'pagination' => array(
                'pageSize' => 100,
            ),
		));
		$iterator = new CDataProviderIterator($dataProvider);

		echo 'total: '.count($iterator).'<hr/>';
		$timeBegin = time();
		$countClicks = 0;
		foreach ($iterator as $click) {
			$countClicks++;

			if ( $click->query !== NULL ) {
				echo $countClicks . " - " . $click->date . " - " . $click->id . " - " . $click->query . "<br/>";
				continue;
			}

			$tmp = array();
			if (preg_match('/q=[^\&]*/', $click->referer, $tmp)) {
				$click->query = urldecode(substr($tmp[0], 2));
			}
			
			$click->save();
			echo $countClicks . " - " . $click->date . " - " . $click->id . " - " . $click->query . " - updated<br/>";
		}
		echo "Execution time: " . (time() - $timeBegin) . " seg <br>";

	}

	/**
	 * Store clicks_log data from 1 month ago in clicks_log_storage_2
	 * @return string Transaction status
	 */
	public function actionStorage()
	{
		$copyRows     = 'INSERT INTO clicks_log_storage_2 
						SELECT * FROM clicks_log WHERE id < (
							SELECT id FROM clicks_log 
							WHERE DATE(date) = DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
							ORDER BY id ASC 
							LIMIT 0,1
							) 
						ORDER BY id 
						ASC LIMIT 0,300000';

		$deleteRows	 = 'DELETE FROM clicks_log 
						WHERE id <= (
							SELECT id FROM clicks_log_storage_2 
							ORDER BY id DESC 
							LIMIT 0,1
							) 
						ORDER BY id ASC';
		
		$result['action'] = 'ClicksLogStorage';
		$command = Yii::app()->db->createCommand($copyRows);
		$result['inserted'] = $command->execute();
		$command = Yii::app()->db->createCommand($deleteRows);
		$result['deleted'] = $command->execute();
		echo json_encode($result);
	}

	public function logError($msg){
		
		Yii::log( $msg . "<hr/>\n ERROR: " . json_encode($model->getErrors()), 'error', 'system.model.clicksLog');

	}


	public function actionAdmin(){
		KHtml::paginationController();
		
		$model=new ClicksLog('search');
		$model->unsetAttributes();  // clear any default values
		if ( isset($_REQUEST['download']) )
		{
			$this->_sendCsvFile( $model );
		}

		$this->render('admin',array(
			'model'=>$model,
		));	
	}

	public function actionCsv(){
		/*
		$dateStart = isset($_GET['date_start']) ? $_GET['date_start'] : date("Y-m-d", strtotime("yesterday"));
		$dateEnd = isset($_GET['date_end']) ? $_GET['date_end'] : date("Y-m-d", strtotime("today"));
	
		$criteria = new CDbCriteria;
		$criteria->with = array('campaigns.opportunities.regions.financeEntities.advertisers','providers');
		
		// $criteria->compare('providers.type', 'Google AdWords');
		$criteria->compare('t.campaigns_id','1419');
		$criteria->addBetweenCondition('DATE(t.date)', $dateStart, $dateEnd);

		$criteria->select = array(
			't.id as id',
			't.date as date',
			'advertisers.name as advertiser',
			'providers.name as traffic_source'
			);

		$data = ClicksLog::model()->findAll($criteria);
		*/
		$model  = new ClicksLog();
		$this->_sendCsvFile( $model );

	}


	protected function _sendCsvFile ( $model )
	{
		$csvData = array();
			
		$dateStart = isset($_REQUEST['dateStart']) ? $_REQUEST['dateStart'] : date("Y-m-d", strtotime("yesterday"));
		$dateEnd = isset($_REQUEST['dateEnd']) ? $_REQUEST['dateEnd'] : date("Y-m-d", strtotime("today"));
		$timeStart = isset($_REQUEST['timeStart']) ? $_REQUEST['timeStart'] : '12:00 AM';
		$timeEnd = isset($_REQUEST['timeStart']) ? $_REQUEST['timeStart'] : '11:59 PM';	
		$provider = isset($_REQUEST['ts']) ? $_REQUEST['ts'] : null;
		$onlyConversions = isset($_REQUEST['c']) ? true : false;

		$sum = isset($_REQUEST['sum']) ? $_REQUEST['sum'] : array();
		$filters                    = array();
		$filters['provider']        = null; 
		$filters['advertiser']      = null;
		$filters['country']         = null;
		$filters['campaign']        = null;
		$filters['vector']          = null;
		$filters['opportunity']     = null;
		$filters['account_manager'] = null;
		$filters['category']        = null;
		$filters['carrier']         = null;

		if ( isset($_REQUEST['filter']) )
		{
			foreach ( $_REQUEST['filter'] as $f => $v )
			{
				$filters[$f] = $v;
			}
		}

		$group1 = isset($_REQUEST['group1']) ? $_REQUEST['group1'] : array();
		$group2 = isset($_REQUEST['group2']) ? $_REQUEST['group2'] : array();
		$group = array_merge($group1, $group2);

		$grouped = false;

		foreach ( $group as $property => $value )
		{
			if ( $value != 0 )
			{
				$grouped = true;
				break;
			}
		}		

		$dp = $model->csvReport( $dateStart, $dateEnd, $provider, $onlyConversions, $group, $filters );

		foreach ($dp->getData() as $data) {
			$row = array();
/*
			if ( !$grouped )
			{
				$row['Click ID']        		= $data->tid;
				$row['Date']      				= $data->click_date;
				$row['Time']      				= $data->click_time;				
			}
*/
			if ( $group['Date'] == 1 )
			{
				$row['Date']      				= $data->click_date;
				$row['Time']      				= $data->click_time;						
			}

			if ( $group['TrafficSource'] == 1 )
			{
				$row['Traffic Source ID']  		= $data->providers_id;				
				$row['Traffic Source']  		= $data->traffic_source;
				$row['Traffic Source Type']  	= $data->traffic_source_type;				
			}

			if ( $group['TrafficSourceType'] == 1 )
			{
				$row['Traffic Source Type']  	= $data->traffic_source_type;				
			}			


			if ( $group['Advertiser'] == 1 )
				$row['Advertiser']      		= $data->advertiser;


			if ( $group['Country'] == 1 )
				$row['Country']      			= $data->country_name;		


			if ( $group['Vector'] == 1 )
			{	
				$row['Vector ID']     			= $data->vectors_id;				
				$row['Vector']     				= $data->vector_name;
			}


			if ( $group['Campaign'] == 1 )
			{
				$row['Campaign ID']     		= $data->campaigns_id;
				$row['Campaign Name']   		= $data->campaigns_name;				
			}

			if ( $group['Product'] == 1 )
				$row['Product'] 		 		= $data->product;


			if ( $grouped )
			{
				$row['Clicks']       			= $data->totalClicks;			
			}

			if ( $sum['Conv'] == 1 )
				$row['Conversions']	     		= $data->totalConv;	

			if ( $sum['Revenue'] == 1 )
				$row['Revenue']    				= $data->revenue;
			
			if ( $sum['Spend'] == 1 )
				$row['Spend']    				= $data->spend;			


			if ( $sum['Profit'] == 1 )
			{
				$row['Profit']    				= $data->revenue-$data->spend;			
			}

			if ( $group['ServerIP'] == 1 )
				$row['Server IP']     			= $data->server_ip;			

			if ( $group['Carrier'] == 1 )
				$row['Carrier']     			= $data->carrier_name;				

			if ( $group['OS'] == 1 )
				$row['OS']     					= $data->os;

			if ( $group['OSVersion'] == 1 )
				$row['OS Version']     			= $data->os_version;	

			if ( $group['DeviceType'] == 1 )
				$row['Device Type']    			= $data->device_type;						

			if ( $group['DeviceBrand'] == 1 )
				$row['Device Brand']    		= $data->device;	

			if ( $group['DeviceModel'] == 1 )
				$row['Device Model']    		= $data->device_model;		

			if ( $group['Browser'] == 1 )
				$row['Browser']    				= $data->browser;	

			if ( $group['BrowserVersion'] == 1 )
				$row['Browser Version']    		= $data->browser_version;			

/*
			if ( !$grouped )
			{
				$row['Conv Date']       		= $data->conv_date;
				$row['Conv Time']       		= $data->conv_time;							
			}
*/
			$csvData[] = $row;
		}

		$csv = new ECSVExport( $csvData );
		$csv->setEnclosure(chr(0));//replace enclosure with caracter
		$csv->setHeader( 'content-type', 'application/csv;charset=UTF-8' );
		$content = $csv->toCSV();   

		if(isset($_REQUEST['v']))
			echo str_replace("\n", '<br/>', $content);
		else
		{
			$filename = 'ClickLogReport_'.date("Y-m-d", strtotime($dateStart)).'.csv';
			Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", true);		
		}		
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}
