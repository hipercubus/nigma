<?php
/* @var $this IosController */
/* @var $model Ios */
?>

<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h4>Io <?php echo "#".$model->id ?></h4>
</div>

<div class="modal-body">

	<h5>Advertiser</h5>
	<?php $this->widget('bootstrap.widgets.TbDetailView', array(
	    'type'=>'striped bordered condensed',
		'data'=>$model,
		'attributes'=>array(
			'advertisers.id',
			'advertisers.name',
			'advertisers.cat'
		),
	)); ?>

	<h5>Commercial</h5>
	<?php $this->widget('bootstrap.widgets.TbDetailView', array(
	    'type'=>'striped bordered condensed',
		'data'=>$model,
		'attributes'=>array(
			'commercial.id',
			'commercial.name',
			'commercial.lastname',
			'commercial.username',
			'commercial.email',
		),
	)); ?>

	<h5>Insertion Order</h5>
	<?php $this->widget('bootstrap.widgets.TbDetailView', array(
	    'type'=>'striped bordered condensed',
		'data'=>$model,
		'attributes'=>array(
			'id',
			'name',
			'commercial_name',
			array(
				'label' =>$model->getAttributeLabel('country_name'),
				'name'  =>'country.name'
			),
			'address',
			'state',
			'zip_code',
			'phone',
			'contact_com',
			'email_adm',
			'contact_adm',
			'currency',
			'ret',
			'tax_id',
			'net_payment',
			'entity',
			'description',
		),
	)); ?>
	
	<h5>Opportunities</h5>
	<?php 
	$opportunitie=new Opportunities;
	$this->widget('bootstrap.widgets.TbGridView', array(
    'id'           =>'conversions-grid',
    'type'         =>'striped condensed',
    'dataProvider' =>$opportunitie->findByIo($model->id),
    'template'     =>'{items} {summary} {pager}',
    'columns'=>array(
        array(
			'name'              =>'id',
			'headerHtmlOptions' => array('style'=>'width: 50px'),
		),
		array(
			'name'              =>'advertiser_name',
			'value'             =>'$data->ios->advertisers->name',
			'headerHtmlOptions' => array('style'=>'width: 90px'),
		),
		array(
			'name'              =>'country_name',
			'value'             =>'$data->country ? $data->country->ISO2 : ""',
			'headerHtmlOptions' => array('style'=>'width: 50px'),
		),
		array(
			'name'              =>'carrier_mobile_brand',
			'value'             =>'$data->carriers ? $data->carriers->mobile_brand : ""',
			'headerHtmlOptions' => array('style'=>'width: 90px'),
		),
		'product',
		array(
			'name'              => 'model_adv',
			'headerHtmlOptions' => array('style'=>'width: 30px'),
		),
		array(
			'name'              => 'currency',
			'value'             =>'$data->ios->currency',
			'headerHtmlOptions' => array('style'=>'width: 30px'),
		),
		array(
			'name'              => 'rate',
			'headerHtmlOptions' => array('style'=>'width: 60px'),
		),
		array(
			'name' => 'budget',
			'headerHtmlOptions' => array('style'=>'width: 80px'),
		),
    ),
));
?>
</div>

<div class="modal-footer">
    Io detail view.
</div>