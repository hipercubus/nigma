<?php
/* @var $this DailyReportController */
/* @var $model DailyReport */

$this->breadcrumbs=array(
	'Daily Reports'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List DailyReport', 'url'=>array('index')),
	array('label'=>'Create DailyReport', 'url'=>array('create')),
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
?>

<?php
	$dpp            = isset($_GET['dpp']) ? $_GET['dpp'] : '5' ;
	$dateStart      = isset($_GET['dateStart']) ? $_GET['dateStart'] : 'today -7 days' ;
	$dateEnd        = isset($_GET['dateEnd']) ? $_GET['dateEnd'] : 'yesterday';
	$accountManager = isset($_GET['accountManager']) ? $_GET['accountManager'] : NULL;
	$advertisers    = isset($_GET['advertisers']) ? $_GET['advertisers'] : NULL;
	$opportunities  = isset($_GET['opportunities']) ? $_GET['opportunities'] : NULL;
	$providers      = isset($_GET['providers']) ? $_GET['providers'] : NULL;
	$adv_categories = isset($_GET['advertisers-cat']) ? $_GET['advertisers-cat'] : NULL;

	$editable = false;

	$group = array(
		'Date'          =>0, 
		'TrafficSource' =>0, 
		'Advertiser'    =>1, 
		// businnes unit
		'Country'       =>0, 
		'Campaign'      =>0,
		'Vector'      =>0,
		);
	if(isset($_GET['g'])) 
		$group = array_merge($group, $_GET['g']); 

	$grouped = array_search(0, $group) ? 1 : 0;

	$sum = array(
		'Imp'        =>1, 
		'Clicks'     =>1, 
		'CTR'        =>1,
		'Conv'       =>1, 
		'CR'         =>1,
		'Rate'       =>1, 
		'Revenue'    =>1,
		'Spend'      =>1,
		'Profit'     =>1,
		'eCPM'       =>0,
		'eCPC'       =>0,
		'eCPA'       =>0,
		);
	if(isset($_GET['s'])) 
		$sum = array_merge($sum, $_GET['s']); 
	
	$dateStart  = date('Y-m-d', strtotime($dateStart));
	$dateEnd    = date('Y-m-d', strtotime($dateEnd));
	$totalsGrap =$model->getTotals($dateStart,$dateEnd,$accountManager,$opportunities,$providers, $adv_categories);

?>
<div class="row">
	<div id="container-highchart" class="span12">
	<?php

	$this->Widget('ext.highcharts.HighchartsWidget', array(
		'options'=>array(
			'chart' => array('type' => 'area'),
			'title' => array('text' => ''),
			'xAxis' => array(
				'categories' => $totalsGrap['dates']
				),
			'tooltip' => array('crosshairs'=>'true', 'shared'=>'true'),
			'yAxis'   => array(
				'title' => array('text' => '')
				),
			'series' => array(
				array('name' => 'Imp.', 'data' => $totalsGrap['impressions'],),
				array('name' => 'Clicks', 'data' => $totalsGrap['clics'],),
				array('name' => 'Conv.','data' => $totalsGrap['conversions'],),
				array('name' => 'Revenue','data' => $totalsGrap['revenues'],),
				array('name' => 'Spend','data' => $totalsGrap['spends'],),
				array('name' => 'Profit','data' => $totalsGrap['profits'],),
				),
	        'legend' => array(
				'layout'          => 'vertical',
				'align'           =>  'left',
				'verticalAlign'   =>  'top',
				'x'               =>  40,
				'y'               =>  3,
				'floating'        =>  true,
				'borderWidth'     =>  1,
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

	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'type'        => 'info',
		'label'       => 'Add Daily Report Manualy',
		'block'       => false,
		'buttonType'  => 'linkButton',
		'url'         => 'create',
		'htmlOptions' => array(
			'id'                => 'createIframe',
			"data-grid-id"      => "daily-report-grid", 
			"data-modal-id"     => "modalDailyReport", 
			"data-modal-title"  => "Add Daily Report", 
			'onclick'           => 'event.preventDefault(); openModal(this)',
			),
		)
	); ?>
	
	<?php 
	//Create link to load filters in modal
	$link='excelReport?dateStart='.$dateStart.'&dateEnd='.$dateEnd.'&grouped='.$grouped;
	if(isset($accountManager)){
		if(is_array($accountManager)){
			foreach ($accountManager as $id) {
				$link.='&accountManager[]='.$id;
			}			
		}else{
			$link.='&accountManager='.$accountManager;
		}
	}
	if(isset($advertisers)){
		if(is_array($advertisers)){
			foreach ($advertisers as $id) {
				$link.='&advertisers[]='.$id;
			}			
		}else{
			$link.='&advertisers='.$advertisers;
		}
	}	
	if(isset($opportunities)){
		if(is_array($opportunities)){
			foreach ($opportunities as $id) {
				$link.='&opportunities[]='.$id;
			}			
		}else{
			$link.='&opportunities='.$opportunities;
		}
	}	
	if(isset($providers)){
		if(is_array($providers)){
			foreach ($providers as $id) {
				$link.='&providers[]='.$id;
			}			
		}else{
			$link.='&providers='.$providers;
		}
	}
	if(isset($adv_categories)){
		if(is_array($adv_categories)){
			foreach ($adv_categories as $id) {
				$link.='&advertisers-cat[]='.$id;
			}			
		}else{
			$link.='&advertisers-cat='.$adv_categories;
		}
	}

	$this->widget('bootstrap.widgets.TbButton', array(
		'type'        => 'info',
		'label'       => 'Excel Report',
		'block'       => false,
		'buttonType'  => 'ajaxButton',
		'url'         => $link,
		'ajaxOptions' => array(
			'type'    => 'POST',
			'beforeSend' => 'function(data)
				{
			    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
					$("#modalDailyReport").html(dataInicial);
					$("#modalDailyReport").modal("toggle");
				}',
			'success' => 'function(data)
				{
					$("#modalDailyReport").html(data);
				}',
			),
		'htmlOptions' => array('id' => 'excelReport'),
		)
	); ?>
</div>
<br/>

<!-- FILTERS -->

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		'id'                   =>'date-filter-form',
		'type'                 =>'search',
		'htmlOptions'          =>array('class'=>'well'),
		'enableAjaxValidation' =>true,
		'action'               => Yii::app()->getBaseUrl() . '/dailyReport/admin',
		'method'               => 'GET',
		'clientOptions'        =>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

<fieldset class="formfilter">
	<div>
		<?php echo KHtml::datePickerPresets($dpp); ?>
		<span class='formfilter-space'></span>
		<?php echo KHtml::datePicker('dateStart', $dateStart, array(), array('style'=>'width:73px'), 'From'); ?>
		<span class='formfilter-space'></span>
		<?php echo KHtml::datePicker('dateEnd', $dateEnd, array(), array('style'=>'width:73px'), 'To'); ?>
	</div>
	<hr>
	<?php 
		//Load Filters

	
		if (FilterManager::model()->isUserTotalAccess('daily'))
			KHtml::filterAccountManagersMulti($accountManager,array('id' => 'accountManager-select'),'opportunities-select','accountManager','opportunities');
		KHtml::filterAdvertisersMulti($advertisers, $accountManager, array('style' => "width: 140px; margin-left: 1em",'id' => 'advertisers-select'),'advertisers');
		KHtml::filterAdvertisersCategoryMulti($adv_categories, array('style' => "width: 140px; margin-left: 1em",'id' => 'advertisers-cat-select'),'advertisers-cat');
		KHtml::filterOpportunitiesMulti($opportunities, $accountManager, array('style' => "width: 140px; margin-left: 1em",'id' => 'opportunities-select'),'opportunities');
		KHtml::filterProvidersMulti($providers, NULL, array('style' => "width: 140px; margin-left: 1em",'id' => 'providers-select'),'providers');
		
	?>
	<hr>

	<?php

	// get filters //
	
	$style = 'width:120px;';

	echo '<div class="form-row">';
	
	KHtml::groupFilter($this, $group, 'g', 'Group Columns', $style);

	echo '</div>';
	echo '<div>';

	KHtml::groupFilter($this, $sum, 's', 'Sum Columns', $style);

	echo '</div>';
	?>

	<hr>
	<div class="formfilter-submit">
    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Submit', 'type' => 'success', 'htmlOptions' => array('class' => 'showLoading'))); ?>
	</div>

</fieldset>
<?php $this->endWidget(); ?>

<?php 

	$dataProvider=$model->search($dateStart, $dateEnd, $accountManager, $opportunities, $providers, $grouped, $adv_categories, $group, $sum, $advertisers);
	$totals=$model->searchTotals($dateStart, $dateEnd, $accountManager, $opportunities, $providers, $grouped, $adv_categories, $advertisers);

	$this->widget('application.components.NiExtendedGridView', array(
	'id'                       => 'daily-report-grid',
	'fixedHeader'              => true,
	'headerOffset'             => 50,
	'dataProvider'             => $dataProvider,
	'filter'                   => $model,
	'selectionChanged'         => 'js:selectionChangedDailyReport',
	'type'                     => 'striped condensed',
	'rowHtmlOptionsExpression' => 'array("data-row-id" => $data->id, "data-row-net-id" => $data->providers_id, "data-row-c-id" => $data->campaigns_id)',
	'template'                 => '{items} {pagerExt} {summary}',
	// 'rowCssClassExpression'    => '$data->getCapStatus() ? "errorCap" : null',
	'columns'                  => array(
		array(
			'name'               =>	'id',
			'footer'             => 'Totals:',
			// VECTOR COLOR
			'cssClassExpression' => '$data->isFromVector() ? "isFromVector" : NULL',
			'htmlOptions'        => array('style' => 'padding-left: 10px; height: 70px;'),
			'headerHtmlOptions'  => array('style' => 'border-left: medium solid #FFF;'),
            'visible' => false,
		),
		array(
			'name'              => 'date',
			'value'             => 'date("d-m-Y", strtotime($data->date))',
			'headerHtmlOptions' => array('style' => "width: 60px"),
			'htmlOptions'       => array(
					'class' => 'date', 
					'style' =>'text-align:right;'
				),
			// 'filter'      => false,
            'visible' => $group['Date'],
        ),
		array(
			'name'   =>	'providers_name',
			'value'  =>	'$data->providers->name',
			'filter' => $providers_names,
            'visible' => $group['TrafficSource'],
		),
		array(
			'name'   =>	'advertisers_name',
            'visible' => $group['Advertiser'],
		),
		array(
			'name'   =>	'country_name',
            'visible' => $group['Country'],
		),
		array(
			'name'        => 'campaign_name',
			'value'       => 'Campaigns::model()->getExternalName($data->campaigns_id)',
			'headerHtmlOptions' => array('width' => '200'),
			'htmlOptions' => array('style'=>'word-wrap:break-word;'),
            'visible' => $group['Campaign'],
		),
		array(
			'name'   =>	'vector',
			//'value'	=> '$data->dailyReportVectors->vectors_id',
            'visible' => $group['Vector'],
		),		
		array(	
			'name'              => 'imp',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => number_format($totals['imp']),
            'visible' => $sum['Imp'],
        ),
        array(	
			'name'              => 'imp_adv',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => number_format($totals['imp_adv']),
			'class'             => 'bootstrap.widgets.TbEditableColumn',
			'editable'          => array(
				'apply'      => $grouped ? false : true,
				'title'      => 'Impressions',
				'type'       => 'text',
				'url'        => 'updateEditable/',
				'emptytext'  => 'Add',
				'inputclass' => 'input-mini',
				'success'    => 'js: function(response, newValue) {
					  	if (!response.success) {
							$.fn.yiiGridView.update("daily-report-grid");
					  	}
					}',
            ),
            'visible' => $editable,
        ),
        array(
			'name'              => 'clics',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => number_format($totals['clics']),
            'visible' => $sum['Clicks'],
        ),
        /*array(
            'name'  => 'clics_redirect',
            'value' => '$data->getClicksRedirect()',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => number_format($totals['clics']),
        ),*/
		array(
			'name'              => 'click_through_rate',
			'value'             => $grouped ? 'number_format($data->getCtr()*100, 2)."%"' : 'number_format($data->click_through_rate*100, 2)."%"', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['imp']) && $totals['imp']!=0  ? (round($totals['clics'] / $totals['imp'], 4)*100)."%" : 0,
            'visible' => $sum['CTR'],
		),
        array(
			'name'              => 'conv_api',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => number_format($totals['conv_api']),
            'visible' => $sum['Conv'],
        ),
		array(
			'name'              => 'conv_adv',
			// 'filterHtmlOptions' => array('colspan'=>'2'),
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'class'             => 'bootstrap.widgets.TbEditableColumn',
			'cssClassExpression'=> '$data->campaigns->opportunities->rate === NULL 
									&& $data->campaigns->opportunities->carriers_id === NULL ?
									"notMultiCarrier" :
									"multiCarrier"',
			'editable'          => array(
				'apply'      => $grouped ? false : true,
				'title'      => 'Conversions',
				'type'       => 'text',
				'url'        => 'updateEditable/',
				'emptytext'  => 'Add',
				'inputclass' => 'input-mini',
				'success'    => 'js: function(response, newValue) {
					  	if (!response.success) {
							$.fn.yiiGridView.update("daily-report-grid");
					  	}
					}',
            ),
			'footer' => number_format($totals['conv_adv']),
            'visible' => $editable,
		),
		array(
			'name'              => 'mr',
			'filter'			=> false,
			'headerHtmlOptions' => array('class'=>'plusMR'),
			//'filterHtmlOptions' => array('class'=>'plusMR'),
			'htmlOptions'       => array('class'=>'plusMR'),
			'type'              => 'raw',
			'value'             =>	'
				$data->campaigns->opportunities->rate === NULL && $data->campaigns->opportunities->carriers_id === NULL && '.$grouped.' == 0 ?
					CHtml::link(
            				"<i class=\"icon-plus\"></i>",
	            			"javascript:;",
	        				array(
	        					"onClick" => CHtml::ajax( array(
									"type"    => "POST",
									"url"     => "multiRate/" . $data->id,
									"data"    => "'.$_SERVER['QUERY_STRING'].'",
									"success" => "function( data )
										{
											$(\"#modalDailyReport\").html(data);
											$(\"#modalDailyReport\").modal(\"toggle\");
										}",
									)),
								//"style"               => "width: 20px;pointer-events: none;cursor: default;",
								"rel"                 => "tooltip",
								"data-original-title" => "Update"
								)
						) 
				: null
				',
            'visible' => $editable,
        ),
		array(
			'name'              => 'conversion_rate',
			'value'             => $grouped ? 'number_format($data->getConvRate()*100, 2)."%"' : 'number_format($data->conversion_rate*100, 2)."%"', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['clics']) && $totals['clics']!=0 ? (round( $totals['conv'] / $totals['clics'], 4 )*100)."%" : 0,
            'visible' => $sum['CR'],
		),
		array(
			'name'        => 'rate',
			'value'       => '$data->getRateUSD() ? "$".number_format($data->getRateUSD(),2) : "$0.00"',
			'htmlOptions' => array('style'=>'text-align:right;'),
            'visible' => $sum['Rate'] && !$grouped,
		),
        array(
			'name'              => 'revenue',
			'value'             => '"$".number_format($data->getRevenueUSD(), 2)',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => '$'.number_format($totals['revenue'],2),
            'visible' => $sum['Revenue'],
        ),
		array(
			'name'              => 'spend',
			'value'             => '"$".number_format($data->getSpendUSD(), 2)',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => '$'.number_format($totals['spend'],2),
            'visible' => $sum['Spend'],
        ),
		array(
			'name'              => 'profit',
			'value'             => '"$".number_format($data->profit, 2)',
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => '$'.number_format($totals['profit'],2),
            'visible' => $sum['Profit'],
		),
		array(
			'name'              => 'profit_percent',
			'value'             => $grouped ? '$data->revenue == 0 ? "0%" : number_format($data->profit / $data->getRevenueUSD() * 100) . "%"' : 'number_format($data->profit_percent*100)."%"', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['revenue']) && $totals['revenue']!=0 ? number_format(($totals['profit'] / $totals['revenue']) * 100)."%" : 0,
            'visible' => $sum['Profit'],
		),
		array(
			'name'              => 'eCPM',
			'value'             => $grouped ? '"$".number_format($data->getECPM(), 2)' : '"$".$data->eCPM', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['imp']) && $totals['imp']!=0 ? '$'.round($totals['spend'] * 1000 / $totals['imp'], 2) : '$0',
            'visible' => $sum['eCPM'],
		),
		array(
			'name'              => 'eCPC',
			'value'             => $grouped ? '"$".number_format($data->getECPC(), 2)' : '"$".$data->eCPC', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['clics']) && $totals['clics']!=0 ? '$'.round($totals['spend'] / $totals['clics'], 2) : '$0',
            'visible' => $sum['eCPC'],
		),
		array(
			'name'              => 'eCPA',
			'value'             => $grouped ? '"$".number_format($data->getECPA(), 2)' : '"$".$data->eCPA', // FIX for sum feature
			'htmlOptions'       => array('style'=>'text-align:right;'),
			'footerHtmlOptions' => array('style'=>'text-align:right;'),
			'footer'            => isset($totals['conv']) && $totals['conv']!=0 ? '$'.round($totals['spend'] / $totals['conv'], 2) : '$0',
            'visible' => $sum['eCPA'],
		),
        array(	
			'name'        => 'comment',
			'filter'      => false,
			'sortable'    => false,
			'class'       => 'bootstrap.widgets.TbEditableColumn',
			'header' => false,
			'htmlOptions' => array('class'=>'editableField'),
			'editable'    => array(
				'title'   => 'Comment',
				'type'    => 'textarea',
				'url'     => 'updateEditable/',
				'display' => 'js:function(value, source){
					if(value){
						$(this).html("<i class=\"icon-font icon-red\"></i>");
					}else{
						$(this).html("<i class=\"icon-font\"></i>");
					}
				}'
            ),
            'visible' => !$grouped,
        ),
        array(
			'class'             => 'bootstrap.widgets.TbButtonColumn',
			'headerHtmlOptions' => array('style' => "width: 20px"),
            'visible' => $group['Campaign'],
			'buttons'           => array(
				'delete' => array(
					'visible' => '!$data->is_from_api',
				),
				'updateIframe' => array(
					'label'   => 'Update',
					'icon'    => 'pencil',
					'visible' => '!$data->is_from_api || $data->campaigns->editable == 1',
					'url'     => 'array("update", "id" => $data->id)',
					'options' => array(
						"data-grid-id"      => "daily-report-grid", 
						"data-modal-id"     => "modalDailyReport", 
						"data-modal-title"  => "Update Daily Report", 
						'onclick'           => 'event.preventDefault(); openModal(this)',
						),
					),
				// 'updateAjax' => array(
				// 	'label'   => 'Update',
				// 	'icon'    => 'pencil',
				// 	'visible' => '!$data->is_from_api || $data->campaigns->editable == 1',
				// 	'click'   => '
				//     function(){
				//     	// get row id from data-row-id attribute
				//     	var id = $(this).parents("tr").attr("data-row-id");

				//     	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
				// 		$("#modalDailyReport").html(dataInicial);
				// 		$("#modalDailyReport").modal("toggle");

				    	
				//     	// use jquery post method to get updateAjax view in a modal window
				//     	$.post(
				// 		"update/"+id,
				// 		"",
				// 		function(data)
				// 			{
				// 				//alert(data);
				// 				$("#modalDailyReport").html(data);
				// 			}
				// 		)
				// 	return false;
				//     }
				//     ',
				// ),
				'updateCampaign' => array(
					'label'   => 'Update Campaign',
					'icon'    => 'eye-open',
					//'visible' => '$data->getCapStatus()',
					'click' => '
				    function(){
				    	// get row id from data-row-id attribute
				    	var id = $(this).parents("tr").attr("data-row-c-id");

				    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
						$("#modalDailyReport").html(dataInicial);
						$("#modalDailyReport").modal("toggle");

				    	// use jquery post method to get updateAjax view in a modal window
				    	$.post(
						"'.Yii::app()->baseUrl.'/campaigns/updateAjax/"+id,
						"",
						function(data)
							{
								//alert(data);
								$("#modalDailyReport").html(data);
							}
						)
						return false;
				    }
				    ',
				),
			),

			'template' => $grouped ? '{updateCampaign}' : '{updateCampaign} {updateIframe} {delete}',
		),
	),
)); ?>

<?php BuildGridView::printModal($this, 'modalDailyReport', 'Daily Report'); ?>

<div class="row" id="blank-row">
</div>
