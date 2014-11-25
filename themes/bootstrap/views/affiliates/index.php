<?php
/* @var $this AffiliatesController */

$this->breadcrumbs=array(
	'Affiliates',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#daily-report-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
//echo Affiliates::model()->findByUser(Yii::app()->user->id)->networks_id;
?>

<?php
	$dateStart      = isset($_GET['dateStart']) ? $_GET['dateStart'] : '-1 week' ;
	$dateEnd        = isset($_GET['dateEnd']) ? $_GET['dateEnd'] : 'today';
	$accountManager = isset($_GET['accountManager']) ? $_GET['accountManager'] : NULL;
	$opportunities  = isset($_GET['opportunities']) ? $_GET['opportunities'] : NULL;
	$networks       = isset($_GET['networks']) ? $_GET['networks'] : NULL;
	$adv_categories = isset($_GET['advertisers-cat']) ? $_GET['advertisers-cat'] : NULL;
	$sum            = isset($_GET['sum']) ? $_GET['sum'] : 0;

	$dateStart  = date('Y-m-d', strtotime($dateStart));
	$dateEnd    = date('Y-m-d', strtotime($dateEnd));
	$data = $model->getAffiliates($dateStart, $dateEnd, $network);
$alert = array('error', 'info', 'success', 'warning', 'muted');
?>
<div class="row totals-bar ">
	<div class="span6">
		<div class="alert alert-error">
			<small >TOTAL</small>
			<h3 class="">Conversions: <?php echo array_sum($data['graphic']['convs']) ?></h3>
			<br>
		</div>
	</div>
	<div class="span6">
		<div class="alert alert-info">
			<small >TOTAL</small>
			<h3 class="">Revenue: $<?php echo array_sum($data['graphic']['spends']) ?></h3>
			<br>
		</div>
	</div>
</div>
<div class="row">
	<div id="container-highchart" class="span12">
	<?php

	$this->Widget('ext.highcharts.HighchartsWidget', array(
		'options'=>array(
			'chart' => array('type' => 'area'),
			'title' => array('text' => ''),
			'xAxis' => array(
				'categories' => $data['graphic']['dates']
				),
			'tooltip' => array('crosshairs'=>'true', 'shared'=>'true'),
			'yAxis' => array(
				'title' => array('text' => '')
				),
			'series' => array(
				array('name' => 'Revenues', 'data' => $data['graphic']['spends'],),
				array('name' => 'Conversions', 'data' => $data['graphic']['convs'],),
				),
	        'legend' => array(
	            'layout' => 'vertical',
	            'align' =>  'left',
	            'verticalAlign' =>  'top',
	            'x' =>  40,
	            'y' =>  3,
	            'floating' =>  true,
	            'borderWidth' =>  1,
	            'backgroundColor' => '#FFFFFF'
	        	)
			),
		)
	);
	?>
			
	</div>
</div>

<hr>

<div class="botonera">
	<?php 
	//Create link to load filters in modal
	$link='excelReport?dateStart='.$dateStart.'&dateEnd='.$dateEnd.'&sum='.$sum;
	// $this->widget('bootstrap.widgets.TbButton', array(
	// 	'type'        => 'info',
	// 	'label'       => 'Excel Report',
	// 	'block'       => false,
	// 	'buttonType'  => 'ajaxButton',
	// 	'url'         => $link,
	// 	'ajaxOptions' => array(
	// 		'type'    => 'POST',
	// 		'beforeSend' => 'function(data)
	// 			{
	// 		    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
	// 				$("#modalDailyReport").html(dataInicial);
	// 				$("#modalDailyReport").modal("toggle");
	// 			}',
	// 		'success' => 'function(data)
	// 			{
	// 				$("#modalDailyReport").html(data);
	// 			}',
	// 		),
	// 	'htmlOptions' => array('id' => 'excelReport'),
	// 	)
	// ); 
	?>
</div>

<br>
<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		'id'                   =>'date-filter-form',
		'type'                 =>'search',
		'htmlOptions'          =>array('class'=>'well'),
		'enableAjaxValidation' =>true,
		'action'               => Yii::app()->getBaseUrl() . '/affiliates/index',
		'method'               => 'GET',
		'clientOptions'        =>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

<fieldset>
	From: <?php echo KHtml::datePicker('dateStart', $dateStart); ?>
	To: <?php echo KHtml::datePicker('dateEnd', $dateEnd); ?>
		
    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Filter', 'htmlOptions' => array('class' => 'showLoading'))); ?>

</fieldset>
<?php $this->endWidget(); ?>

<?php 
	// $totals=$model->getDailyTotals($dateStart, $dateEnd, $accountManager,$opportunities,$networks, $adv_categories);
	$this->widget('bootstrap.widgets.TbGroupGridView', array(
	'id'                       => 'daily-report-grid',
	// 'fixedHeader'              => true,
	// 'headerOffset'             => 50,
	'dataProvider'             => $data['dataProvider'],
	'filter'                   => $model,
	// 'selectionChanged'         => 'js:selectionChangedDailyReport',
	'type'                     => 'striped condensed',
	// 'rowHtmlOptionsExpression' => 'array("data-row-id" => $data->id, "data-row-net-id" => $data->networks_id, "data-row-c-id" => $data->campaigns_id)',
	'template'                 => '{items} {pager} {summary}',
	// 'rowCssClassExpression'    => '$data->getCapStatus() ? "errorCap" : null',
	'columns'      =>array(
                //array('name' =>'id'),
                array('name' =>'date'),
                array('name' =>'name'),
                array('name' =>'rate'),
                array('name' =>'conv'),
                array('name' =>'spend', 'header'=>'Revenue'),
            ),
	'mergeColumns' => array('date'),
	)
); ?>

<?php $this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'modalDailyReport')); ?>

		<div class="modal-header"></div>
        <div class="modal-body"></div>
        <div class="modal-footer"></div>

<?php $this->endWidget(); ?>

<div class="row" id="blank-row">
</div>