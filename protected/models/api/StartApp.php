<?php

class StartApp
{ 

	private $provider_id = 24;
	private $apiLog;

	public function downloadInfo($offset)
	{
		date_default_timezone_set('UTC');
		$return = '';

		if ( isset( $_GET['date']) ) {
		
			$date = $_GET['date'];
			$this->apiLog = ApiLog::initLog($date, $this->provider_id, null);
			$return.= $this->downloadDateInfo($date);
		
		} else {

			if(date('G')<=$offset){
				$return.= '<hr/>yesterday<hr/>';
				$date = date('Y-m-d', strtotime('yesterday'));
				$this->apiLog = ApiLog::initLog($date, $this->provider_id, null);
				$return.= $this->downloadDateInfo($date);
			}
			//default
			$return.= '<hr/>today<hr/>';
			$date = date('Y-m-d', strtotime('today'));
			$this->apiLog = ApiLog::initLog($date, $this->provider_id, null);
			$return.= $this->downloadDateInfo($date);
		
		}

		return $return;
	}

	public function downloadDateInfo($date)
	{
		$return = '';

		if ( isset( $_GET['date']) ) {
			$date = $_GET['date'];
		} else {
			$date = date('Y-m-d', strtotime('today'));
		}

		/*
		// validate if info have't been dowloaded already.
		if ( DailyReport::model()->exists("providers_id=:providers AND DATE(date)=:date", array(":providers"=>$this->provider_id, ":date"=>$date)) ) {
			Yii::log("Information already downloaded.", 'warning', 'system.model.api.startapp');
			return 2;
		}
		*/

		// Get json.
		$network = Providers::model()->findbyPk($this->provider_id);
		$partner = $network->token1;
		$token = $network->token2;
		$apiurl = $network->url;
		$url = $apiurl . "?partner=" . $partner . "&token=" . $token . "&startDate=" . $date . "&endDate=" . $date;

		//$url = $apiurl . "?partner=" . $partner . "&token=" . $token . "&startDate=2016-01-01&endDate=2016-10-11";		

		$this->apiLog->updateLog('Processing', 'Getting traffic data');
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		$result = json_decode($result);
		if (!$result) {
			Yii::log("ERROR - decoding json", 'error', 'system.model.api.startapp');
			return 1;
		}
		curl_close($curl);

		// print provider api results
		//var_export(json_encode($result).'<br>');

		$this->apiLog->updateLog('Processing', 'Writing traffic data');

		$updated = 0;
		//var_export($result);die();
		
		// Save campaigns information 
		foreach ($result->data as $campaign) {

			if ( $campaign->impressions == 0 && $campaign->clicks == 0) { // if no impressions dismiss campaign
				continue;
			}

			// get campaign ID used in Server, from the campaign name use in the external provider
			$campaigns_id = Utilities::parseCampaignID($campaign->campaignName);

			if ( !$campaigns_id ) {
				Yii::log("Invalid external campaign name: '" . $campaigns_id, 'warning', 'system.model.api.startapp');
				continue;
			}

			// if is vector
			if(substr($campaign->campaignName, 0, 1)=='v'){

				$vid = Utilities::parseVectorID($campaign->campaignName);
				$vectorModel = Vectors::model()->findByPk($vid);

				$ret = $vectorModel->explodeVector(array('spend'=>$campaign->spent,'date'=>$date));
				$return .= json_encode($ret);
				$return.= '<br>';
				continue;
			}
			
			$campaigns_id = Utilities::parseCampaignID($campaign->campaignName);			

			// if exists overwrite, else create a new
			$dailyReport = DailyReport::model()->find(
				"providers_id=:providers AND DATE(date)=:date AND campaigns_id=:cid", 
				array(
					":providers"=>$this->provider_id, 
					":date"=>$date, 
					":cid"=>$campaigns_id,
					)
				);
			if(!$dailyReport){
				$dailyReport = new DailyReport();
				$return.= "<hr/>New record: ";
			}else{
				$return.= "<hr/>Update record: ".$dailyReport->id;
			}

			$dailyReport->campaigns_id = $campaigns_id;
			$dailyReport->date = $date;
			$dailyReport->providers_id = $this->provider_id;
			$dailyReport->imp = $campaign->impressions;
			$dailyReport->clics = $campaign->clicks;
			$dailyReport->conv_api = ConvLog::model()->count("campaigns_id=:campaignid AND DATE(date)=:date", array(":campaignid"=>$dailyReport->campaigns_id, ":date"=>$date));
			//$dailyReport->conv_adv = 0;
			$dailyReport->spend = $campaign->spent;

			$campaignModel = Campaigns::model()->findByPk($campaigns_id);
			$model_adv = $campaignModel->opportunities->model_adv;
			$return.= ' - '.$model_adv;

			if($model_adv != 'RS'){
				$dailyReport->updateRevenue();
				$dailyReport->setNewFields();
				$return.= ' -Yes Revenue- ';
			}else{
				$return.= ' -Not Revenue- ';
			}
			
			if ( !$dailyReport->save() ) {
				Yii::log("Can't save campaign: '" . $campaign->campaigNname . "message error: " . json_encode($dailyReport->getErrors()), 'error', 'system.model.api.startapp');
			}else{
				$updated++;
				$return.='<br/>===> saved';
			}

			// $return .= json_encode($campaign);
			// $return .= '<br/>';
		}

		$this->apiLog->updateLog('Completed', 'Procces completed: '.$updated.' campaigns updated');

		Yii::log("SUCCESS - Daily info downloaded", 'info', 'system.model.api.startapp');
		return $return;
	}

}