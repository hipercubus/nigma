<?php
/* @var $this DailyReportController */
/* @var $model DailyReport */
/* @var $networks Networks[] */
/* @var $campaign Campaign */
/* @var $date Date */
/* @var $currentNetwork network_id */

$this->breadcrumbs=array(
	'Daily Reports'=>array('index'),
	'Create By Network',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#massivecreate-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		'id'                   => 'network-filter-form',
		'type'                 => 'search',
		'htmlOptions'          => array('class'=>'well'),
		// to enable ajax validation
		'enableAjaxValidation' => true,
		'action'               => Yii::app()->getBaseUrl() . '/dailyReport/createByNetwork',
		'method'               => 'GET',
		'clientOptions'        => array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

	<fieldset>
	Date: 
	<div class="input-append">
		<?php 
		    $this->widget('bootstrap.widgets.TbDatePicker',array(
			'name'  => 'date',
			'value' => date('d-m-Y', strtotime($date)),
			'htmlOptions' => array(
				'style' => 'width: 80px',
			),
		    'options' => array(
				'autoclose'      => true,
				'todayHighlight' => true,
				'format'         => 'dd-mm-yyyy',
				'viewformat'     => 'dd-mm-yyyy',
				'placement'      => 'right',
		    ),
		));
		?>
		<span class="add-on"><i class="icon-calendar"></i></span>
	</div>
	Network: 
	<?php echo CHtml::dropDownList('network', $currentNetwork, $networks, array('empty' => 'Select Network')); ?>

    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Load Campaigns', 'htmlOptions' => array('name' => 'networkSubmit'))); ?>

    </fieldset>

<?php $this->endWidget(); ?>

<hr>

<?php 
	if ( $currentNetwork != NULL )
		if ( Networks::model()->findByPk($currentNetwork)->use_vectors )
			$dataProvider = $vector->searchByNetworkAndDate($currentNetwork, $date);
		else
			$dataProvider = $campaign->searchByNetworkAndDate($currentNetwork, $date);
	else
		$dataProvider = $campaign->searchByNetworkAndDate($currentNetwork, $date);
?>

<?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	'id'                       => 'massivecreate-grid',
	'dataProvider'             => $dataProvider,
	// 'filter'                   => $campaign,
	'type'                     => 'striped condensed',
	'fixedHeader'              => true,
	'headerOffset'             => 50,
	'selectableRows'           => 0,
	'rowHtmlOptionsExpression' => 'array("id" => $data->id)',
	'template'                 => '{items} {pager} {summary}',
	'columns'                  => array(
			array(
				'name'        => 'name',
				'value'       => '$data->getExternalName($data->id)',
				'htmlOptions' => array('class' => 'span4', 'id' => 'external_name'),
			),
			array(
				'header'      => $model->getAttributeLabel('imp'),
				'type'        => 'raw',
				'htmlOptions' => array('class'=>'span1'),
				'value'       => 'CHtml::textField("row-imp", "", array(
					"style" => "width:80%; margin-bottom: 0px;",
    				"onkeydown" => "
        				var r = $( this ).parents( \"tr\" );
        				r.removeClass( \"control-group success\" );
        				r.addClass( \"control-group error\" );
        				r.find( \"#labelSubmit\" ).removeClass( \"label-success\" );
        				r.find( \"#labelSubmit\" ).addClass( \"label-important\" );
    				"
    				));',
			),
			array(
				'header'      => $model->getAttributeLabel('imp_adv'),
				'type'        => 'raw',
				'htmlOptions' => array('style'=>'width: 50px'),
				'value'       => 'CHtml::textField("row-imp_adv", "", array(
					"style" => "width:80%; margin-bottom: 0px;",
    				"onkeydown" => "
        				var r = $( this ).parents( \"tr\" );
        				r.removeClass( \"control-group success\" );
        				r.addClass( \"control-group error\" );
        				r.find( \"#labelSubmit\" ).removeClass( \"label-success\" );
        				r.find( \"#labelSubmit\" ).addClass( \"label-important\" );
    				"
    				));',
			),
			array(
				'header'      => $model->getAttributeLabel('clics'),
				'type'        => 'raw',
				'htmlOptions' => array('style'=>'width: 50px'),
				'value'       => 'CHtml::textField("row-clics", "", array(
					"style" => "width:80%; margin-bottom: 0px;",
    				"onkeydown" => "
        				var r = $( this ).parents( \"tr\" );
        				r.removeClass( \"control-group success\" );
        				r.addClass( \"control-group error\" );
						r.find( \"#labelSubmit\" ).removeClass( \"label-success\" );
        				r.find( \"#labelSubmit\" ).addClass( \"label-important\" );
    				"
    				));',
			),
			array(
				'header'      => $model->getAttributeLabel('spend'),
				'type'        => 'raw',
				'htmlOptions' => array('style'=>'width: 50px'),
				'value'       => 'CHtml::textField("row-spend", "", array(
					"style" => "width:80%; margin-bottom: 0px;",
    				"onkeydown" => "
        				var r = $( this ).parents( \"tr\" );
        				r.removeClass( \"control-group success\" );
        				r.addClass( \"control-group error\" );
        				r.find( \"#labelSubmit\" ).removeClass( \"label-success\" );
        				r.find( \"#labelSubmit\" ).addClass( \"label-important\" );
    				"
    				));',
			),
			array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 50px; vertical-align: middle;"),
			'template'          => '{submit}',
			'buttons'           => array(
				'submit' => array(
					'label' => 'Save',
					// 'icon'  => 'upload',
					'options' => array('class' => 'label', 'rel' => '', 'id'=>'labelSubmit'),
					'click' => '
				    function() {
				    	// Create parameters
						var tr                          = $( this ).parents( "tr" );
						var params                      = new Object();
						params.saveSubmit               = "";
						params.DailyReport              = new Object();
						params.DailyReport.networks_id  = $( "#network" ).val();
						params.DailyReport.imp          = tr.find( "#row-imp" ).val();
						params.DailyReport.imp_adv      = tr.find( "#row-imp_adv" ).val();
						params.DailyReport.clics        = tr.find( "#row-clics" ).val();
						params.DailyReport.spend        = tr.find( "#row-spend" ).val();
						
						var externalName                = tr.find( "#external_name" ).text();
						params.DailyReport.campaigns_id = externalName.substring(0, externalName.indexOf("-"));
						
						var tmp = $( "#date" ).val();
						var y = tmp.substring(tmp.lastIndexOf("-") + 1);
						var m = tmp.substring(tmp.indexOf("-") + 1, tmp.lastIndexOf("-"));
						var d = tmp.substring(0, tmp.indexOf("-"));
						params.DailyReport.date = y + "-" + m + "-" + d;

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
							"createByNetwork",
							params,
							function(data) {
								if (data.result == "OK") {
									var r = $( "#" + data.c_id );
 									r.removeClass( "control-group error" );
									r.addClass( "control-group success" );
									var l = r.find("#labelSubmit");
									l.removeClass( "label-important" );
									l.addClass( "label-success" );
									l.text("Update");
								}
								if (data.result == "ERROR") {
									var r = $( "#" + data.c_id );
									r.addClass( "control-group error" );
									r.find("#labelSubmit").removeClass( "label-success" );
									r.find("#labelSubmit").addClass( "label-important" );
								}
							},
							"json"
						)
						return false;
				    }
				    ',
				),
			),
		),
		),
)); ?>


<div class="row" id="blank-row"></div>
