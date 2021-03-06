<?php
/* @var $this IosController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Finance'=>'#',	
	'Clients',
);
 Yii::app()->clientScript->registerScript("", "$('.ipopover').popover({'placement':'left'});", CClientScript::POS_READY);
?>

<?php
//Totals
echo '
<div class="row totals-bar ">
    <div class="span12">
        <div class="alert alert-error">
        	<h4 class="">Subtotal: '.number_format($totals['subtotal'],2).'</h4>
        	<h5 class="">Total Count: '.number_format($totals['subtotalCount'],2).'</h5>
        	<h5 class="">Total Clients: '.number_format($totals['total'],2).'</h5>
        </div>
    </div>
</div>
'; 

//echo KHtml::currencyTotals($totals->getData());

$this->menu=array(
	array('label'=>'Create Ios', 'url'=>array('create')),
	array('label'=>'Manage Ios', 'url'=>array('admin')),
);
$log    =new ValidationLog;
$financeEntity    =new FinanceEntities;
/*
$buttonsColumn='
				CHtml::ajaxLink(
					"<i style=\"cursor:default\" id=\"icon-status\" class=\"".strtolower(str_replace(" ","_",$data["status_io"]))."\"></i>", 
					"javascript:void(0)", 
				    array (), 
				    array ("data-toggle"=>"tooltip", "data-original-title"=>$data["status_io"])
				).
				CHtml::link(
					"<i class=\"icon-eye-open\"></i>",
					array("finance/view/".$data["id"]),
    				array("class"=>"link", "data-toggle"=>"tooltip", "data-original-title"=>"View IO")


					).';
if (FilterManager::model()->isUserTotalAccess('clients.validateIo'))
	$buttonsColumn.='CHtml::link(
					"<i class=\"icon-envelope\"></i>",
					array("revenueValidation?io=".$data["id"]."&month='.$month.'&year='.$year.'"),
    				array("class"=>"link", "data-toggle"=>"tooltip", "data-original-title"=>"Send Mail")


					).';
else 
	$buttonsColumn.='CHtml::link(
					"<i style=\"cursor:default\" class=\"icon-envelope\"></i>",
					array(""),
    				array("class"=>"no-link", "data-toggle"=>"tooltip", "data-original-title"=>"Send Mail")


					).';
if (FilterManager::model()->isUserTotalAccess('clients.invoice'))
	$buttonsColumn.='
				CHtml::link(
					"<i id=\"icon-status\" class=\"".strtolower(str_replace(" ","_",$data["status_io"]))."\"></i>",
					array(),
    				array("data-toggle"=>"tooltip", "data-original-title"=>"Invoice", "class"=>"linkinvoiced",  
    					"onclick" => 
    					"js:bootbox.prompt(\"Are you sure?\", function(confirmed){
    						if(confirmed!==null){
		    					$.post(\"invoice\",{ \"io_id\": ".$data["id"].", \"period\":\"'.$year.'-'.$month.'-01\",  \"invoice_id\": confirmed })
		                            .success(function( data ) {
			                            alert(data );
			                            window.location = document.URL;
		                            });
								}
							 })
						")


					);
				';
else 
	$buttonsColumn.='
				CHtml::link(
					"<i style=\"cursor:default\" id=\"icon-status\" class=\"".strtolower(str_replace(" ","_",$data["status_io"]))."\"></i>",
					array(),
    				array("class"=>"no-link", "data-toggle"=>"tooltip", "data-original-title"=>"Invoice")


					);
				';
if (FilterManager::model()->isUserTotalAccess('clients.validateOpportunity'))
	$buttonValidate='$data["status_opp"] == false ?
				CHtml::link(
					"<i class=\"not_verifed\" ></i>",
					array("opportunityValidation?op=".$data["opportunitie_id"]."&month='.$month.'&year='.$year.'"),
    				array(
    					"class"=>"link", 
    					"data-toggle"=>"tooltip", 
    					"data-original-title"=>"Not Verified",
				    	"id" => "icon-status-opp-".$data["id"],
    					)
					)
				: 
					"<i id=\"icon-status\" class=\"icon-status verifed\"></i>"
				;';
else 
	$buttonValidate='$data["status_opp"] == false ?
				CHtml::link(
					"<i style=\"cursor:default\" class=\"not_verifed\" ></i>",
					array(""),
    				array(
    					"class"=>"no-link", 
    					"data-toggle"=>"tooltip", 
    					"data-original-title"=>"Not Verified",
				    	"id" => "icon-status-opp-".$data["id"],
    					)
					)
				: 
					"<i id=\"icon-status\" class=\"icon-status verifed\"></i>"
				;';
*/
?>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		'id'                   =>'date-filter-form',
		'type'                 =>'search',
		'htmlOptions'          =>array('class'=>'well'),
		// to enable ajax validation
		'enableAjaxValidation' =>false,
		'action'               => Yii::app()->getBaseUrl() . '/finance/clients',
		'method'               => 'GET',
		'clientOptions'        =>array('validateOnSubmit'=>true, 'validateOnChange'=>true),
    )); ?> 

	<fieldset>
		<?php
			$months[0]	='Select a month';
			$months[1]  ='January';
			$months[2]  ='February';
			$months[3]  ='March';
			$months[4]  ='April';
			$months[5]  ='May';
			$months[6]  ='June';
			$months[7]  ='July';
			$months[8]  ='August';
			$months[9]  ='September';
			$months[10] ='October';
			$months[11] ='November';
			$months[12] ='December';
			$years[0]   ='Select a year';
			foreach (range(date('Y'), 2014) as $y) {
				$years[$y]=$y;
			}

			$entities=KHtml::enumItem(new FinanceEntities,'entity');
			$entities[0]='All Entities';
			$categories=KHtml::enumItem(new Advertisers,'cat');
			$categories[0]='All Categories';
			$status=KHtml::enumItem(new IosValidation,'status');
			foreach ($status as $key => $value) {
				if($value=='Approved' || $value=='Expired')unset($status[$key]);
			}
			$status['ok']='Approved/Expired';
			$status['Not Sent']='Not Sent';
			$status[0]='All Status';
			echo $form->dropDownList(new DailyReport,'date',$months,array('name'=>'month', 'style'=>'width:15%;', 'options' => array(intval($month)=>array('selected'=>true))));
			echo $form->dropDownList(new DailyReport,'date',$years,array('name'=>'year', 'style'=>'width:15%; margin-left:1em;','options' => array($year=>array('selected'=>true))));
			echo $form->dropDownList(new FinanceEntities,'entity',$entities,array('name'=>'entity', 'style'=>'width:15%; margin-left:1em;','options' => array(isset($_GET['entity']) ? $_GET['entity'] : 0=>array('selected'=>true))));
			echo $form->dropDownList(new Advertisers,'cat',$categories,array('name'=>'cat', 'style'=>'width:15%; margin-left:1em;','options' => array(isset($_GET['cat']) ? $_GET['cat'] : 0=>array('selected'=>true))));
			echo $form->dropDownList(new IosValidation,'status',$status,array('name'=>'status', 'style'=>'width:15%; margin-left:1em;','options' => array(isset($_GET['status']) ? $_GET['status'] : 0=>array('selected'=>true))));
		
	?>
    <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Filter', 'htmlOptions' => array('class' => 'showLoading'))); ?>
	<?php /*$this->widget('bootstrap.widgets.TbButton', array(
		'type'        => 'info',
		'label'       => 'Excel Report',
		'block'       => false,
		'buttonType'  => 'ajaxButton',
		'url'         => 'excelReport?month='.$month.'&year='.$year.'&entity='.$entity.'&status='.$stat,
		'ajaxOptions' => array(
			'type'    => 'POST',
			'beforeSend' => 'function(data)
				{
			    	var dataInicial = "<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"'.  Yii::app()->theme->baseUrl .'/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>";
					$("#modalClients").html(dataInicial);
					$("#modalClients").modal("toggle");
				}',
			'success' => 'function(data)
				{
					$("#modalClients").html(data);
				}',
			),
		'htmlOptions' => array('id' => 'excelReport','style'=>'float:right'),
		)
	);*/ ?>
	</div>
    </fieldset>

