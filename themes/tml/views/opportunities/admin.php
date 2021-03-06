<?php
/* @var $this OpportunitiesController */
/* @var $model Opportunities */
// Config parameters depending if have to show Archived or Admin view
if( isset($isArchived) ) {
	$delete['icon']       = 'refresh';
	$delete['label']      = 'Restore';
	$delete['confirm']    = 'Are you sure you want to restore this opportunity?';
	$breadcrumbs['title'] = 'Archived Opportunities';
} else {
	$delete['icon']       = 'trash';
	$delete['label']      = 'Archive';
	$delete['confirm']    = 'Are you sure you want to archive this opportunity and all associated campaigns?';
	$breadcrumbs['title'] = 'Opportunities';
}

$this->breadcrumbs=array(
	'Advertisers'=>array('advertisers/admin'),
	'FinanceEntities'=>array('financeEntities/admin'),
	'Regions'=>array('regions/admin'),
	$breadcrumbs['title'],
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#opportunities-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php
	$accountManager   = isset($_GET['accountManager']) ? $_GET['accountManager'] : NULL;
	$advertiser   = isset($_GET['advertiser']) ? $_GET['advertiser'] : NULL;
	$country   = isset($_GET['country']) ? $_GET['country'] : NULL;
	$region   = isset($_GET['region']) ? $_GET['region'] : NULL;
?>
<?php if( !isset($isArchived) )  : ?>
	<div class="botonera">
	<?php
	// $this->widget('bootstrap.widgets.TbButton', array(
	// 	'type'        => 'info',
	// 	'label'       => 'Create Opportunity',
	// 	'block'       => false,
	// 	'buttonType'  => 'ajaxButton',
	// 	'url'         => 'create',
	// 	'ajaxOptions' => array(
	// 		'type'    => 'POST',
	// 		'beforeSend' => 'function(data)
	// 			{
	// 		    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
	// 				$("#modalOpportunities").html(dataInicial);
	// 				$("#modalOpportunities").modal("toggle");
	// 			}',
	// 		'success' => 'function(data)
	// 			{
	//                     // console.log(this.url);
	// 	                //alert("create");
	// 					$("#modalOpportunities").html(data);
	// 			}',
	// 		),
	// 	'htmlOptions' => array('id' => 'create'),
	// 	)
	// );
	?>
	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'type'        => 'info',
		'label'       => 'Create Opportunity',
		'block'       => false,
		'buttonType'  => 'linkButton',
		'url'         => 'create',
		'htmlOptions' => array(
			"data-grid-id"      => "opportunities-grid", 
			"data-modal-id"     => "modalOpportunities", 
			"data-modal-title"  => "Create Opportunity", 
			'onclick'           => 'event.preventDefault(); openModal(this)',
			),
		)
	); ?>
	</div>
<?php endif; ?>
<br>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
        'id'=>'date-filter-form',
        'type'=>'search',
        'htmlOptions'=>array('class'=>'well'),
        // to enable ajax validation
        'enableAjaxValidation'=>true,
        'action' => Yii::app()->getBaseUrl() . '/opportunities/admin',
        'method' => 'GET',
        'clientOptions'=>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

	<fieldset>
	<?php
	if (FilterManager::model()->isUserTotalAccess('campaign.account'))
		echo KHtml::filterAccountManagers($accountManager);

	echo KHtml::filterAdvertisers($advertiser);
	echo KHtml::filterRegions($region);
//	echo KHtml::filterCountries($country);	  
	?>

    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Filter', 'htmlOptions' => array('class' => 'showLoading'))); ?>

    </fieldset>

