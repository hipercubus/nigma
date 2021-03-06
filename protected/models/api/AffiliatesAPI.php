<?php

class AffiliatesAPI
{ 
	private $apiLog;
 
	public function downloadInfo($offset)
	{
		date_default_timezone_set('UTC');
		$return = '';
		$fixedRate = isset($_GET['rate']) ? $_GET['rate'] : null;
		$fixedCid  = isset($_GET['cid']) ? $_GET['cid'] : null;

		if ( isset( $_GET['date']) ) {
		
			$date = $_GET['date'];
			$return.= $this->downloadDateInfo($date, $fixedRate, $fixedCid);
		
		} else {

			if(date('G')<=$offset){
				$return.= '<hr/>yesterday<hr/>';
				$date = date('Y-m-d', strtotime('yesterday'));
				$return.= $this->downloadDateInfo($date);
			}
			//default
			$return.= '<hr/>today<hr/>';
			$date = date('Y-m-d', strtotime('today'));
			$return.= $this->downloadDateInfo($date);
		
		}

		return $return;
	}


	public function downloadDateInfo($date, $fixedRate=null, $fixedCid=null)
	{
		$return = '';
		$testSource = 29;
		$clicksLogCriteria = new CDbCriteria;

		$clicksLogCriteria->with = array(
			'campaigns', 
			'providers',
			'campaigns.opportunities',
			'vectorsLog',
			'vectorsLog.vectors', 
			'convLogs',
		);

		$clicksLogCriteria->group = 't.campaigns_id, t.providers_id, vectorsLog.vectors_id';

		$clicksLogCriteria->select = array( 
			't.campaigns_id AS campaign',			
			't.providers_id AS provider', 
			'campaigns.name AS campaigns_name',
			'providers.name AS provider_name', 
			'count(t.id) AS clicks', 
			'count(convLogs.id) AS conversions', 
			'opportunities.model_adv AS model_adv',
			'vectorsLog.vectors_id AS vectors_id',
			'vectors.name AS vector_name',
			'vectors.rate AS vector_rate'
		);

		$clicksLogCriteria->compare('DATE(t.date)',$date);
		$clicksLogCriteria->compare('t.providers_id','<>'.$testSource);
		$clicksLogCriteria->addCondition('t.providers_id IS NOT NULL');
		$clicksLogCriteria->compare('providers.type','Affiliate');
		if(isset($fixedCid))			
			$clicksLogCriteria->compare( 'campaigns.id', $fixedCid );

		$clicksLogs = ClicksLog::model()->findAll( $clicksLogCriteria );

		$updated = 0;
		$prevProvider = false;
		$prevProviderName = false;

		if ( !$clicksLogs )
		{
			return 'No se encontraron click logs.';
		}

		foreach ( $clicksLogs as $clicksLog )
		{

			if ( $prevProvider && $prevProvider!=$clicksLog->provider )
			{
				Yii::log("SUCCESS - Daily info downloaded", 'info', 'system.model.api.affiliate.' . $prevProviderName);
				$this->apiLog->updateLog('Completed', 'Procces completed: '.$updated.' campaigns updated');				
			}

			if ( !$prevProvider || $clicksLog->provider!=$prevProvider )
			{
				$this->apiLog = ApiLog::initLog($date, $clicksLog->provider, null);	
				$this->apiLog->updateLog('Processing', 'Calculating traffic data');
			}

			$dailyRepCriteria = new CDbCriteria;

			$dailyRepCriteria->with = array(
				'dailyReportVectors',
			);

			$dailyRepCriteria->select = array( 
				'*',
				'dailyReportVectors.id AS daily_report_vector',
			);

			$dailyRepCriteria->compare( 'DATE(date)', $date);
			$dailyRepCriteria->compare( 'dailyReportVectors.vectors_id', $clicksLog->vectors_id );
			$dailyRepCriteria->compare( 'providers_id', $clicksLog->provider );
			$dailyRepCriteria->compare( 'campaigns_id', $clicksLog->campaign );

			$dailyReport = DailyReport::model()->find( $dailyRepCriteria );

			// if exists overwrite, else create a new
			if(!$dailyReport)
				$dailyReport = new DailyReport();

			$dailyReport->date         = $date;
			$dailyReport->campaigns_id = $clicksLog->campaign;
			$dailyReport->providers_id = $clicksLog->provider;
			$dailyReport->imp          = 0;
			$dailyReport->clics        = $clicksLog->clicks;
			$dailyReport->conv_api     = $clicksLog->conversions;

			$return .= 'Campaign: '.$clicksLog->campaigns_name.'<br/>Provider: '.$clicksLog->provider_name.'<br/>Vector: '.$clicksLog->vectors_id.'<br/>';

			// spend

			if ( !$fixedRate && isset($clicksLog->vector_rate) )
				$fixedRate = $clicksLog->vector_rate;

			$dailyReport->updateSpendAffiliates($fixedRate);

			// revenue

			if( $clicksLog->model_adv != 'RS' ){ // Esto esta porque el revenue share se ingresa manualmente

				$dailyReport->updateRevenue();
				$dailyReport->setNewFields();
				$return.= ' -Yes Revenue- ';
			}else{
				$return.= ' -Not Revenue- ';
			}

			$return.= $clicksLog->provider.'::'.$clicksLog->campaign .' - '.$clicksLog->clicks.' - '.$clicksLog->conversions.' - '.$dailyReport->spend.'<br/><br/>';	


			if ( !$dailyReport->save() ) {
				$return.="Can't save campaign: '" . $clicksLog->campaigns_name . "message error: " . json_encode($dailyReport->getErrors());
				Yii::log("Can't save campaign: '" . $clicksLog->campaigns_name . "message error: " . json_encode($dailyReport->getErrors()), 'error', 'system.model.api.affiliate.' . $clicksLog->provider_name);
				continue;
			}


			if ( isset($clicksLog->vectors_id) )
			{		
				if ( $dailyReport->daily_report_vector )
				{
					$repVectorCriteria = new CDbCriteria;
					$repVectorCriteria->compare( 'id', $dailyReport->daily_report_vector );
					$dailyReportVector = DailyReportVectors::model()->find( $repVectorCriteria );				
				}
				else
				{
					$dailyReportVector = new DailyReportVectors();	
					$dailyReportVector->id = null;
				}

				$dailyReportVector->vectors_id = $clicksLog->vectors_id;
				$dailyReportVector->daily_report_id = $dailyReport->id;

				if ( !$dailyReportVector->save() ) {
					$return.="Can't save vector: ".$clicksLog->vectors_id." for campaign: '" . $clicksLog->campaigns_name . "message error: " . json_encode($dailyReportVector->getErrors());
					Yii::log("Can't save vector for campaign: '" . $clicksLog->campaigns_name . "message error: " . json_encode($dailyReportVector->getErrors()), 'error', 'system.model.api.affiliate.' . $clicksLog->provider_name);
					continue;
				}
			}


			$this->apiLog->updateLog('Completed', 'Procces completed: '.$updated.' campaigns updated');
			
			$updated++;
			
			$prevProvider = $clicksLog->provider;
			$prevProviderName = $clicksLog->provider_name;
		}
		Yii::log("SUCCESS - Daily info downloaded", 'info', 'system.model.api.affiliate.' . $prevProviderName);
		
		$this->apiLog->updateLog('Completed', 'Procces completed: '.$updated.' campaigns updated');	

		$return.="SUCCESS - Daily info downloaded for all affiliates<br/>";
		Yii::log("SUCCESS - Daily info downloaded for all affiliates", 'info', 'system.model.api.affiliate');

		return $return;
	}

}