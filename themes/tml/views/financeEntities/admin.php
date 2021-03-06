<?php
/* @var $this OpportunitiesController */
/* @var $model Opportunities */
// Config parameters depending if have to show Archived or Admin view
if( isset($isArchived) ) {
	$delete['icon']       = 'refresh';
	$delete['label']      = 'Restore';
	$delete['confirm']    = 'Are you sure you want to restore this finance entity?';
	$breadcrumbs['title'] = 'Archived Finance Entities';
} else {
	$delete['icon']       = 'trash';
	$delete['label']      = 'Archive';
	$delete['confirm']    = 'Are you sure you want to archive this finance entity and all associated regions?';
	$breadcrumbs['title'] = 'Finance Entities';
}

$this->breadcrumbs=array(
	'Advertisers'=>array('advertisers/admin'),
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

<?php if( !isset($isArchived) )  : ?>
	<div class="botonera">
	<?php
	// $this->widget('bootstrap.widgets.TbButton', array(
	// 	'type'        => 'info',
	// 	'label'       => 'Create Finance Entities',
	// 	'block'       => false,
	// 	'buttonType'  => 'ajaxButton',
	// 	'url'         => 'create',
	// 	'ajaxOptions' => array(
	// 		'type'    => 'POST',
	// 		'beforeSend' => 'function(data)
	// 			{
	// 		    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
	// 				$("#modalFinanceEntities").html(dataInicial);
	// 				$("#modalFinanceEntities").modal("toggle");
	// 			}',
	// 		'success' => 'function(data)
	// 			{
	//                     // console.log(this.url);
	// 	                //alert("create");
	// 					$("#modalFinanceEntities").html(data);
	// 			}',
	// 		),
	// 	'htmlOptions' => array('id' => 'create'),
	// 	)
	// );
	?>
	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'type'        => 'info',
		'label'       => 'Create Finance Entity',
		'block'       => false,
		'buttonType'  => 'linkButton',
		'url'         => 'create',
		'htmlOptions' => array(
			"data-grid-id"      => "financeEntities-grid", 
			"data-modal-id"     => "modalFinanceEntities", 
			"data-modal-title"  => "Create Finance Entity", 
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
        'action' => Yii::app()->getBaseUrl() . '/financeEntities/admin',
        'method' => 'GET',
        'clientOptions'=>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

	<fieldset>
	<?php
	if (FilterManager::model()->isUserTotalAccess('campaign.account'))
		echo KHtml::filterAccountManagers($accountManager);

	echo KHtml::filterAdvertisers($advertiser);
	//FIXME Modificar filtros para Finance entities
	//echo KHtml::filterCountries($country);	  
	?>

    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Filter', 'htmlOptions' => array('class' => 'showLoading'))); ?>

    </fieldset>

<?php $this->endWidget(); ?>
<?php 
$this->widget('application.components.NiExtendedGridView', array(
	'id'                       => 'financeEntities-grid',
	'dataProvider'             => $model->search($advertiser, $accountManager),
	'filter'                   => $model,
	'type'                     => 'striped condensed',
	'rowHtmlOptionsExpression' => 'array(
		"data-row-id" => $data->id, 
		"class" => "deepLink",
		"onclick" => "deepLink(\"'.Yii::app()->createUrl('regions/admin').'?financeEntities=\"+$data->id)",
		)',
	'template'                 => '{items} {pagerExt} {summary}',
	'columns'                  =>array(
		array(
			'name'=>'id',
			'headerHtmlOptions' => array('style' => "width: 60px"),
		),
		array(
			'name'=>'advertiser_name',
			'value'=> '$data->advertisers->name',
			'headerHtmlOptions' => array('style' => "width: 80px"),
		),
		array(
			'name'=>'name',
			'headerHtmlOptions' => array('style' => "width: 100px"),
		),
		// 'status',
		'commercial_name',
		array(
		 	'name'=>'country_name',
		 	'value'=> '$data->country ? $data->country->name : ""',			
		),
		'contact_com',
		'contact_adm',
		array(
			'name'=>'com_name',
			'header'=>'Commercial Name',
			'value'=> '$data->commercial ? $data->commercial->name . " " . $data->commercial->lastname : ""',
		),
		// array(
		// 	'name'=>'entity',
		// 	'headerHtmlOptions' => array('style' => "width: 30px"),
		// ),
		array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 120px"),
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
						$("#modalFinanceEntities").html(dataInicial);
						$("#modalFinanceEntities").modal("toggle");

				    	$.post(
						"view/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalFinanceEntities").html(data);
							}
						)
						return false;
				    }
				    ',
				),
				// 'updateAjax' => array(
				// 	'label' => 'Update',
				// 	'icon'  => 'pencil',
				// 	'click' => '
				//     function(){
				//     	// get row id from data-row-id attribute
				//     	var id = $(this).parents("tr").attr("data-row-id");
				    	
				// 		var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
				// 		$("#modalFinanceEntities").html(dataInicial);
				// 		$("#modalFinanceEntities").modal("toggle");

				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"update/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalFinanceEntities").html(data);
				// 			}
				// 		)
				// 		return false;
				//     }
				//     ',
				// ),
				'updateIframe' => array(
					'label' => 'Update',
					'icon'  => 'pencil',
					'url'     => 'array("update", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "financeEntities-grid", 
						"data-modal-id"     => "modalFinanceEntities", 
						"data-modal-title"  => "Update Finance Entity", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				'duplicateAjax' => array(
					'label' => 'Duplicate',
					'icon'  => 'plus-sign',
					'click' => '
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-id");

						var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalFinanceEntities").html(dataInicial);
						$("#modalFinanceEntities").modal("toggle");

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
						"duplicate/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalFinanceEntities").html(data);
							}
						)
						return false;
				    }
				    ',
				),
				/*'generatePdf' => array(
					'label'   => 'Generate PDF',
					'icon'    => 'download',
					'url'     => 'Yii::app()->getBaseUrl(true) . "/ios/generatePdf/" . $data->id',
					'options' => array('target' => '_blank'),
					//'visible' => '$data->status == 10 ? false : true',
				),
				'uploadPdf' => array(
					'label' => 'Upload Signed IO',
					'icon'  => 'upload',
					'click' => '
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-id");

				    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalFinanceEntities").html(dataInicial);
						$("#modalFinanceEntities").modal("toggle");

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
						"uploadPdf/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalFinanceEntities").html(data);
							}
						)
						return false;
				    }
				    ',
				),
				'viewPdf' => array(
					'label'   => 'View Signed IO',
					'icon'    => 'file',
					'url'     => 'Yii::app()->getBaseUrl(true) . "/ios/viewPdf/" . $data->id',
					'options' => array('target' => '_blank'),
					'visible' => '$data->prospect == 10 ? true : false',
				)*/
			),
			'deleteButtonIcon'   => $delete['icon'],
			'deleteButtonLabel'  => $delete['label'],
			'deleteConfirmation' => $delete['confirm'],
			//'template' => '{viewAjax} {updateAjax} {duplicateAjax} {generatePdf} {uploadPdf} {viewPdf} {delete}',
			'template' => '{viewAjax} {updateIframe} {delete}',
		),
	),
)); ?>

<?php BuildGridView::printModal($this, 'modalFinanceEntities', 'Finance Entity'); ?>
