<?php

/**
 * This is the model class for table "campaigns".
 *
 * The followings are the available columns in table 'campaigns':
 * @property integer $id
 * @property string $name
 * @property integer $providers_id
 * @property integer $campaign_categories_id
 * @property integer $wifi
 * @property integer $formats_id
 * @property string $cap
 * @property string $model
 * @property integer $ip
 * @property integer $devices_id
 * @property string $url
 * @property string $status
 * @property integer $opportunities_id
 * @property boolean $post_data
 * @property string $external_rate
 *
 * The followings are the available model relations:
 * @property Providers $providers
 * @property Devices $devices
 * @property Formats $formats
 * @property CampaignCategories $campaignCategories
 * @property Opportunities $opportunities
 * @property ConvLog[] $convLogs
 * @property DailyReport[] $dailyReports
 * @property DailyVectors[] $dailyVectors
 * @property Vectors[] $vectors
 */
class Campaigns extends CActiveRecord
{
	/**
	 * Related columns availables in search rules
	 * @var [type] advertisers_name
	 * @var [type] opportunities_rate
	 * @var [type] opportunities_carrier
	 */
	public $advertisers_name;
	public $opportunities_rate;
	public $opportunities_carrier;
	public $financeEntities_name;
	public $vectors_id;
	public $net_currency;
	public $clicks;
	public $conv;
	public $account_manager;
	public $rate;
	public $revenue;
	public $profit;
	public $spend;
	public $profit_percent;
	public $format;
	public $clics_redirect;
	public $date;
	public $cp_status;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'campaigns';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, providers_id, campaign_categories_id, wifi, formats_id, cap, model, devices_id, url, opportunities_id', 'required'),
			array('providers_id, campaign_categories_id, wifi, formats_id, ip, post_data, editable, devices_id, opportunities_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>128),
			// array('comment', 'length', 'max'=>512),
			array('comment, status, environment, flow, inventory_type', 'safe'),
			array('cap, external_rate', 'length', 'max'=>11),
			array('model', 'length', 'max'=>3),
			array('url', 'length', 'max'=>512),
			array('url', 'url'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id,account_manager, name, advertisers_name, financeEntities_name, opportunities_rate, opportunities_carrie, providers_id, campaign_categories_id, wifi, formats_id, cap, model, ip, devices_id, url, status, opportunities_id, net_currency, external_rate, comment, environment, flow, inventory_type, cp_status, editable', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'providers'           => array(self::BELONGS_TO, 'Providers', 'providers_id'),
			'devices'            => array(self::BELONGS_TO, 'Devices', 'devices_id'),
			'formats'            => array(self::BELONGS_TO, 'Formats', 'formats_id'),
			'campaignCategories' => array(self::BELONGS_TO, 'CampaignCategories', 'campaign_categories_id'),
			'opportunities'      => array(self::BELONGS_TO, 'Opportunities', 'opportunities_id'),
			'bannerSizes'        => array(self::BELONGS_TO, 'BannerSizes', 'banner_sizes_id'),
			'convLogs'           => array(self::HAS_MANY, 'ConvLog', 'campaigns_id'),
			'clicksLogs'         => array(self::HAS_MANY, 'ClicksLog', 'campaigns_id'),
			'dailyReports'       => array(self::HAS_MANY, 'DailyReport', 'campaigns_id'),
			'dailyVectors'       => array(self::HAS_MANY, 'DailyVectors', 'campaigns_id'),
			'vectors'            => array(self::MANY_MANY, 'Vectors', 'vectors_has_campaigns(campaigns_id, vectors_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'name'                   => 'Name',
			'providers_id'           => 'Traffic Source',
			'campaign_categories_id' => 'Campaign Categories',
			'wifi'                   => 'Wifi',
			'formats_id'             => 'Formats',
			'cap'                    => 'Cap',
			'model'                  => 'Model',
			'ip'                     => 'Ip',
			'devices_id'             => 'Devices',
			'url'                    => 'Url',
			'status'                 => 'Status',
			'opportunities_id'       => 'Opportunities',
			'comment'				 => 'Comment',
			'financeEntities_name'   => 'Finance Entity',
			'environment'			 => 'Buying Environment',
			'cp_status'				 => "Status",
			// Header names for the related columns
			'advertisers_name'       => 'Advertiser', 
			'opportunities_rate'     => 'Rate', 
			'opportunities_carrier'  => 'Carrier',
			'post_data'              => 'Post Data',
			'editable'               => 'Editable Report',
			'banner_sizes_id'        => 'Banner Sizes',
			'net_currency'           => 'T. Source Currency',
			'clicks'                 => 'Clicks Log',
			'conv'                   => 'Conv. Log',
			'account_manager'        => 'Account Manager',
			'rate'                   => 'Rate',
			'revenue'                => 'Revenue',
			'profit'                 => 'Profit',
			'spend'                  => 'Spend',
			'profit_percent'         => 'Profit %',
			'format'                 => 'Format',
			'clics_redirect'         => 'Clics Redirect',
			'date'                   => 'Date',
			'external_rate'          => 'External rate',
			'flow'					 => 'Flow (only VAS)',
			'inventory_type'		 => 'Inventory Type',
		);
	}

	public function behaviors()
	{
		return array(
	    		'modelVersioning' => array(
	          		'class' => 'SAModelVersioning',
	       		)
	    	);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($accountManager, $opportunitie, $providers, $advertiser)
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
		$criteria->compare('t.id',$this->id);		
		$criteria->compare('campaign_categories_id',$this->campaign_categories_id);
		$criteria->compare('t.wifi',$this->wifi);
		$criteria->compare('formats_id',$this->formats_id);
		$criteria->compare('cap',$this->cap,true);
		$criteria->compare('t.model',$this->model,true);
		$criteria->compare('ip',$this->ip);
		$criteria->compare('devices_id',$this->devices_id);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('t.status',$this->status);
		$criteria->compare('post_data',$this->post_data);
		$criteria->compare('editable',$this->editable);
		$criteria->compare('banner_sizes_id',$this->banner_sizes_id);
		$criteria->compare('t.status',$this->cp_status);
		$criteria->compare('t.flow',$this->flow);
		$criteria->compare('t.inventory_type',$this->inventory_type);

		//We need to list all related tables in with property
		$criteria->with = array(
				'opportunities',
				'opportunities.accountManager', 
				'opportunities.regions', 
				'opportunities.regions.financeEntities', 
				'opportunities.regions.financeEntities.advertisers', 
				'opportunities.regions.country', 
				'opportunities.carriers', 
				'vectors', 
				'providers',
				'providers.affiliates'
			);
		// Related search criteria items added (use only table.columnName)
		$criteria->compare('advertisers.name',$this->advertisers_name, true);
		$criteria->compare('opportunities.rate',$this->opportunities_rate, true);
		$criteria->compare('opportunities.carrier',$this->opportunities_carrier, true);
		$criteria->compare('accountManager.name',$this->account_manager, true);
		$criteria->compare('accountManager.lastname',$this->account_manager, true, 'OR');
		
		if ($accountManager != NULL)
			$criteria->compare('accountManager.id',$accountManager);
		$criteria->compare('opportunities_id',$opportunitie);
		$criteria->compare('t.providers_id',$providers);
		$criteria->compare('advertisers.cat',$advertiser);

		//nomenclatura
		$tmp = new CDbCriteria;
		$tmp->compare('t.id',$this->name,true);
		$tmp->compare('country.ISO2',$this->name,true,'OR');
		$tmp->compare('t.name',$this->name,true,'OR');
		$tmp->compare('carriers.mobile_brand',$this->name,true,'OR');
		$tmp->compare('advertisers.prefix',$this->name,true,'OR');
		$tmp->compare('opportunities.product',$this->name,true,'OR');
		$criteria->mergeWith($tmp);

		$roles = array_keys(Yii::app()->authManager->getRoles(Yii::app()->user->id));
		if ( in_array('commercial', $roles, true) )
			FilterManager::model()->addUserFilter($criteria, 'daily.commercial');
		else if (in_array('affiliates_manager', $roles, true))
			$criteria->addCondition('affiliates.providers_id IS NOT NULL');
		else
			FilterManager::model()->addUserFilter($criteria, 'daily');

		if( UserManager::model()->isUserAssignToRole('account_manager_admin') || UserManager::model()->isUserAssignToRole('account_manager_admin') )
			$criteria->compare('advertisers.cat', array('VAS','Affiliates','App Owners'));

		if( UserManager::model()->isUserAssignToRole('operation_manager') )
			$criteria->compare('advertisers.cat', array('Networks','Incent'));		

		$criteria->compare('financeEntities.name',$this->financeEntities_name, true);
		$criteria->compare('LOWER(providers.currency)', strtolower($this->net_currency), true);
		// $criteria->compare('vectors_has_campaigns.vectors',$this->vectors_id, true);

		return new CActiveDataProvider($this, array(
			'criteria' =>$criteria,
			// Setting 'sort' property in order to add 
			// a sort tool in the related collumns
			'pagination'=> KHtml::pagination(),
			'sort'     =>array(
		        'attributes'=>array(
					// Adding custom sort attributes
		            'advertisers_name'=>array(
						'asc'  =>'advertisers.name',
						'desc' =>'advertisers.name DESC',
		            ),
		            'account_manager'=>array(
						'asc'  =>'accountManager.name',
						'desc' =>'accountManager.name DESC',
		            ),
		            'opportunities_rate'=>array(
						'asc'  =>'opportunities.rate',
						'desc' =>'opportunities.rate DESC',
		            ),
		            'opportunities_carrier'=>array(
						'asc'  =>'opportunities.carrier',
						'desc' =>'opportunities.carrier DESC',
		            ),
		            'financeEntities_name'=>array(
						'asc'  =>'financeEntities.id',
						'desc' =>'financeEntities.id DESC',
		            ),
		            'net_currency'=>array(
						'asc'  =>'providers.currency',
						'desc' =>'providers.currency DESC',
		            ),
		            'cp_status'=>array(
						'asc'  =>'status',
						'desc' =>'status DESC',
		            ),
		            // Adding all the other default attributes
		            '*',
		        ),
		    ),
		));
	}

	/**
	 * Retrieves a list of models for specified provider and date. Ignore the campaigns that had already info entry.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function searchByProviderAndDate($provider_id, $date)
	{
		$criteria=new CDbCriteria;

		if ( $provider_id ) {
			$criteria->compare('providers_id', $provider_id);
			$criteria->addCondition("t.id NOT IN (SELECT d.campaigns_id FROM daily_report d WHERE d.providers_id=". $provider_id . " AND d.date='". date('Y-m-d', strtotime($date)) . "')");
		} else {
			$criteria->compare('providers_id', -1); // Select none
		}

		$criteria->with = array('opportunities', 'opportunities.country', 'opportunities.regions.financeEntities.advertisers');
		
		// external name
		$criteria->compare('t.id', $this->name, true);
		$criteria->compare('t.status', 'Active');
		$criteria->compare('country.ISO2', $this->name, true, 'OR');
		$criteria->compare('t.name', $this->name, true, 'OR');

		FilterManager::model()->addUserFilter($criteria, 'campaign.account');

		if( UserManager::model()->isUserAssignToRole('account_manager_admin') || UserManager::model()->isUserAssignToRole('account_manager_admin') )
			$criteria->compare('advertisers.cat', array('VAS','Affiliates','App Owners'));

		if( UserManager::model()->isUserAssignToRole('operation_manager') )
			$criteria->compare('advertisers.cat', array('Networks','Incent'));		

		return new CActiveDataProvider($this, array(
			'criteria'   => $criteria,
			// Setting 'sort' property in order to add 
			// a sort tool in the related collumns
			'pagination' => false,
			'sort'       => array(
		        'attributes'=>array(
		        	'name'=>array(
		        		'asc'  =>'t.id',
						'desc' =>'t.id DESC',
		        	),
		        	// Adding all the other default attributes
		            '*',
		        ),
		    ),
		));
	}

	public function searchByVectors($vector_id){

	    $criteria = new CDbCriteria;
	    $criteria->with = array('vectors');
		$criteria->together = true;
	    $criteria->compare('vectors.id', $vector_id);

  		FilterManager::model()->addUserFilter($criteria, 'campaign.account');

		return new CActiveDataProvider($this, array(
			'criteria'   =>$criteria,
			'pagination' =>false,
			)
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Campaigns the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function getExternalName($id=NULL, $isAffiliate=false)
	{
		$model = Campaigns::model()->with(
				'opportunities', 
				'opportunities.regions', 
				'opportunities.regions.financeEntities', 
				'opportunities.regions.financeEntities.advertisers', 
				'opportunities.regions.country', 
				'opportunities.carriers', 
				'providers', 
				'devices', 
				'formats'
			)->findByPk($id);

		$opportunity = $model->opportunities;
		$adv = $model->opportunities->regions->financeEntities->advertisers->prefix;

		$country = '';
		if ( $opportunity->regions->country_id !== NULL )
			$country = '-' . $model->opportunities->regions->country->ISO2;

		$carrier = '-MUL';
		if ( $opportunity->carriers_id !== NULL )
			$carrier = '-' . substr( $model->opportunities->carriers->mobile_brand , 0 , 3);

		$wifi_ip = $model->wifi ? '-WIFI' : '';
		$wifi_ip .= $model->ip ? '-IP' : '';
		
		$device = '';
		if ( $model->devices_id !== NULL )
			$device = '-' . $model->devices->prefix;

		$providers = '';
		if ( $model->providers_id !== NULL )
			$providers = '-' . $model->providers->prefix;
		
		$product = $opportunity->product ? '-' . $opportunity->product : '';

		$format = '';
		if ( $model->formats_id !== NULL )
			$format = '-' . $model->formats->prefix;
		
		$alternativeConventionName = '';
		if ( $model->providers->isNetwork() && $model->providers->networks->use_alternative_convention_name )
			$alternativeConventionName = '-' . $model->id;

		// *CID* ADV(5) COUNTRY(2) CARRIER(3) [WIFI-IP] DEVICE(1) NET(2) [PROD] FORM(3) NAME
		if($isAffiliate)
			return $model->id . '-' . $model->name . $alternativeConventionName;
		else
			return $model->id . '-' . $adv . $country . $carrier . $wifi_ip . $device . $providers . $product . $format . '-' . $model->name . $alternativeConventionName;
	}
	
	public function excel($startDate=NULL, $endDate=NULL, $id=null, $opp=NULL)
	{
		$criteria=new CDbCriteria;
		$criteria->with=array('clicksLog', 'clicksLog.providers', 'campaign');
		$criteria->compare('campaign.opportunities_id', $opp);
		if($id) $criteria->addCondition('t.campaigns_id='.$id);
		$criteria->addCondition("DATE(t.date)>='".date('Y-m-d', strtotime($startDate))."'");
		$criteria->addCondition("DATE(t.date)<='".date('Y-m-d', strtotime($endDate))."'");
		//$criteria->addCondition('t.clicks_log_id=clicksLog.id');
		$modelc=new ConvLog;
		return new CActiveDataProvider($modelc, array(
			'criteria'=>$criteria,
		));
	}
	
	function arrayCharts($dateStart, $dateEnd) {
	    $range = array();

	    if (is_string($dateStart) === true) $dateStart = strtotime($dateStart);
	    if (is_string($dateEnd) === true ) $dateEnd = strtotime($dateEnd);

	    if ($dateStart > $dateEnd) $range=createDateRangeArray($dateEnd, $dateStart);

	    do {
	        $range[] = date('Y-m-d', $dateStart);
	        $dateStart = strtotime("+ 1 day", $dateStart);
	    } while($dateStart <= $dateEnd);
	    $charts=array();
	    foreach ($range as $date) {
	    	$charts[]=array(
	    		'date'=>$date,
	    		'clicks'=>$this->countClicks($date,$date),
	    		'convs'=>$this->countConv($date,$date),
	    		);
	    	echo self::countClicks('2014-09-15','2014-09-17').'<br>';
	    }
	    return $charts;
	    
	}
	public function getTotals($startDate=NULL, $endDate=NULL,$campaign=NULL,$accountManager=NULL,$opportunitie=null,$providers=null)
	{
		if(!$startDate)	$startDate = 'today' ;
		if(!$endDate) $endDate   = 'today';
		$startDate = date('Y-m-d', strtotime($startDate));
		$endDate = date('Y-m-d', strtotime($endDate));
		$dataTops=array();
		$conversions=array();
		$clics=array();
		$dates=array();
		foreach (Utilities::dateRange($startDate,$endDate) as $date) {
			$dataTops[$date]['conversions']     =0;
			$dataTops[$date]['clics']           =0;
			$dataTops[$date]['clics_redirect']  =0;
			$dataTops[$date]['conversions_s2s'] =0;
			$dataTops[$date]['revenue']         =0;
		}

		$criteria=new CDbCriteria;
		//$criteria->with('opportunities','providers');
		$criteria->with = array('opportunities','opportunities.accountManager','providers');
		if($campaign!=null)$criteria->compare('t.id',$campaign);
		else
		{
			if($accountManager!=null)$criteria->compare('opportunities.account_manager_id',$accountManager);
			if($opportunitie!=null)$criteria->compare('opportunities.id',$opportunitie);
			if($providers!=null)$criteria->compare('t.providers_id',$providers);
		}
		$campaigns=self::model()->findAll($criteria);
		foreach ($campaigns as $campaign) 
		{
			foreach (Utilities::dateRange($startDate,$endDate) as $date) 
			{
				// echo $campaign->id."<br>";
				// echo $date."<br>";
				//echo ConvLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign->id,":date"=>$date))."<br>";
				$dataTops[$date]['conversions']    +=intval(ConvLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign->id,":date"=>$date)));
				$dataTops[$date]['clics']          +=intval(ClicksLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign->id,":date"=>$date)));
				$dataTops[$date]['clics_redirect'] +=intval(ClicksLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign->id,":date"=>$date)));
				 
				$conv                               = intval(ConvLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign->id,":date"=>$date)));
				$dataTops[$date]['conversions_s2s'] += $conv;
				$dataTops[$date]['revenue']         += $conv * $campaign->opportunities->rate;
			}
		}

		foreach ($dataTops as $date => $data) {
			$conversions[]     =$data['conversions'];
			$clics[]           =$data['clics'];
			$clics_redirect[]  =$data['clics_redirect'];
			$conversions_s2s[] =$data['conversions_s2s'];
			$revenue[]         =$data['revenue'];
			$dates[]           =$date;
		}

		$result=array(
			'conversions'     => $conversions, 
			'clics'           => $clics, 
			'dates'           => $dates, 
			'clics_redirect'  => $clics_redirect, 
			'conversions_s2s' => $conversions_s2s,
			'revenue'         => $revenue,
			);
		
		return $result;
	}
	public function totalsTraffic($startDate=NULL, $endDate=NULL, $campaign=NULL)
	{
		if(!$startDate)	$startDate = 'today' ;
		if(!$endDate) $endDate   = 'today';
		$startDate = date('Y-m-d', strtotime($startDate));
		$endDate = date('Y-m-d', strtotime($endDate));
		$dataTops=array();
		$conversions=array();
		$clics=array();
		$dates=array();

		foreach (Utilities::dateRange($startDate,$endDate) as $date) {
			$dataTops[$date]['conversions']=0;
			$dataTops[$date]['clics']=0;
		}

		$criteria=new CDbCriteria;
		$criteria->select='count(*) as clics,DATE(date) as date';
		$criteria->addCondition("DATE(date)>="."'".$startDate."'");
		$criteria->addCondition("DATE(date)<="."'".$endDate."'");
		if($campaign!=NULL)$criteria->addCondition("campaigns_id=".$campaign);
		$criteria->group='DATE(date)';
		$criteria->order='DATE(date) ASC';
		$r         = ClicksLog::model()->findAll( $criteria );

		foreach ($r as $value) {			
			$dataTops[date('Y-m-d', strtotime($value->date))]['clics']=intval($value->clics);
			if($campaign==NULL)$dataTops[date('Y-m-d', strtotime($value->date))]['conversions']=intval(ConvLog::model()->count("DATE(date)=:date", array(":date"=>date('Y-m-d', strtotime($value->date)))));
			if($campaign!=NULL)$dataTops[date('Y-m-d', strtotime($value->date))]['conversions']=intval(ConvLog::model()->count("DATE(date)=:date AND campaigns_id=:campaign", array(":campaign"=>$campaign,":date"=>date('Y-m-d', strtotime($value->date)))));
		}
		
		foreach ($dataTops as $date => $data) {
			$conversions[] = $data['conversions'];
			$clics[]       = $data['clics'];
			$dates[]       = $date;
		}


		$result = array(
			'conversions' => $conversions, 
			'clics'       => $clics, 
			'dates'       => $dates, 
			);
		
		return $result;
	}

	public function searchTraffic($accountManager=NULL,$opportunitie=null,$providers=null,$dateStart='today',$dateEnd='today')
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
		$criteria->with = array(
			'opportunities.regions', 
			'opportunities.regions.financeEntities', 
			'opportunities.regions.financeEntities.advertisers', 
			'opportunities.country', 
			'opportunities.carriers'
			);
		
		// external name
		$tmp = new CDbCriteria;
		$tmp->compare('t.id',$this->name,true);
		$tmp->compare('country.ISO2',$this->name,true,'OR');
		$tmp->compare('t.name',$this->name,true,'OR');
		$tmp->compare('carriers.mobile_brand',$this->name,true,'OR');
		$tmp->compare('advertisers.prefix',$this->name,true,'OR');
		$tmp->compare('opportunities.product',$this->name,true,'OR');
		$criteria->mergeWith($tmp);

		$criteria->compare('advertisers.name',$this->advertisers_name, true);
		$criteria->compare('financeEntities.name',$this->financeEntities_name, true);
		$criteria->compare('opportunities.rate',$this->opportunities_rate, true);
		$criteria->compare('opportunities.carrier',$this->opportunities_carrier, true);
		if($accountManager!=null)$criteria->compare('opportunities.account_manager_id',$accountManager);
		if($opportunitie!=null)$criteria->compare('opportunities.id',$opportunitie);
		if($providers!=null)$criteria->compare('t.providers_id',$providers);

		$dateStart = date('Y-m-d', strtotime($dateStart)); 
		$dateEnd   = date('Y-m-d', strtotime($dateEnd));

		// custom subselect columns
		$countClicks = "(SELECT count(cl.id) FROM clicks_log cl WHERE cl.campaigns_id = t.id AND DATE(cl.date)>='" . $dateStart . "' AND DATE(cl.date)<='" . $dateEnd . "')";
		$countConv = "(SELECT count(cv.id) FROM conv_log cv WHERE cv.campaigns_id = t.id AND DATE(cv.date)>='" . $dateStart . "' AND DATE(cv.date)<='" . $dateEnd . "')";

		$criteria->select = array(
			'*',
			$countClicks . " as clicks",
			$countConv . " as conv",
		);

		$criteria->having = 'clicks != 0';

		return new CActiveDataProvider($this, array(
			'criteria' =>$criteria,
			// Setting 'sort' property in order to add 
			// a sort tool in the related collumns
			'pagination'=> KHtml::pagination(),
			'sort'     =>array(
		        'attributes'=>array(
					// Adding custom sort attributes
		            'campaigns_name'=>array(
						'asc'  =>'campaigns.name',
						'desc' =>'campaigns.name DESC',
		            ),
		            'advertisers_name'=>array(
						'asc'  =>'advertisers.name',
						'desc' =>'advertisers.name DESC',
		            ),
		            'financeEntities_name'=>array(
						'asc'  =>'financeEntities.name',
						'desc' =>'financeEntities.name DESC',
		            ),
		            'clicks'=>array(
						'asc'  =>'clicks',
						'desc' =>'clicks DESC',
		            ),
		            'conv'=>array(
						'asc'  =>'conv',
						'desc' =>'conv DESC',
		            ),
		            // Adding all the other default attributes
		            '*',
		        ),
		    ),
		));
	}

	public function isValidId($id)
	{
		$isValid = $this->find('id=:id', array(':id' => $id));
		return $isValid ? true : false;
	}

	public function getRateUSD($date)
	{
		$opportunity =Opportunities::model()->with('regions','regions.financeEntities')->findByPk($this->opportunities_id);
		$rate        =$opportunity->rate;
		$finance_entitie_currency =$opportunity->regions->financeEntities->currency;

		if ($finance_entitie_currency == 'USD') // if currency is USD dont apply type change
			return $rate;

		$currency = Currency::model()->findByDate($date);
		return $currency ? number_format($rate / $currency[$finance_entitie_currency], 2) : 'Currency ERROR!';
	}

	public function getGeoClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		$data['array']        =array();
		$data['dataprovider'] =array();
		$dateStart            =date('Y-m-d', strtotime($dateStart));
		$dateEnd              =date('Y-m-d', strtotime($dateEnd));

		$criteria             =new CDbCriteria;
		$criteria->join = 'LEFT JOIN conv_log c ON c.clicks_log_id = t.id';
		$criteria->select     =array('count(*) as clics', 'count(c.id) as conv', 'country');
	    
	    if($campaign!=null)
	    	$criteria->addCondition("DATE(t.date)>='".$dateStart."' AND DATE(t.date)<='".$dateEnd."' AND t.campaigns_id=".$campaign);
	    else
	    	$criteria->addCondition("DATE(t.date)>='".$dateStart."' AND DATE(t.date)<='".$dateEnd."'");
		
		$criteria->group ='country';
		$criteria->order ='clics DESC';
	    
	    $clicksLogs = ClicksLog::model()->findAll($criteria);
	    $data['dataprovider']= new CActiveDataProvider(ClicksLog::model(), array(
				'criteria'   =>$criteria,
				'pagination' =>false,
				'sort'       =>array(
					'attributes'   =>array(
			            // Adding all the other default attributes
			            '*',
			        ),
			    ),

			));
	    foreach ($clicksLogs as $log) 
	    {
	    	if(strlen($log->country)==2)
		        $data['array'][] = array(
		        	'hc-key' => strtolower($log->country), 
		        	'value' => intval($log->clics)
		        	);
    	}
    	return $data;
	}

	public function getDevicesTypeClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		//select campaigns_id,count(*),device from clicks_log where campaigns_id=11 group by device;
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->select                        ='count(*) as clics,device_type';
		if($campaign                             !=null)$criteria->addCondition("DATE(date)>='".$dateStart."' AND DATE(date)<='".$dateEnd."' AND campaigns_id=".$campaign);
		else $criteria->addCondition("DATE(date) >='".$dateStart."' AND DATE(date)<='".$dateEnd."'");
		$criteria->group                         ='device_type';
		$criteria->order                         ='clics DESC';
		$clicksLogs                              = ClicksLog::model()->findAll($criteria);
	    foreach ($clicksLogs as $log) 
	    {
			$data[]=array(
				$log->device_type==null || $log->device_type=="" || $log->device_type=="-" || $log->device_type==" " ? "Other" : $log->device_type,
			 	intval($log->clics)
			 );
	    }
		return $data;
	}

	public function getDevicesClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		//select campaigns_id,count(*),device from clicks_log where campaigns_id=11 group by device;
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->join = 'LEFT JOIN conv_log c ON c.clicks_log_id = t.id';
		$criteria->select                        =array(
			'count(*) as clics',
			'count(c.id) as conv',
			'device',
			'device_model',
			'device_type',
			);
		if($campaign!=null)
			$criteria->addCondition("DATE(t.date)>='".$dateStart."' AND DATE(t.date)<='".$dateEnd."' AND t.campaigns_id=".$campaign);
		else
			$criteria->addCondition("DATE(t.date) >='".$dateStart."' AND DATE(t.date)<='".$dateEnd."'");

		$criteria->group                         ='device';
		//$criteria->order                         ='clics DESC';
		$clicksLogs                              = ClicksLog::model()->findAll($criteria);
		$data= new CActiveDataProvider(ClicksLog::model(), array(
				'criteria'   =>$criteria,
				'pagination'=>array(
                'pageSize'=>10,
            ),
				'sort'       =>array(
					'defaultOrder' => 'clics DESC',
					'attributes'   =>array(
			            'clics'=>array(
							'asc'  =>'clics',
							'desc' =>'clics DESC',
			            ),
			            'device'=>array(
							'asc'  =>'device',
							'desc' =>'device DESC',
			            ),
			            'device_model'=>array(
							'asc'  =>'device_model',
							'desc' =>'device_model DESC',
			            ),
			            'device_type'=>array(
							'asc'  =>'device_model',
							'desc' =>'device_model DESC',
			            ),
			            // Adding all the other default attributes
			            '*',
			        ),
			    ),

			));
		return $data;
	}

	public function getCarriersClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		//select campaigns_id,count(*),carrier,date from clicks_log where campaigns_id=11 group by carrier;
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->select                        ='count(*) as clics,carrier';
		if($campaign !=null)$criteria->addCondition("DATE(date)>='".$dateStart."' AND DATE(date)<='".$dateEnd."' AND campaigns_id=".$campaign);
		else $criteria->addCondition("DATE(date) >='".$dateStart."' AND DATE(date)<='".$dateEnd."'");
		$criteria->group                         ='carrier';
		$clicksLogs                              = ClicksLog::model()->findAll($criteria);
	    foreach ($clicksLogs as $log) 
	    {
	    	$data[]=array(
				$log->carrier==null || $log->carrier=="" || $log->carrier=="-" || $log->carrier==" " ? "Other" : $log->carrier,
			 	intval($log->clics)
			 );
	    }
		return $data;
	}

	public function getOSClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		//select campaigns_id,count(*),os,date from clicks_log where campaigns_id=11 group by os;
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->select                        ='count(*) as clics,os';
		if($campaign !=null)$criteria->addCondition("DATE(date)>='".$dateStart."' AND DATE(date)<='".$dateEnd."' AND campaigns_id=".$campaign);
		else $criteria->addCondition("DATE(date) >='".$dateStart."' AND DATE(date)<='".$dateEnd."'");
		$criteria->group                         ='os';
		$clicksLogs                              = ClicksLog::model()->findAll($criteria);
	    foreach ($clicksLogs as $log) 
	    {
	    	$data[]=array(
				$log->os==null || $log->os=="" || $log->os=="-" || $log->os==" " ? "Other" : $log->os,
			 	intval($log->clics)
			 );
	    }
		return $data;
	}

	public function getBrowsersClicks($dateStart=null,$dateEnd=null,$campaign=null)
	{
		//select campaigns_id,count(*),browser,date from clicks_log where campaigns_id=11 group by browser;
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->select                        ='count(*) as clics,browser';
		if($campaign !=null)$criteria->addCondition("DATE(date)>='".$dateStart."' AND DATE(date)<='".$dateEnd."' AND campaigns_id=".$campaign);
		else $criteria->addCondition("DATE(date) >='".$dateStart."' AND DATE(date)<='".$dateEnd."'");
		$criteria->group                         ='browser';
		$clicksLogs                              = ClicksLog::model()->findAll($criteria);
	    foreach ($clicksLogs as $log) 
	    {
			$data[]=array(
				$log->browser==null || $log->browser=="" || $log->browser=="-" || $log->os==" " ? "Other" : $log->browser,
			 	intval($log->clics)
			 );
	    }
		return $data;
	}

	public function getClicksRedirect($dateStart=null,$dateEnd=null,$campaign=null)
	{
		$data                                    =array();
		$dateStart                               =date('Y-m-d', strtotime($dateStart));
		$dateEnd                                 =date('Y-m-d', strtotime($dateEnd));
		$criteria                                =new CDbCriteria;
		$criteria->select                        ='count(*) as clics';
		if($campaign !=null)$criteria->addCondition("date BETWEEN '".$dateStart."' AND '".$dateEnd."' AND campaigns_id=".$campaign);
		else return 0;
		$clicksLogs                              = ClicksLog::model()->find($criteria)->clics;
		return $clicksLogs;
	}

	public function findByOpportunities($opportunitie)
	{		
		$criteria = new CDbCriteria;
		$criteria->addCondition("opportunities_id=".$opportunitie."");
		return new CActiveDataProvider($this, array(
				'criteria'=>$criteria,
				'pagination'=>false,
				'sort'=>array(
					'attributes'   =>array(
			            '*',
			        ),
			    ),

			));
	}

}
