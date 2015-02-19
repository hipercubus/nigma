<?php

class PartnersController extends Controller
{

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column1', meaning
	 * using two-column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';

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

	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('affiliates'),
				'roles'=>array('admin','affiliate'),
			),
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('advertisers'),
				'roles'=>array('admin','advertiser'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionAffiliates()
	{
		// if(Yii::app()->user->id)
		// {
			$dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : '-1 week' ;
			$dateEnd   = isset($_GET['dateEnd']) ? $_GET['dateEnd'] : 'today';
			$sum       = isset($_GET['sum']) ? $_GET['sum'] : 0;
			
			$dateStart = date('Y-m-d', strtotime($dateStart));
			$dateEnd   = date('Y-m-d', strtotime($dateEnd));
			
			$model     = new Affiliates;
			$provider  = Affiliates::model()->findByUser(Yii::app()->user->id)->providers_id;
			
			$data = $model->getAffiliates($dateStart, $dateEnd, $provider);

			$this->render('affiliates',array(
				'model'     =>$model,
				'provider'  =>$provider,
				'dateStart' =>$dateStart,
				'dateEnd'   =>$dateEnd,
				'sum'       =>$sum,
				'data'      =>$data
			));
		// }
		// else
		// {			
		// 	$this->redirect(Yii::app()->baseUrl);
		// }	
	}

	public function actionAdvertisers()
	{
		$year  = isset($_GET['year']) ? $_GET['year'] : date('Y', strtotime('today'));
		$month = isset($_GET['month']) ? $_GET['month'] : date('m', strtotime('today'));
		
		$advertiser = Advertisers::model()->findByUser(Yii::app()->user->id);
		
		$data = Ios::model()->getClients(
			$month, // month
			$year, // year
			null, // entity
			null, // io
			null, // account
			null, // opportunity
			null, // cat
			null, // status
			null, // group
			false, // close deal
			$advertiser->id
		);

		// Create dataProvider
		$dataProvider=new CArrayDataProvider($data['data'], array(
		    'id'=>'clients',
		    'sort'=>array(
		    	'defaultOrder'=>'country ASC',
		        'attributes'=>array(
		             'id', 'name', 'model', 'entity', 'currency', 'rate', 'conv','revenue', 'carrier','country','product','mobileBrand'
		        ),
		    ),
		    'pagination'   =>array(
		        'pageSize' =>30,
		    ),
		));


		$this->render('advertisers',array(
			// 'model'      =>$model,
			'advertiser'   =>$advertiser,
			'data'         =>$data,
			'dataProvider' =>$dataProvider,
			'month'        =>$month,
			'year'         =>$year
		));
	}
}