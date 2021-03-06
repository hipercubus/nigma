<?php

class LeadBolt
{ 

	private $provider_id = 6;

	public function downloadInfo()
	{
		if ( isset( $_GET['date']) ) {
			$date = $_GET['date'];
		} else {
			$date = date('Y-m-d', strtotime('yesterday'));
		}
		$dateOrig = $date;

		// validate if info have't been dowloaded already.
		if ( DailyReport::model()->exists("providers_id=:provider AND DATE(date)=:date", array(":provider"=>$this->provider_id, ":date"=>$date)) ) {
			Yii::log("Information already downloaded.", 'warning', 'system.model.api.leadBolt');
			return 2;
		}

		$date = str_replace('-', '', $date); // Leadbolt api use YYYYMMDD date format
		
		// Get json from Loadbolt API.
		$network = Networks::model()->findbyPk($this->provider_id);
		$advertiserid = $network->token1;
		$secretkey	= $network->token2;
		$apiurl = $network->url;
		$url = $apiurl . "?advertiser_id=" . $advertiserid . "&secret_key=" . $secretkey . "&format=json" . "&date_from=" . $date . "&date_to=" . $date;

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		$result = json_decode($result);
		if (!$result) {
			Yii::log("Decoding json.", 'error', 'system.model.api.leadBolt');
			return 1;
		}
		curl_close($curl);

		foreach ($result[0]->data as $campaign) {

			if ( $campaign->impressions == 0 && $campaign->clicks == 0) { // if no impressions dismiss campaign
				continue;
			}
			
			$dailyReport = new DailyReport();
			
			// get campaign ID used in Server, from the campaign name use in the external provider
			$dailyReport->campaigns_id = Utilities::parseCampaignID($campaign->campaign_name);

			if ( !$dailyReport->campaigns_id ) {
				Yii::log("Invalid external campaign name: '" . $campaign->campaign_name, 'warning', 'system.model.api.leadBolt');
				continue;
			}

			$dailyReport->date = $dateOrig;
			$dailyReport->providers_id = $this->provider_id;
			$dailyReport->imp = $campaign->impressions;
			$dailyReport->clics = $campaign->clicks;
			$dailyReport->conv_api = ConvLog::model()->count("campaigns_id=:campaignid AND DATE(date)=:date", array(":campaignid"=>$dailyReport->campaigns_id, ":date"=>$date));
			//$dailyReport->conv_adv = 0;
			$dailyReport->spend = $campaign->spend;
			$dailyReport->updateRevenue();
			$dailyReport->setNewFields();
			if ( !$dailyReport->save() ) {
				Yii::log("Can't save campaign: '" . $campaign->campaign_name . "message error: " . json_encode($dailyReport->getErrors()), 'error', 'system.model.api.leadBolt');
				continue;
			}
		}
		Yii::log("SUCCESS - Daily info downloaded", 'info', 'system.model.api.leadBolt');
		return 0;
	}

}