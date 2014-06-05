<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/bootstrap.css" />

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

    <?php Yii::app()->bootstrap->register(); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/styles.css" />
</head>

<body>
    
<?php $this->widget('bootstrap.widgets.TbNavbar',array(
    'items'=>array(
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'htmlOptions'=>array('class'=>'pull-right nav'),
            'items'=>array(
                array('label'=>'Dashboard', 'url'=>array('/site/index'), 'visible'=>!Yii::app()->user->isGuest),
				array('label'=>'Media', 'url'=>'#','itemOptions'=>array('class'=>'dropdown','tabindex'=>"-1"),'linkOptions'=>array('class'=>'dropdown-toggle','data-toggle'=>"dropdown"), 
                'items'=>array(
                    array('label'=>'Reporting', 'url'=>array('/campaigns/index')),
                    array('label'=>'Conversions', 'url'=>'#'),
                    array('label'=>'Uploading Campaigns', 'url'=>'#'),
                    array('label'=>'Optimization', 'url'=>'#'),
                    array('label'=>'Campaigns', 'url'=>array('/campaigns/admin')),
                ), 'visible'=>!Yii::app()->user->isGuest),
				array('label'=>'Sales', 'url'=>'#','itemOptions'=>array('class'=>'dropdown','tabindex'=>"-1"),'linkOptions'=>array('class'=>'dropdown-toggle','data-toggle'=>"dropdown"), 
                'items'=>array(
                    array('label'=>'Prospect', 'url'=>array('/advertisers/admin')),
                    array('label'=>'Client', 'url'=>'#'),
                    array('label'=>'IO Generation', 'url'=>'#'),
                    array('label'=>'New Client', 'url'=>'#'),
                    array('label'=>'Cierre y %', 'url'=>'#'),
                    array('label'=>'Media Kit', 'url'=>'#'),
                ), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Finance', 'url'=>'#','itemOptions'=>array('class'=>'dropdown','tabindex'=>"-1"),'linkOptions'=>array('class'=>'dropdown-toggle','data-toggle'=>"dropdown"), 
                'items'=>array(
                    array('label'=>'Clients', 'url'=>'#'),
                    array('label'=>'Providers', 'url'=>'#'),
                    array('label'=>'Cierre Mes', 'url'=>'#'),
                    array('label'=>'Invoices', 'url'=>'#'),
                ), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Daily', 'url'=>'#','itemOptions'=>array('class'=>'dropdown','tabindex'=>"-1"),'linkOptions'=>array('class'=>'dropdown-toggle','data-toggle'=>"dropdown"), 
                'items'=>array(
                    array('label'=>'Clients', 'url'=>'#'),
                    array('label'=>'Networks', 'url'=>'#'),
                    array('label'=>'Regions', 'url'=>'#'),
                    array('label'=>'PNL', 'url'=>'#'),
                    array('label'=>'AM', 'url'=>'#'),
                    array('label'=>'Daily Revenue', 'url'=>'#'),
                    array('label'=>'Budget', 'url'=>'#'),
                ), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Admin', 'url'=>'#','itemOptions'=>array('class'=>'dropdown','tabindex'=>"-1"),'linkOptions'=>array('class'=>'dropdown-toggle','data-toggle'=>"dropdown"), 
                'items'=>array(
                    array('label'=>'Profile', 'url'=>'#'),
                    array('label'=>'Users', 'url'=>'#'),
                    array('label'=>'Permissions', 'url'=>'#'),
                    array('label'=>'Configuration', 'url'=>'#'),
                ), 'visible'=>!Yii::app()->user->isGuest),
                array('label'=>'Login', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
                array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
            ),
        ),
    ),
)); ?>

<div class="container" id="page">

	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<?php endif?>

	<?php echo $content; ?>

	<div class="clear"></div>

	<footer>
        <div class="subnav navbar navbar-fixed-bottom">
            <div class="navbar-inner">
                <div class="container text-center">
                	<small>Copyright &copy; <?php echo date('Y'); ?> All Rights Reserved. Powered by <a href="http://www.kickads.mobi" title="Kickads.mobi" target="_new">Kickads.mobi</a></small>
                </div>
            </div>
        </div>      
	</footer>

</div><!-- page -->

</body>
</html>
