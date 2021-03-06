<?php
/* @var $this UsersController */
/* @var $model Users */

$this->breadcrumbs=array(
	'Users'=>array('index'),
	'Manage Users',
);

$this->menu=array(
	// array('label'=>'List Users', 'url'=>array('index')),
	// array('label'=>'Create Users', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#users-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<div class="botonera">
<?php
// $this->widget('bootstrap.widgets.TbButton', array(
// 	'type'        => 'info',
// 	'label'       => 'Create User',
// 	'block'       => false,
// 	'buttonType'  => 'ajaxButton',
// 	'url'         => 'create',
// 	'ajaxOptions' => array(
// 		'type'    => 'POST',
// 		'beforeSend' => 'function(data)
// 			{
// 		    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
// 				$("#modalUser").html(dataInicial);
// 				$("#modalUser").modal("toggle");
// 			}',
// 		'success' => 'function(data)
// 			{
//                     console.log(data);
// 	                // alert("create");
// 					$("#modalUser").html(data);
// 			}',
// 		),
// 	'htmlOptions' => array('id' => 'create'),
// 	)
// );
?>
<?php $this->widget('bootstrap.widgets.TbButton', array(
	'type'        => 'info',
	'label'       => 'Create User',
	'block'       => false,
	'buttonType'  => 'linkButton',
	'url'         => 'create',
	'htmlOptions' => array(
		"data-grid-id"      => "users-grid", 
		"data-modal-id"     => "modalUser", 
		"data-modal-title"  => "Create User", 
		'onclick'           => 'event.preventDefault(); openModal(this)',
		),
	)
); ?>
</div>

<?php $this->widget('application.components.NiExtendedGridView', array(
	'id'=>'users-grid',
	'dataProvider'             => $model->search(),
	'filter'                   => $model,
	'type'                     => 'striped condensed',
	'rowHtmlOptionsExpression' => 'array("data-row-id" => $data->id)',
	'template'                 => '{items} {pagerExt} {summary}',
	'columns'=>array(
		array(
			'name' => 'id',
			'headerHtmlOptions' => array('style' => 'width: 65px;'),
			),
		'username',
		// 'password',
		'email',
		'name',
		'lastname',
		array(
			'name' => 'status',
			'headerHtmlOptions' => array('style' => 'width: 45px;'),
			),
		array(
			'name'  => 'partners_external_access_type',
			'value' => 'Users::model()->getPartnerName($data->id, true)',
			'filter' => false,
			),
		array(
			'name'  => 'partners_external_access',
			'value' => 'Users::model()->getPartnerName($data->id)',
			'filter' => false,
			),
		array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 100px"),
			'buttons'           => array(
				'viewAjax' => array(
					'label' =>'Detail',
					'icon'  =>'eye-open',
					'click' =>'
				    function(){
				    	var id = $(this).parents("tr").attr("data-row-id");

				    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalUser").html(dataInicial);
						$("#modalUser").modal("toggle");

				    	$.post(
						"view/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalUser").html(data);
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
						"data-grid-id"      => "users-grid", 
						"data-modal-id"     => "modalUser", 
						"data-modal-title"  => "Update User", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				'permissionsIframe' => array(
					'label' => 'Permissions',
					'icon'  => 'lock',
					'url'     => 'array("adminRoles", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "users-grid", 
						"data-modal-id"     => "modalUser", 
						"data-modal-title"  => "User Permissions", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				// 'permissions' => array(
				// 	'label' => 'Permissions',
				// 	'icon'  => 'lock',
				// 	'click' => '
				//     function(){
				//     	// get row id from data-row-id attribute
				//     	var id = $(this).parents("tr").attr("data-row-id");

				//     	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
				// 		$("#modalUser").html(dataInicial);
				// 		$("#modalUser").modal("toggle");

				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"adminRoles/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalUser").html(data);
				// 			}
				// 		)
				// 		return false;
				//     }
				//     ',
				// ),
				'visibilityIframe' => array(
					'label' => 'Visibility',
					'icon'  => 'th-list',
					'url'     => 'array("visibility", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "users-grid", 
						"data-modal-id"     => "modalUser", 
						"data-modal-title"  => "User Visibility", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				'visibility' => array(
					'label' => 'Visibility',
					'icon'  => 'th-list',
					'click' => '
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-id");

				    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalUser").html(dataInicial);
						$("#modalUser").modal("toggle");

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
						"visibility/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalUser").html(data);
							}
						)
						return false;
				    }
				    ',
				),
				'preview' => array(
					'label'   => 'Preview External Login',
					'icon'    => 'eye-close',
					'url'     => 'Yii::app()->createUrl(Users::getPartnerPreview($data->primaryKey),array("id"=>$data->primaryKey))',
					'options' => array('target'=>'_blank'),
				),
			),
			'template' => '{viewAjax} {updateIframe} {permissionsIframe} {visibilityIframe} {preview} {delete}',
		),
	),
)); ?>

<?php $this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'modalUser')); ?>

		<div class="modal-header"></div>
        <div class="modal-body"><h1>Advertiser</h1></div>
        <div class="modal-footer"></div>

<?php $this->endWidget(); ?>