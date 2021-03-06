<?php

class SemController extends Controller
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
				'actions'=>array('keyword','placement','creative','excelReport', 'searchCriteria'),
				'roles'=>array('admin', 'sem'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionKeyword()
	{
		$model = new ClicksLog('search');
		$model->unsetAttributes();  // clear any default values

		if(isset($_GET['ClicksLog']))
			$model->attributes=$_GET['ClicksLog'];

		$this->render('report', array(
			'model'       => $model,
			'report_type' => 'keyword',
		));
	}


	public function actionPlacement()
	{
		$model = new ClicksLog('search');
		$model->unsetAttributes();  // clear any default values

		if(isset($_GET['ClicksLog']))
			$model->attributes=$_GET['ClicksLog'];

		$this->render('report', array(
			'model'       => $model,
			'report_type' => 'placement',
		));
	}


	public function actionCreative()
	{
		$model = new ClicksLog('search');
		$model->unsetAttributes();  // clear any default values

		if(isset($_GET['ClicksLog']))
			$model->attributes=$_GET['ClicksLog'];

		$this->render('report', array(
			'model'       => $model,
			'report_type' => 'creative',
		));
	}


	public function actionExcelReport()
	{
		// generate excel report if submitted
		if( isset($_POST['excel-report-sem']) ) {
			$this->renderPartial('excelReport', array(
				'model'       => new ClicksLog,
				'report_type' => $_POST['excel-report'],
			));
		}

		// render modal with config input information to generate excel report
		$this->renderPartial('_excelReport', array(
			'report_type' => $_POST['report_type'],
		), false, true);
	}


	public function actionSearchCriteria()
	{
		$model = new ClicksLog('search');
		$model->unsetAttributes();  // clear any default values

		if(isset($_GET['ClicksLog']))
			$model->attributes=$_GET['ClicksLog'];

		if (isset($_GET['submit-excel'])) {
			$this->renderpartial('excelSearchQuery', array(
				'model' => $model,
			));
		} 

		$this->render('searchCriteria', array(
			'model' => $model,
		));
	}
}