<?php $this->endWidget(); ?>
<?php 
	$this->widget('yiibooster.widgets.TbExtendedGridView', array(
	'id'              => 'clients-grid',
	'dataProvider'    => $dataProvider,
	//'filter'          => $model,
	'afterAjaxUpdate' =>'verifedIcon',
	'type'            => 'condensed',
	//'template'        => '{items} {pager} {summary}',
	'columns'         => array(		
        array(
            'class'=>'bootstrap.widgets.TbRelationalColumn',
            'name' => 'finance_entity',
            //'htmlOptions'       => array('id'=>'alignLeft'),
			'url' => $this->createUrl('/finance/clientDetails', array('year'=>$year, 'month' => $month)),
		),
		array(
			'type'              => 'raw',
			'header'            => '',
			'sortable'		    => false,
			'filter'            => false,
			'name'              => 'mr',
			'headerHtmlOptions' => array('width' => '20'),
			'name'              =>	'name',
			'value'             =>'$data["status_adv"] == false ?
				CHtml::link(
					"<i id=\"icon-status-".$data["fe_id"]."\" class=\"not_verifed\" data_id=\"".$data["fe_id"]."\"></i>",
					array("finance/validateOpportunitiesByAdvertiser/".$data["fe_id"]."?period='.$year.'-'.$month.'-01"),
					array(
						"data_name" => $data["advertisers_name"],
						"onclick" => "validateAdv(this);return false;"
						)
					)
				:
				"<i class=\"icon-status verifed\" data_id=\"".$data["fe_id"]."\"></i>"
				',		
		),
		/*
		array(
			'name'              => 'opportunitie',
			'value'             => '$data["opportunitie"]',	
			'htmlOptions'       => array('id'=>'alignLeft'),
			'header'            => 'Opportunity',                           
			),	
		array(
			'name'              =>'model',
			'value'             =>'$data["model"]',	
			'headerHtmlOptions' => array('width' => '80'),
			'header'            =>'Model',		
			),
		array(
			'name'              =>'entity',
			'value'             =>'$data["entity"]',
			'headerHtmlOptions' => array('width' => '80'),	
			'header'            =>'Entity',    
			),	
		array(
			'name'              =>'currency',
			'value'             =>'$data["currency"]',
			'headerHtmlOptions' => array('width' => '80'),		
			'header'            =>'Currency',	
			),
		array(
			'name'              =>'rate',
			'value'             =>'$data["multi"] === 1 ? $data["rate"] : "Multi"',
			'headerHtmlOptions' => array('width' => '80'),
			'htmlOptions'       => array('style'=>'text-align:right;'),	
			'header'            =>'Rate',	
		),	
		array(
			'name'              => 'mr',
			'header'			=> false,
			'filter'			=> false,
			'headerHtmlOptions' => array('class'=>'plusMR'),
			'filterHtmlOptions' => array('class'=>'plusMR'),
			'htmlOptions'       => array('class'=>'plusMR'),
			'type'              => 'raw',
			'value'             =>	'
				$data["multi"] === 0 ?
				CHtml::link(
					"<i class=\"icon-plus\"></i>",
					array("multiRate?id=" . $data["opportunitie_id"] ."&month='.$month.'&year='.$year.'"),
    				array("class"=>"link", "data-toggle"=>"tooltip", "data-original-title"=>"View Rate")
					)
				: null
				'
				,
        ),
        
		array(
			'name'              =>'opp_vals',				
			'headerHtmlOptions' => array('width' => '80'),	
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),       
		array(
			'name'              =>'opp_count',				
			'headerHtmlOptions' => array('width' => '80'),	
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),
		*/		
		array(
			'name'              =>'imp',				
			'headerHtmlOptions' => array('width' => '80'),	
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),
		array(
			'name'              =>'clics',
			'headerHtmlOptions' => array('width' => '80'),	
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),		
		array(
			'name'              =>'conv_api',

			'headerHtmlOptions' => array('width' => '80'),	
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),		
		array(
			'name'              =>'revenue',
			'header'            =>'Revenue',
			'value'             =>'number_format($data["revenue"],2)',
			'headerHtmlOptions' => array('width' => '80'),
			'htmlOptions'       => array('style'=>'text-align:right;'),		
		),
		/*
		array(
			'type'              =>'raw',
			'header'            =>'',
			'sortable'		    => false,
			'filter'            => false,
			'headerHtmlOptions' => array('width' => '20'),
			'name'              => 'opportunitie',
			'value'             => $buttonValidate,		
		),
		*/
		array(
			'name'              =>'total',
			'header'            =>'Total Revenue',
			'filter'			=>false,
			'value'             =>'number_format($data["total"],2)',
			'headerHtmlOptions' => array('width' => '80'),
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),

		array(
			'type'              => 'raw',
			'header'            => '',
			'sortable'		    => false,
			'filter'            => false,
			'headerHtmlOptions' => array('width' => '20'),
			'name'              => 'name',
			'value'             =>'
				CHtml::link(
					"<i class=\"icon-pencil\"></i>",
					array(
						"finance/transaction", 
						"id"     => $data["fe_id"],
						"period" => "'.$year.'-'.$month.'-01",
						),
    				array(
    					"class"=>"openModal",
    					"data-modal-id"=>"modalClients", 
    					"data-modal-title"=>"Transaction Count", 
    					"data-toggle"=>"tooltip", 
    					"data-original-title"=>"Count",
    					"onclick"=>"event.preventDefault(); openModal(this)",
    					)
					);
				',		
		),
		/*
		array(
			'name'              =>'name',
			'header'            =>'Total Transaction',
			'filter'			=>false,
			'value'             =>'number_format($data["total_transaction"],2)',
			'headerHtmlOptions' => array('width' => '80'),
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),
		array(
			'name'              =>'name',
			'header'            =>'Total',
			'filter'			=>false,
			'value'             =>'number_format($data["total"],2)',
			'headerHtmlOptions' => array('width' => '80'),
			'htmlOptions'       => array('style'=>'text-align:right;'),	
		),
		*/
		/*
		array(
			'type'              =>'raw',
			'header'            =>'',
			'sortable'		    => false,
			'filter'            => false,
			'headerHtmlOptions' => array('width' => '5'),
			'name'              =>'name',
			'htmlOptions'		=>array('style'=>'text-align:left !important'),
			'value'             =>$buttonsColumn,		
		), 
		*/
		/*
		array(
			'type'              =>'raw',
			'header'            =>'',
			'sortable'		    => false,
			'filter'            => false,
			'headerHtmlOptions' => array('width' => '5'),
			'name'              =>'name',
			'htmlOptions'		=>array('style'=>'text-align:left !important'),
			'value'             =>'$data["comment"] ? CHtml::link("<i class=\"icon-info-sign\" style=\"cursor:default\"></i>","javascript:void(0)", array(
							    "class" => "ipopover",
							    "data-trigger" => "hover",
							    "data-content" => $data["comment"],
							)
						) : null;',		
		),
		*/ 
	),
)); ?>

