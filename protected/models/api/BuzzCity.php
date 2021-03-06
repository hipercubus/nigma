<?php

class BuzzCity
{ 

	private $providers_ids = array(7, 8, 9, 10);

	public function downloadInfo()
	{
		if ( isset( $_GET['date']) ) {
			$date = $_GET['date'];
		} else {
			$date = date('Y-m-d', strtotime('yesterday'));
		}

		foreach ($this->providers_ids as $provider_id) {

			// validate if info have't been dowloaded already.
			if ( DailyReport::model()->exists("providers_id=:providers AND DATE(date)=:date", array(":providers"=>$provider_id, ":date"=>$date)) ) {
				Yii::log("Information already downloaded.", 'warning', 'system.model.api.buzzCity');
				continue;
			}

			// Get json from BuzzCity API.
			$network   = Networks::model()->findbyPk($provider_id);
			$partnerid = $network->token1;
			$hash      = $network->token2;
			$apiurl    = $network->url;
			$url       = $apiurl . "?partnerid=" . $partnerid . "&hash=" . $hash . "&reporttype=campaign&datefrom=" . $date . "&dateto=" . $date . "&fmt=json&consolidated=1";

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($curl);
			$result = json_decode($result);
			if (!$result) {
				Yii::log("Decoding json.", 'error', 'system.model.api.buzzCity');
				continue;
			}
			curl_close($curl);

			if (empty($result->data)) {
				Yii::log("Empty daily report.", 'info', 'system.model.api.buzzCity');
				continue;	
			}
			
			// Save campaigns information 
			foreach ($result->data as $campaign) {

				if ( $campaign->exposures == 0 && $campaign->clicks == 0) { // if no impressions dismiss campaign
					continue;
				}
				
				$dailyReport = new DailyReport();
				
				// get campaign ID used in Server, from the campaign name use in the external network
				$dailyReport->campaigns_id = Utilities::parseCampaignID($campaign->title);

				if ( !$dailyReport->campaigns_id ) {
					Yii::log("Invalid external campaign name: '" . $campaign->title, 'warning', 'system.model.api.buzzCity');
					continue;
				}

				$dailyReport->date = $date;
				$dailyReport->providers_id = $provider_id;
				$dailyReport->imp = $campaign->exposures;
				$dailyReport->clics = $campaign->clicks;
				$dailyReport->conv_api = ConvLog::model()->count("campaigns_id=:campaignid AND DATE(date)=:date", array(":campaignid"=>$dailyReport->campaigns_id, ":date"=>$date));
				//$dailyReport->conv_adv = 0;
				$dailyReport->spend = $campaign->spending;
				$dailyReport->updateRevenue();
				$dailyReport->setNewFields();
				if ( !$dailyReport->save() ) {
					Yii::log("Can't save campaign: '" . $campaign->title . "message error: " . json_encode($dailyReport->getErrors()), 'error', 'system.model.api.buzzCity');
					continue;
				}
			}
		}
		Yii::log("SUCCESS - Daily info downloaded", 'info', 'system.model.api.buzzCity');
		return 0;
	}

}