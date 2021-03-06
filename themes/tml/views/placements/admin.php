<?php
/* @var $this PlacementsController */
/* @var $model Placements */

// Config parameters depending if have to show Archived or Admin view
if( isset($isArchived) ) {
	$delete['icon']       = 'refresh';
	$delete['label']      = 'Restore';
	$delete['confirm']    = 'Are you sure you want to restore this placements?';
	$breadcrumbs['title'] = 'Archived Placements';
} else {
	$delete['icon']       = 'trash';
	$delete['label']      = 'Archive';
	$delete['confirm']    = 'Are you sure you want to archive this placements?';
	$breadcrumbs['title'] = 'Placements';
}

$this->breadcrumbs=array(
	'Publishers'=>array('publishers/admin'),
	'Sites'=>array('sites/admin'),
	$breadcrumbs['title'],
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#publishers-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php BuildGridView::createButton($this, array('placements/create'), 'modalPlacements', 'placements-grid', 'Create Placement'); ?>

<br>


<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
        'id'=>'date-filter-form',
        'type'=>'search',
        'htmlOptions'=>array('class'=>'well'),
        // to enable ajax validation
        'enableAjaxValidation'=>true,
        'action' => Yii::app()->getBaseUrl() . '/' . Yii::app()->controller->getId().'/'.Yii::app()->controller->getAction()->getId(),
        'method' => 'GET',
        'clientOptions'=>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

<fieldset>

	<?php echo KHtml::filterSites($site); ?>
    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Filter', 'htmlOptions' => array('class' => 'showLoading'))); ?>

</fieldset>
<?php $this->endWidget(); ?>

<?php $this->widget('application.components.NiExtendedGridView', array(
	'id'                       =>'placements-grid',
	'fixedHeader'              => true,
	'headerOffset'             => 50,
	'dataProvider'             => $model->search($site),
	'filter'                   => $model,
	'type'                     => 'striped condensed',
	'rowHtmlOptionsExpression' => 'array("data-row-id" => $data->id)',
	'template'                 => '{items} {pagerExt} {summary}',
	'columns'                  => array(
		// 'state',
		// 'zipcode',
		// 'address',
		// 'phone',
		// 'currency',
		// 'status',
		// 'contact_com',
		// 'email_com',
		// 'contact_adm',
		// 'email_adm',
		array(
			'name' => 'id',
			'headerHtmlOptions' => array('style' => "width: 80px"),
		),
		array(
			'name' => 'name',
		),
		array( 
			'name'  => 'sites_name',
			'value' => '$data->sites->name',
		),
		array( 
			'name'  => 'publishers_name',
			'value' => '$data->sites->providers->name',
		),
		// array( 
		// 	'name'  => 'exchanges_name',
		// 	'value' => '$data->exchanges->name',
		// ),
		array( 
			'name'  => 'size',
			'headerHtmlOptions' => array('style' => "width: 80px"),
			'value' => '$data->sizes->size',
		),
		array( 
			'name'  => 'model',
			'headerHtmlOptions' => array('style' => "width: 80px"),
		),
		array( 
			'name'  => 'publisher_percentage',
			'headerHtmlOptions' => array('style' => "width: 80px"),
		),
		array( 
			'name'  => 'rate',
			'headerHtmlOptions' => array('style' => "width: 80px"),
		),
		array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 120px"),
			'afterDelete'       => 'function(link, success, data) { if(data) alert(data); }',
			'buttons'           => array(
				'viewAjax' => array(
					'label' =>'Detail',
					'icon'  =>'eye-open',
					'click' =>'
				    function(){
				    	var id = $(this).parents("tr").attr("data-row-id");

						var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalPlacements").html(dataInicial);
						$("#modalPlacements").modal("toggle");

				    	$.post(
						"view/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalPlacements").html(data);
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
						"data-grid-id"      => "placements-grid", 
						"data-modal-id"     => "modalPlacements", 
						"data-modal-title"  => "Update Placement", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				'duplicateIframe' => array(
					'label' => 'Duplicate',
					'icon'  => 'plus-sign',
					'url'     => 'array("duplicate", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "placements-grid", 
						"data-modal-id"     => "modalPlacements", 
						"data-modal-title"  => "Duplicate Placement", 
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
				    	
				// 		var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						
				// 		$("#modalPlacements").html(dataInicial);
				// 		$("#modalPlacements").modal("toggle");

				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"update/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalPlacements").html(data);
				// 			}
				// 		)
				// 		return false;
				//     }
				//     ',
				// ),
				'waterfall' => array(
					'label' => 'Waterfall',
					'icon'  => 'tint',
					'click' => '
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-id");
				    	
						var dataInicial = "<img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" />";
						
						$("#modalPlacements .modal-body").html(dataInicial);
						$("#modalPlacements").modal("toggle");

						var dataInicial = "<iframe src=\"'.$this->createUrl('placements/waterfall/').'/"+id+"\" width=\"100%\" height=\"300px\" frameborder=\"0\" ></iframe>";
						$("#modalPlacements .modal-body").html(dataInicial);

    					var dataHeader = "<a class=\"close\" data-dismiss=\"modal\">&times;</a><h4>Placement Waterfall</h4>";
						$("#modalPlacements .modal-header").html(dataHeader);
						var dataFooter = "Edit Placement Waterfall";
						$("#modalPlacements .modal-footer").html(dataFooter);

				    	// use jquery post method to get updateAjax view in a modal window
				    	/*
				    	$.post(
						"waterfall/"+id,
						"",
						function(data)
							{
								//alert(data);
								console.log("modal")
								$("#modalPlacements").html(data);
							}
						)
						*/
						return false;
				    }
				    ',
				),
				'labelAjax' => array(
					'label' =>'Label',
					'icon'  =>'repeat',
					'click' =>'
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-id");
				    	
						var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						
						$("#modalPlacements").html(dataInicial);
						$("#modalPlacements").modal("toggle");

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
						"labelAjax/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalPlacements").html(data);
							}
						)
						return false;
				    }
				    ',
				),
			),
			'deleteButtonIcon'   => $delete['icon'],
			'deleteButtonLabel'  => $delete['label'],
			'deleteConfirmation' => $delete['confirm'],
			'template' => '{viewAjax} {updateIframe} {duplicateIframe} {labelAjax} {waterfall} {delete}',
		),
	),
)); ?>

<?php BuildGridView::printModal($this, 'modalPlacements', 'Placement'); ?>
