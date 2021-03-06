<?php

class SitesController extends Controller
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
			array('allow', // allow actions
				'actions'=>array('admin','delete','create','response','update','view'),
				'roles'=>array('admin','media_buyer_admin','operation_manager'),
			),
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
		$this->renderPartial('view',array(
			'model'=>$this->loadModel($id),
		), false, true);
	}

	public function actionResponse($id){
		
		$action = isset($_GET['action']) ? $_GET['action'] : 'created';
		$this->layout='//layouts/modalIframe';
		$this->render('//layouts/mainResponse',array(
			'entity' => 'Site',
			'action' => $action,
			'id'    => $id,
		));
	}

	/**
	* Creates a new model.
	* If creation is successful, the browser will be redirected to the 'view' page.
	*/
	public function actionCreate()
	{
		$model=new Sites;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sites']))
		{
			$model->attributes=$_POST['Sites'];
			if($model->save())
				$this->redirect(array('response', 'id'=>$model->id, 'action'=>'created'));
		}

		$this->renderFormAjax($model, 'Create');
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

		if(isset($_POST['Sites']))
		{
			$model->attributes=$_POST['Sites'];
			if($model->save())
				$this->redirect(array('response', 'id'=>$model->id, 'action'=>'updated'));
		}

		$this->renderFormAjax($model, 'Update');
	}

	/**
	* Deletes a particular model.
	* If deletion is successful, the browser will be redirected to the 'admin' page.
	* @param integer $id the ID of the model to be deleted
	*/
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	* Lists all models.
	*/
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Sites');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			));
	}

	/**
	* Manages all models.
	*/
	public function actionAdmin()
	{
		KHtml::paginationController();
		
		$model=new Sites('search');
		$model->unsetAttributes();  // clear any default values

		$publisher = isset($_GET['publisher']) ? $_GET['publisher'] : null;

		if(isset($_GET['Sites']))
			$model->attributes=$_GET['Sites'];

		$this->render('admin',array(
			'model'=>$model,
			'publisher' => $publisher,
		));
	}

	/**
	* Returns the data model based on the primary key given in the GET variable.
	* If the data model is not found, an HTTP exception will be raised.
	* @param integer the ID of the model to be loaded
	*/
	public function loadModel($id)
	{
		$model=Sites::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	* Performs the AJAX validation.
	* @param CModel the model to be validated
	*/
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='sites-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	private function renderFormAjax($model, $action=null) 
	{
		$this->layout='//layouts/modalIframe';

		// $sizes      = CHtml::listData( BannerSizes::model()->findAll(array('order'=>'width, height')), 'id', 'size' );
		// $exchanges  = CHtml::listData( Exchanges::model()->findAll(array('order'=>'name')), 'id', 'name');
		$publishers = CHtml::listData( Providers::model()->findAll(array('order'=>'name', 'condition' => "status='Active'")), 'id', 'name');
		$model_pub = KHtml::enumItem($model, 'model');

		$this->render('_form', array(
			'model'      => $model,
			// 'sizes'      => $sizes,
			// 'exchanges'  => $exchanges,
			'publishers' => $publishers,
			'action'	 => $action,
			'model_pub'      => $model_pub,
		));
	}
}