<?php $this->endWidget(); ?>
<?php  $this->widget('application.components.NiExtendedGridView', array(
	'id'                       => 'opportunities-grid',
	'dataProvider'             => $model->search($accountManager,$advertiser,$country,null,$region),
	'filter'                   => $model,
	'type'                     => 'striped condensed',
	'rowHtmlOptionsExpression' => 'array(
		"data-row-id" => $data->id, 
		"class" => "deepLink",
		"onclick" => "deepLink(\"'.Yii::app()->createUrl('campaigns/admin').'?opportunity=\"+$data->id)",
		)',
	'template'                 => '{items} {pagerExt} {summary}',
	'columns'                  =>array(
		array(
			'name'              =>'id',
			'headerHtmlOptions' => array('style'=>'width:10px'),
		),
		// array( 
		// 	'name'              => 'regions_name',
		// 	// 'header'            => 'Region',
		// 	'value'             => '$data->regions->country->name',
		// 	'headerHtmlOptions' => array('style'=>'width: 90px'),
		// ),
		array(
			'name'              =>'advertiser_name',
			'value'             =>'$data->regions->financeEntities->advertisers->name',
			'headerHtmlOptions' => array('style'=>'width: 90px'),
		),
		// array( 
		// 	'name'              =>'finance_entities_name',
		// 	'value'             =>'$data->regions->financeEntities->name',
		// 	'headerHtmlOptions' => array('style'=>'width: 90px'),
		// ),
		array(
			'name'              =>'country_name',
			'value'             =>'$data->regions->country ? $data->regions->country->name : ""',
			'headerHtmlOptions' => array('style'=>'width: 50px'),
		),
		array(
			'name'              =>'carrier_mobile_brand',
			'value'             =>'$data->carriers ? $data->carriers->mobile_brand : ""',
			'headerHtmlOptions' => array('style'=>'width: 90px'),
		),
		array(
			'name'              => 'product',
			'headerHtmlOptions' => array('style'=>'width: 50px'),
		),
		array(
			'name'              => 'model_adv',
			'headerHtmlOptions' => array('style'=>'width: 30px'),
		),
		/*array(
			'name'              => 'currency',
			'value'             =>'$data->ios->currency',
			'headerHtmlOptions' => array('style'=>'width: 30px'),
		),*/
		array(
			'name'              => 'rate',
			'headerHtmlOptions' => array('style'=>'width: 60px'),
		),
		// array(
		// 	'name'  =>'account_manager_lastname',
		// 	'value' =>'$data->account_manager_id ? $data->accountManager->lastname . " " . $data->accountManager->name : ""',
		// ),
		// 'comment',
		// array(
		// 	'name'  =>'country_name',
		// 	'value' =>'$data->country_id ? $data->country->name : ""',
		// ),
		// array( 
		// 	'name'  =>'wifi',
		// 	'value' =>'$data->wifi ? "Habilitado" : "Inhabilitado"',
		// ),
		array(
			'name' => 'budget',
			'headerHtmlOptions' => array('style'=>'width: 80px'),
		),
		// 'server_to_server',
		// array( 
		// 	'name'  =>'startDate',
		// 	'value' =>'$data->startDate !== "0000-00-00 00:00:00" ? date("d-m-Y", strtotime($data->startDate)) : ""',
		// 	'headerHtmlOptions' => array('style'=>'width: 80px'),
		// 	'filter' => false,
		// ),
		// array( 
		// 	'name'  =>'endDate',
		// 	'value' =>'$data->endDate !== "0000-00-00 00:00:00" ? date("d-m-Y", strtotime($data->endDate)) : ""',
		// 	'headerHtmlOptions' => array('style'=>'width: 80px'),
		// 	'filter' => false,
		// ),
		array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 70px"),
			'afterDelete'       => 'function(link, success, data) { if(data) alert(data); }',
			'htmlOptions' => array('onclick' => 'prevent=1;'),
			'buttons'           => array(
				'viewAjax' => array(
					'label' =>'Detail',
					'icon'  =>'eye-open',
					'click' =>'
				    function(){
				    	var id = $(this).parents("tr").attr("data-row-id");

				    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalOpportunities").html(dataInicial);
						buenoa$("#modalOpportunities").modal("toggle");

				    	$.post(
						"view/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalOpportunities").html(data);
							}
						)
						return false;
				    }
				    ',
				),
				'updateIframe' => array(
					'label' => 'Update',
					'icon'  => 'pencil',
					'url'     => 'array("update", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "opportunities-grid", 
						"data-modal-id"     => "modalOpportunities", 
						"data-modal-title"  => "Update Opportunity", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				// 'updateAjax' => array(
				// 	'label' => 'Update',
				// 	'icon'  => 'pencil',
				// 	'click' => '
				//     function(){
				//     	// get row id from data-row-id attribute
				//     	var id = $(this).parents("tr").attr("data-row-id");

				//     	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
				// 		$("#modalOpportunities").html(dataInicial);
				// 		$("#modalOpportunities").modal("toggle");

				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"update/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalOpportunities").html(data);
				// 			}
				// 		)
				// 		return false;
				//     }
				//     ',
				// ),
				'duplicateIframe' => array(
					'label' => 'Duplicate',
					'icon'  => 'plus-sign',
					'url'     => 'array("duplicate", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "opportunities-grid", 
						"data-modal-id"     => "modalOpportunities", 
						"data-modal-title"  => "Duplicate Opportunity", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				// 'duplicateAjax' => array(
				// 	'label' => 'Duplicate',
				// 	'icon'  => 'plus-sign',
				// 	'click' => '
				//     function(){
				//     	// get row id from data-row-id attribute
				//     	var id = $(this).parents("tr").attr("data-row-id");

				// 		var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
				// 		$("#modalOpportunities").html(dataInicial);
				// 		$("#modalOpportunities").modal("toggle");

				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"duplicate/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalOpportunities").html(data);
				// 			}
				// 		)
				// 		return false;
				//     }
				//     ',
				// ),
			),
			'deleteButtonIcon'   => $delete['icon'],
			'deleteButtonLabel'  => $delete['label'],
			'deleteConfirmation' => $delete['confirm'],
			'template' => '{viewAjax} {duplicateIframe} {updateIframe} {delete}',
		),
	),
)); ?>

<?php BuildGridView::printModal($this, 'modalOpportunities', 'Opportunity'); ?>