<?php $this->beginWidget('bootstrap.widgets.TbModal', 
	array('id'=>'modalClients')); ?>

		<div class="modal-header"></div>
        <div class="modal-body"></div>
        <div class="modal-footer"></div>

<?php $this->endWidget(); ?>

<div class="row" id="blank-row">
</div>

<?php 
Yii::app()->clientScript->registerScript('verifedIcon', "
					$('.linkinvoiced').click(function(e){
                        e.preventDefault();
                    });
                        
					function verifedIcon(){
                        $('.link').click(function(e){
                            e.preventDefault();
                            var that = $(this);
							var link = that.attr('href');

							var dataInicial = '<div class=\"modal-header\"></div><div class=\"modal-body\" style=\"padding:100px 0px;text-align:center;\"><img src=\"".  Yii::app()->theme->baseUrl ."/img/loading.gif\" width=\"40\" /></div><div class=\"modal-footer\"></div>';
							$('#modalClients').html(dataInicial);
							$('#modalClients').modal('toggle');
                           $.post( link, {})
								.success(function( data ) {
									$('#modalClients').html(data);
									//Error en modal, se cerraba luego de abrirse. Ver con Santi.
									//$('#modalClients').modal('toggle');
                                }

					
                                );
                            
                        });
					}
                    ", CClientScript::POS_READY);  
?>