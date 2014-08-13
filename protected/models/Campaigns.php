<?php

/**
 * This is the model class for table "campaigns".
 *
 * The followings are the available columns in table 'campaigns':
 * @property integer $id
 * @property string $name
 * @property integer $networks_id
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
 *
 * The followings are the available model relations:
 * @property Networks $networks
 * @property Devices $devices
 * @property Formats $formats
 * @property CampaignCategories $campaignCategories
 * @property Opportunities $opportunities
 * @property ConvLog[] $convLogs
 * @property DailyReport[] $dailyReports
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
			array('name, networks_id, campaign_categories_id, wifi, formats_id, cap, model, devices_id, url, opportunities_id', 'required'),
			array('networks_id, campaign_categories_id, wifi, formats_id, ip, devices_id, opportunities_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>128),
			array('cap', 'length', 'max'=>11),
			array('model', 'length', 'max'=>3),
			array('url', 'length', 'max'=>256),
			array('status', 'length', 'max'=>8),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, advertisers_name, opportunities_rate, opportunities_carrie, networks_id, campaign_categories_id, wifi, formats_id, cap, model, ip, devices_id, url, status, opportunities_id', 'safe', 'on'=>'search'),
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
			'networks'           => array(self::BELONGS_TO, 'Networks', 'networks_id'),
			'devices'            => array(self::BELONGS_TO, 'Devices', 'devices_id'),
			'formats'            => array(self::BELONGS_TO, 'Formats', 'formats_id'),
			'campaignCategories' => array(self::BELONGS_TO, 'CampaignCategories', 'campaign_categories_id'),
			'opportunities'      => array(self::BELONGS_TO, 'Opportunities', 'opportunities_id'),
			'convLogs'           => array(self::HAS_MANY, 'ConvLog', 'campaign_id'),
			'clicksLogs'         => array(self::HAS_MANY, 'ClicksLog', 'campaign_id'),
			'dailyReports'       => array(self::HAS_MANY, 'DailyReport', 'campaigns_id'),
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
			'networks_id'            => 'Networks',
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
			// Header names for the related columns
			'advertisers_name'       => 'Advertiser', 
			'opportunities_rate'     => 'Rate', 
			'opportunities_carrier'  => 'Carrier',
			'post_data'              => 'Post Data',
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
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('networks_id',$this->networks_id);
		$criteria->compare('campaign_categories_id',$this->campaign_categories_id);
		$criteria->compare('t.wifi',$this->wifi);
		$criteria->compare('formats_id',$this->formats_id);
		$criteria->compare('cap',$this->cap,true);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('ip',$this->ip);
		$criteria->compare('devices_id',$this->devices_id);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('opportunities_id',$this->opportunities_id);
		$criteria->compare('post_data',$this->post_data);

		// We need to list all related tables in with property
		$criteria->with = array(  'opportunities', 'opportunities.ios.advertisers' );
		// Related search criteria items added (use only table.columnName)
		$criteria->compare('advertisers.name',$this->advertisers_name, true);
		$criteria->compare('opportunities.rate',$this->opportunities_rate, true);
		$criteria->compare('opportunities.carrier',$this->opportunities_carrier, true);

		return new CActiveDataProvider($this, array(
			'criteria' =>$criteria,
			// Setting 'sort' property in order to add 
			// a sort tool in the related collumns
			'sort'     =>array(
		        'attributes'=>array(
					// Adding custom sort attributes
		            'advertisers_name'=>array(
						'asc'  =>'advertisers.name',
						'desc' =>'advertisers.name DESC',
		            ),
		            'opportunities_rate'=>array(
						'asc'  =>'opportunities.rate',
						'desc' =>'opportunities.rate DESC',
		            ),
		            'opportunities_carrier'=>array(
						'asc'  =>'opportunities.carrier',
						'desc' =>'opportunities.carrier DESC',
		            ),
		            // Adding all the other default attributes
		            '*',
		        ),
		    ),
		));
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
}
