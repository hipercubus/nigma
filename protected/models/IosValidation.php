<?php

/**
 * This is the model class for table "ios_validation".
 *
 * The followings are the available columns in table 'ios_validation':
 * @property integer $id
 * @property integer $finance_entities_id
 * @property string $period
 * @property string $date
 * @property string $status
 * @property string $comment
 * @property string $validation_token
 * @property string $invoice_id
 *
 * The followings are the available model relations:
 * @property FinanceEntities $ios
 */
class IosValidation extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ios_validation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('period, date, validation_token', 'required'),
			array('finance_entities_id', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>255),
			array('comment', 'length', 'max'=>255),
			array('invoice_id', 'length', 'max'=>255),
			array('validation_token', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, finance_entities_id, period, date, status, comment, validation_token, invoice_id', 'safe', 'on'=>'search'),
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
			'financeEntities' => array(self::BELONGS_TO, 'FinanceEntities', 'finance_entities_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'finance_entities_id' => 'FinanceEntities',
			'period' => 'Period',
			'date' => 'Date',
			'status' => 'Status',
			'comment' => 'Comment',
			'validation_token' => 'Validation Token',
			'invoice_id' => 'Invoice Id',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('finance_entities_id',$this->finance_entities_id);
		$criteria->compare('period',$this->period,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('validation_token',$this->validation_token,true);
		$criteria->compare('invoice_id',$this->invoice_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return IosValidation the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * [loadModelByToken description]
	 * @param  [type] $token [description]
	 * @return [type]        [description]
	 */
	public function loadModelByToken($token)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition("validation_token='".$token."'");
		if($validation = self::find($criteria))
			return $validation;
		else
			return null;
	}

	/**
	 * [loadModelByIo description]
	 * @param  [type] $io     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function loadModelByIo($io,$period=null)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition("finance_entities_id=".$io);
		if($period)
		{
			$criteria->addCondition("MONTH(period)='".date('m', strtotime($period))."'");
			$criteria->addCondition("YEAR(period)='".date('Y', strtotime($period))."'");
		}
		$criteria->order="period DESC";
		if($validation = self::find($criteria))
			return $validation;
		else
			return null;
	}

	/**
	 * [checkValidationOpportunities description]
	 * @param  [type] $io     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function checkValidationOpportunities($io,$period)
	{
		$check=false;
		$ios=new FinanceEntities;
		$opportunitiesValidation=new OpportunitiesValidation;
		$clients = $ios->getClients(date('m', strtotime($period)),date('Y', strtotime($period)),null,$io,null,null,null,null,'otro');
		// foreach ($clients as $client) {			
		// 	foreach ($client as $data) {
		// 		$opportunities[]=$data;
		// 	}
		// }
		foreach ($clients['data'] as $opportunitie) {
			if($opportunitiesValidation->checkValidation($opportunitie['opportunitie_id'],$period)==true) $check=true;
			else return false;
		}
		return $check;
	}

	/**
	 * [checkValidation description]
	 * @param  [type] $io     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function checkValidation($io,$period)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition("finance_entities_id=".$io);
		$criteria->addCondition("MONTH(period)='".date('m', strtotime($period))."'");
		$criteria->addCondition("YEAR(period)='".date('Y', strtotime($period))."'");
		$criteria->order="period DESC";
		if($validation = self::find($criteria))
			return true;
		else
			return false;
	}
	
	/**
	 * [getCommentByIo description]
	 * @param  [type] $id     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function getCommentByIo($id,$period)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition('finance_entities_id='.$id);
		$criteria->compare("period",date('Y-m-d', strtotime($period)));
		if($validation = self::find($criteria))
			return $validation->comment;
		else
			return false;
	}
	
	/**
	 * [getStatusByIo description]
	 * @param  [type] $id     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function getStatusByIo($id,$period)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition('finance_entities_id='.$id);
		$criteria->addCondition("MONTH(period)='".date('m', strtotime($period))."'");
		$criteria->addCondition("YEAR(period)='".date('Y', strtotime($period))."'");
		if($validation = self::find($criteria))
			return $validation->status;
		else
			return 'Not Sent';
	}

	/**
	 * [getDateByIo description]
	 * @param  [type] $id     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function getDateByIo($id,$period)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition('finance_entities_id='.$id);
		$criteria->addCondition("MONTH(period)='".date('m', strtotime($period))."'");
		$criteria->addCondition("YEAR(period)='".date('Y', strtotime($period))."'");
		if($validation = self::find($criteria))
			return $validation->date;
		else
			return '';
	}

	/**
	 * [loadByIo description]
	 * @param  [type] $id     [description]
	 * @param  [type] $period [description]
	 * @return [type]         [description]
	 */
	public function loadByIo($id,$period)
	{
		$criteria=new CDbCriteria;
		$criteria->addCondition('finance_entities_id='.$id);
		$criteria->addCondition("MONTH(period)='".date('m', strtotime($period))."'");
		$criteria->addCondition("YEAR(period)='".date('Y', strtotime($period))."'");
		if($validation = self::model()->find($criteria))
			return $validation;
		else return false;
	}
}
