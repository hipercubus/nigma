<?php

/**
 * This is the model class for table "placements".
 *
 * The followings are the available columns in table 'placements':
 * @property integer $id
 * @property integer $exchanges_id
 * @property integer $publishers_id
 * @property integer $sizes_id
 * @property string $name
 * @property string $product
 *
 * The followings are the available model relations:
 * @property DailyPublishers[] $dailyPublishers
 * @property Publishers $publishers
 * @property Exchanges $exchanges
 * @property BannerSizes $sizes
 */
class Placements extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'placements';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('publishers_id, name', 'required'),
			array('exchanges_id, publishers_id, sizes_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>128),
			array('product', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, exchanges_id, publishers_id, sizes_id, name, product', 'safe', 'on'=>'search'),
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
			'dailyPublishers' => array(self::HAS_MANY, 'DailyPublishers', 'placements_id'),
			'publishers' => array(self::BELONGS_TO, 'Publishers', 'publishers_id'),
			'exchanges' => array(self::BELONGS_TO, 'Exchanges', 'exchanges_id'),
			'sizes' => array(self::BELONGS_TO, 'BannerSizes', 'sizes_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'exchanges_id' => 'Exchanges',
			'publishers_id' => 'Publishers',
			'sizes_id' => 'Sizes',
			'name' => 'Name',
			'product' => 'Product',
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
		$criteria->compare('exchanges_id',$this->exchanges_id);
		$criteria->compare('publishers_id',$this->publishers_id);
		$criteria->compare('sizes_id',$this->sizes_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('product',$this->product,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Placements the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
