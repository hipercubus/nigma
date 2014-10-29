<?php

class CampaignsController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
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

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','duplicate','graphicCampaign','getOpportunities','trafficCampaignAjax','graphic','view','viewAjax','testAjax','create','createAjax','update','updateAjax','redirectAjax','admin','archived','delete','traffic','excelReport'),
				'roles'=>array('admin', 'media', 'media_manager'),
			),
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','viewAjax','redirectAjax','admin', 'traffic','graphicCampaign','trafficCampaignAjax','graphic'),
				'roles'=>array('businness', 'sem'),
			),
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('fetchCampaigns'),
				'users'=>array('*'),
				
			),
			/*
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),*/
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$modelCamp=new Campaigns;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Campaigns']))
		{
			$model->attributes=$_POST['Campaigns'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$modelCamp,
		));
	}


	/**
	 * AJAX ACTIONS
	 */
	

	/**
	 * Displays a particular model by ajax.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionViewAjax($id)
	{
		$model = $this->loadModel($id);

		$this->renderPartial('_viewAjax',array(
			'model'=>$model,
		), false, true);
	}

	/**
	 * Generate redirects.
	 */
	public function actionRedirectAjax($id)
	{
		$model    = $this->loadModel($id);
		$network = Networks::model()->findByPk($model->networks_id);

		$this->renderPartial('_redirects',array(
			'model'        => $model,
			'network'     => $network,
			'campaignName' => Campaigns::model()->getExternalName($id),
		), false, true);
	}

	/**
	 * Creates a new model by ajax.
	 * Optionally add a new opportunitie
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreateAjax()
	{

		$model=new Campaigns;
		//$modelOpp=new Opportunities;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Campaigns']))
		{
			$model->attributes=$_POST['Campaigns'];

			if($model->save())
				$this->redirect(array('admin'));
			
		}

		/*
		// edicion de oportunidades desestimada en este modelo
		if(isset($_POST['Opportunities']))
		{
			$modelOpp->attributes=$_POST['Opportunities'];
			$valid=$modelOpp->validate();

			if($valid){
				if($modelOpp->save())
					//$this->redirect(array('admin'));
					echo "successfull";
				else
					echo "error";
			}else{
				echo CActiveForm::validate($modelOpp);
			}
			Yii::app()->end();
		}
		 */

		$isAdmin = FilterManager::model()->isUserTotalAccess('campaign.account');

		if ( $isAdmin ) {
			$opportunities = CHtml::listData(Opportunities::model()->with('ios', 'ios.advertisers', 'country')->findAll(
				array('order'=>'advertisers.name, country.ISO2')), 
				'id', 
				function($opp) { return $opp->getVirtualName(); }
			);
		} else {
			$opportunities = CHtml::listData(Opportunities::model()->with('ios', 'ios.advertisers', 'country')->findAll(
				array('order'=>'advertisers.name, country.ISO2', 'condition'=>'account_manager_id='.Yii::app()->user->id)), 
				'id', 
				function($opp) { return $opp->getVirtualName(); }
			);
		}
		$categories    = CHtml::listData(CampaignCategories::model()->findAll(array('order'=>'name')), 'id', 'name');
		$networks      = CHtml::listData(Networks::model()->findAll(array('order'=>'name')), 'id', 'name');
		$formats       = CHtml::listData(Formats::model()->findAll(array('order'=>'name')), 'id', 'name');
		$devices       = CHtml::listData(Devices::model()->findAll(array('order'=>'name')), 'id', 'name');
		$campModel     = KHtml::enumItem($model, 'model');
		$this->renderPartial('_formAjax',array(
			'model'         => $model,
			//'modelOpp'    => $modelOpp,
			'opportunities' => $opportunities,
			'categories'    => $categories,
			'networks'      => $networks,
			'devices'       => $devices,
			'formats'       => $formats,
			'campModel'     => $campModel,
			'action'        => 'Create'
		), false, true);
	}
	
	/**
	 * Updates a particular model by ajax.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdateAjax($id)
	{
		$backURL = $_SERVER['HTTP_REFERER'];
		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Campaigns']))
		{
			$model->attributes=$_POST['Campaigns'];
			if($model->save())
				$this->redirect($backURL);
		}

		// use listData in order to send a list of categories to the view
		//$opportunities = CHtml::listData(Opportunities::model()->findAll(array('order'=>'id')), 'id', 'ios.name');
		$categories    = CHtml::listData(CampaignCategories::model()->findAll(array('order'=>'name')), 'id', 'name');
		$networks      = CHtml::listData(Networks::model()->findAll(array('order'=>'name')), 'id', 'name');
		$formats       = CHtml::listData(Formats::model()->findAll(array('order'=>'name')), 'id', 'name');
		$devices       = CHtml::listData(Devices::model()->findAll(array('order'=>'name')), 'id', 'name');
		$campModel     = KHtml::enumItem($model, 'model');
		$this->renderPartial('_formAjax',array(
			'model'         => $model,
			//'modelOpp'    => $modelOpp,
			//'opportunities' => $opportunities,
			'categories'    => $categories,
			'networks'      => $networks,
			'devices'       => $devices,
			'formats'       => $formats,
			'campModel'     => $campModel,
			'action'        => 'Update'
		), false, true);

	}

	public function actionDuplicate($id) 
	{
		$old = $this->loadModel($id);

		$new = clone $old;
		unset($new->id);
		$new->unsetAttributes(array('id'));
		$new->isNewRecord = true;
		
		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($new);

		if(isset($_POST['Campaigns']))
		{
			$new->attributes=$_POST['Campaigns'];
			if($new->save())
				$this->redirect(array('admin'));
		} 
		$model=$new;
		$categories    = CHtml::listData(CampaignCategories::model()->findAll(array('order'=>'name')), 'id', 'name');
		$networks      = CHtml::listData(Networks::model()->findAll(array('order'=>'name')), 'id', 'name');
		$formats       = CHtml::listData(Formats::model()->findAll(array('order'=>'name')), 'id', 'name');
		$devices       = CHtml::listData(Devices::model()->findAll(array('order'=>'name')), 'id', 'name');
		$campModel     = KHtml::enumItem($model, 'model');
		$this->renderPartial('_formAjax',array(
			'model'         => $model,
			//'modelOpp'    => $modelOpp,
			//'opportunities' => $opportunities,
			'categories'    => $categories,
			'networks'      => $networks,
			'devices'       => $devices,
			'formats'       => $formats,
			'campModel'     => $campModel,
			'action'        => 'Update'
		), false, true);
	}
	public function actionGraphicCampaign()
	{
		$model = $this->loadModel($_GET['id']);
		if(isset($_GET['dateStart']))$dateStart=$_GET['dateStart'];
		else $dateStart=date_format('Y-m-d',strtotime('today'));
		if(isset($_GET['dateEnd']))$dateEnd=$_GET['dateEnd'];
		else $dateEnd=date_format('Y-m-d',strtotime('today'));
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		$this->render('graphicCampaign',array(
			'model'       =>$model,
			'geo'         =>Campaigns::model()->getGeoClicks($dateStart,$dateEnd,$model->id),
			'carrier'     =>Campaigns::model()->getCarriersClicks($dateStart,$dateEnd,$model->id),
			'browser'     =>Campaigns::model()->getBrowsersClicks($dateStart,$dateEnd,$model->id),
			'os'          =>Campaigns::model()->getOSClicks($dateStart,$dateEnd,$model->id),
			'device'      =>Campaigns::model()->getDevicesClicks($dateStart,$dateEnd,$model->id),
			'device_type' =>Campaigns::model()->getDevicesTypeClicks($dateStart,$dateEnd,$model->id),
		));
	}

	public function actionTrafficCampaignAjax()
	{
		$backURL = $_SERVER['HTTP_REFERER'];
		$model = $this->loadModel($_GET['id']);
		//$totals=$model->totalsTraffic()
		$this->renderPartial('_formTrafficCampaignAjax',array(
			'model'        		=> $model,
			'dateStart' 		=> $_GET['dateStart'],
			'dateEnd' 			=> $_GET['dateEnd'],
			// 'categories'    => $categories,
			// 'networks'      => $networks,
			// 'devices'       => $devices,
			// 'formats'       => $formats,
			// 'campModel'     => $campModel,
			// 'action'        => 'Update'
		), false, true);

	}

	public function actionTestAjax(){
		echo "ajax ok";
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Campaigns']))
		{
			$model->attributes=$_POST['Campaigns'];
			if($model->save())
				$this->redirect(array('dailyReport/admin'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model = $this->loadModel($id);
		switch ($model->status) {
			case 'Active':
				$model->status = 'Archived';
				break;
			case 'Archived':
				if ($model->opportunities->status == 'Active') {
					$model->status = 'Active';
				} else {
					echo "To restore this item must restore the opportunity associated with it.";
					Yii::app()->end();
				}
				break;
		}

		$model->save();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Campaigns');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages active models.
	 */
	public function actionAdmin()
	{
		$model=new Campaigns('search');
		$model->unsetAttributes();  // clear any default values
		$model->status = 'Active';
		if(isset($_GET['Campaigns']))
			$model->attributes=$_GET['Campaigns'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}
	/**
	 * Manages archived models.
	 */
	public function actionArchived()
	{
		$model=new Campaigns('search');
		$model->unsetAttributes();  // clear any default values
		$model->status = 'Archived';
		if(isset($_GET['Campaigns']))
			$model->attributes=$_GET['Campaigns'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Campaigns the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Campaigns::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Campaigns $model the model to be validated
	 */
	protected function performAjaxValidation($model, $modelOpp=null)
	{
		if(isset($_POST['ajax']))
		{
			switch ($_POST['ajax']) {
				case 'campaigns-form':
					echo CActiveForm::validate($model);
					break;
				case 'opportunities-form':
					echo CActiveForm::validate($modelOpp);
					break;
				
				default:
					break;
			}
			Yii::app()->end();
		}
	}
	
	/**
	 * Return an array of all campaigns in json format.
	 * @return {cid, nid, url}
	 */
	
	public function actionExcelReport()
	{
		if( isset($_POST['excel-traffic']) ) {
			$this->renderPartial('excelReport', array(
				'model' => new Campaigns,
			));
		}

		$this->renderPartial('_excelReport', array(), false, true);
	}
	
	public function actionFetchCampaigns()
	{
		/*
		$criteria = new CDbCriteria;
		$criteria->select = 't.url';

		$campaigns = Campaigns::model()->findAll($criteria);
		var_dump($campaigns);
		echo "<hr/>";
		*/
	
		$q = Yii::app()->db->createCommand()
                    ->select('id, networks_id, url')
                    ->from("campaigns")
                    ->queryAll(false);
		echo json_encode($q);
	}

	public function actionTraffic()
	{
		$model=new Campaigns();
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Campaigns']))
			$model->attributes=$_GET['Campaigns'];

		$this->render('traffic',array(
			'model'=>$model,
		));
		
	}

	public function actionGetOpportunities($id=null)
	{
		// comentado provisoriamente, generar permiso de admin
		//$ios = Ios::model()->findAll( "advertisers_id=:advertiser AND commercial_id=:c_id", array(':advertiser'=>$id, ':c_id'=>Yii::app()->user->id) );
		if($id)$opps = Opportunities::model()->findAll( "account_manager_id=:accountManager", array(':accountManager'=>$id) );
		else $opps =Opportunities::model()->findAll();
		$response='<option value="">All opportunities</option>';
		foreach ($opps as $op) {
			$response .= '<option value="' . $op->id . '">' . $op->getVirtualName() . '</option>';
		}
		echo $response;
		Yii::app()->end();
	}
	
	public function actionGraphic() {
		if ( isset($_POST['c_id'])) {
			$c_id = $_POST['c_id'];
		} else {
			// echo json_encode("ERROR c_id or net_id missing");
			Yii::app()->end();
		}

		if ( isset($_POST['endDate']) ) {
			$endDate = new DateTime( $_POST['endDate'] );
		} else {
			$endDate = new DateTime("NOW");
		}

		
		if ( isset($_POST['startDate']) ) {
			$startDate = new DateTime( $_POST['startDate'] );
		} else {
			$startDate = new DateTime( $endDate->format("Y-m-d") );
			$startDate = $startDate->sub( DateInterval::createFromDateString('7 days') );
		}
		// $startDate = new DateTime($_GET['startDate']);
		// $endDate = new DateTime($_GET['endDate']);
		// $c_id=$_GET['c_id'];
		$model = new Campaigns();
		$response = $model->totalsTraffic($startDate->format("Y-m-d"), $endDate->format("Y-m-d"), $c_id );
		echo json_encode($response, JSON_NUMERIC_CHECK);
		Yii::app()->end();
	}

}
