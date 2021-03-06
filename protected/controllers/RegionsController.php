<?php

class RegionsController extends Controller
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
				'actions'=>array('index','view','create','update','admin', 'response','delete', 'archived','redirect'),
				'roles'=>array('admin', 'commercial', 'commercial_manager', 'media_manager', 'account_manager','account_manager_admin','operation_manager'),
			),
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view','redirect','admin','archived'),
				'roles'=>array('businness', 'finance'),
			),
			// array('allow', // allow authenticated user to perform 'create' and 'update' actions
			// 	'actions'=>array('create','update'),
			// 	'users'=>array('@'),
			// ),
			// array('allow', // allow admin user to perform 'admin' and 'delete' actions
			// 	'actions'=>array('admin','delete'),
			// 	'users'=>array('admin'),
			// ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		KHtml::paginationController();
	
		$model=new Regions('search');
		$model->unsetAttributes();  // clear any default values
		$model->status = array('Active', 'Inactive');
		if(isset($_GET['Regions']))
			$model->attributes=$_GET['Regions'];

		$financeEntities   = isset($_GET['financeEntities']) ? $_GET['financeEntities'] : NULL;

		$this->render('admin',array(
			'model'=>$model,
			'financeEntities'=>$financeEntities,
		));
	}

	public function actionResponse($id){
		
		$action = isset($_GET['action']) ? $_GET['action'] : 'created';
		$this->layout='//layouts/modalIframe';
		$this->render('//layouts/mainResponse',array(
			'entity' => 'Region',
			'action' => $action,
			'id'    => $id,
		));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
	$model = $this->loadModel($id);
		$this->renderPartial('_view', array( 
			'model'=>$model 
		), false, true);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Regions;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Regions']))
		{
			$model->attributes=$_POST['Regions'];
			if($model->save())
				$this->redirect(array('response', 'id'=>$model->id, 'action'=>'created'));
		}

		$this->renderFormAjax($model);
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
		$this->performAjaxValidation($model);

		if(isset($_POST['Regions']))
		{
			$model->attributes=$_POST['Regions'];
			if($model->save())
				$this->redirect(array('response', 'id'=>$model->id, 'action'=>'updated'));
		}

		$this->renderFormAjax($model);
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
			case 'Inactive':
				$query ='UPDATE tags t
					RIGHT JOIN campaigns c ON(t.campaigns_id = c.id) 
					RIGHT JOIN opportunities o ON(c.opportunities_id = o.id) 
					SET t.status = "Archived", c.status = "Archived", o.status = "Archived" 
					WHERE o.regions_id = :pk
					';
				$return = Yii::app()->db->createCommand($query)->bindParam('pk',$id)->execute();

				$model->status = 'Archived';
				$model->save();
				break;
				
			case 'Archived':
				if ($model->financeEntities->status == 'Active') {
					$model->status = 'Active';
					$model->save();
				} else {
					echo "To restore this item must restore the finace entities associated with it.";
					Yii::app()->end();
				}
				break;
		}

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$this->redirect(array('admin'));
		
		// $dataProvider=new CActiveDataProvider('Regions');
		// $this->render('index',array(
		// 	'dataProvider'=>$dataProvider,
		// ));
	}

	/**
	 * Manages archived models.
	 */
	public function actionArchived()
	{
		$model=new Regions('search');
		$model->unsetAttributes();  // clear any default values
		$model->status = 'Archived';
		if(isset($_GET['Regions']))
			$model->attributes=$_GET['Regions'];

		$this->render('admin',array(
			'model'=>$model,
			'financeEntities'=>null,
			'isArchived' => true,
		));		
	}

/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Ios the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Regions::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Ios $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='regions-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function renderFormAjax($model) 
	{
		$this->layout='//layouts/modalIframe';
	
		$country = CHtml::listData(GeoLocation::model()->findAll( array('order'=>'name asc', "condition"=>"status='Active' AND type IN ('Country','Generic','Region')") ), 'id_location', 'name' );
		

        $criteria=new CDbCriteria;
        $criteria->compare('t.status','Active');
        $criteria->order = 't.name';
        $criteria->with = array('advertisers');
        if( UserManager::model()->isUserAssignToRole('account_manager') || UserManager::model()->isUserAssignToRole('account_manager_admin') )
            $criteria->compare('advertisers.cat', array('VAS','Affiliates','App Owners'));

		if( UserManager::model()->isUserAssignToRole('operation_manager') )
			$criteria->compare('advertisers.cat', array('Networks','Incent'));        

		$financeEntities = CHtml::listData(FinanceEntities::model()->findAll( $criteria ), 'id', 'name' );

		$this->render('_form',array(
			'model'      =>$model,
			'financeEntities'   =>$financeEntities,
			'country'    =>$country,
		));
	}

}