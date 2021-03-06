<?php

/**
 * This is the model class for table "advertisers".
 *
 * The followings are the available columns in table 'advertisers':
 * @property integer $id
 * @property string $prefix
 * @property string $name
 * @property string $cat
 * @property integer $commercial_id
 * @property string $status
 * @property integer $users_id
 *
 * The followings are the available model relations:
 * @property Users $users
 * @property Users $commercial
 * @property ApiKey[] $apiKeys
 * @property ExternalIoForm[] $externalIoForms
 * @property Ios[] $ioses
 */
class Advertisers extends CActiveRecord
{

	public $commercial_name;
	public $commercial_lastname;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'advertisers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('prefix, name, cat', 'required'),
			array('commercial_id, users_id', 'numerical', 'integerOnly'=>true),
			array('ext_id', 'length', 'max'=>255),
			array('name', 'length', 'max'=>128),
			array('prefix', 'length', 'max'=>4),
			array('status', 'length', 'max'=>8),
			array('cat', 'length', 'max'=>16),
			array('name, prefix', 'unique'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, prefix, name, commercial_name, commercial_lastname, cat, commercial_id, status, users_id', 'safe', 'on'=>'search'),
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
			'users'           => array(self::BELONGS_TO, 'Users', 'users_id'),
			'commercial'      => array(self::BELONGS_TO, 'Users', 'commercial_id'),
			'apiKeys'         => array(self::HAS_MANY, 'ApiKey', 'advertisers_id'),
			'externalIoForms' => array(self::HAS_MANY, 'ExternalIoForm', 'advertisers_id'),
			'ioses'           => array(self::HAS_MANY, 'Ios', 'advertisers_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                  => 'ID',
			'prefix'              => 'Prefix',
			'name'                => 'Name',
			'cat'                 => 'Category',
			'commercial_id'       => 'Commercial',
			'commercial_name'     => 'Commercial',
			'commercial_lastname' => 'Commercial',
			'status'              => 'Status',
			'users_id'            => 'External user login',
			'ext_id'			  => 'External ID',
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
		$criteria->compare('prefix',$this->prefix,true);
		$criteria->compare('commercial_id',$this->commercial_id);

		if( UserManager::model()->isUserAssignToRole('account_manager') || UserManager::model()->isUserAssignToRole('account_manager_admin') ){
			$criteria->compare('cat', array('VAS','Affiliates','App Owners'));
		}
		else if( UserManager::model()->isUserAssignToRole('operation_manager') )
		{
			$criteria->compare('cat', array('Networks','Incent'));
		}
		else
		{
			$criteria->compare('cat',$this->cat,true);
		}

		$criteria->with = array('commercial');
		$criteria->compare('commercial.name', $this->commercial_lastname, true);
		$criteria->compare('commercial.lastname', $this->commercial_lastname, true, 'OR');
		$criteria->compare('t.status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria' =>$criteria,
			'pagination'=> KHtml::pagination(),
			// Setting 'sort' property in order to add 
			// a sort tool in the related collumns
			'sort'     =>array(
		        'attributes'=>array(
					// Adding custom sort attributes
		            'commercial_name'=>array(
						'asc'  =>'commercial.name',
						'desc' =>'commercial.name DESC',
		            ),
		            'commercial_lastname'=>array(
						'asc'  =>'commercial.lastname',
						'desc' =>'commercial.lastname DESC',
		            ),
		            // Adding all the other default attributes
		            '*',
		        ),
		    ),
		));
	}

	public function findByUser($id)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition('users_id='.$id);
		if($adv=Self::model()->find($criteria))
			return $adv;
	}

	public function getExternalUser($user_id){
		$model = self::model()->findByAttributes(array("users_id"=>$user_id));
		$name = isset($model) ? $model->name .' ('.$model->id.')' : null;
		return $name;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Advertisers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
