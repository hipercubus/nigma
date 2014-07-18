<?php
/* @var $this CampaignsController */
/* @var $model Campaigns */
/* @var $form CActiveForm */
/*
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'model' => $model,
    'attribute' => 'date_start',
    'options' => array(
        'dateFormat' => 'dd-mm-yy'
    ),
    'htmlOptions' => array(
        'class' => 'span2'
    ),
));
*/
?>

<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h4>Update campaign #<?php echo $model->id ?></h4>
</div>

<div class="modal-body">

    <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
        'id'=>'campaigns-form',
        'type'=>'horizontal',
        'htmlOptions'=>array('class'=>'well'),
    )); ?>

    <fieldset>
        <?php 


        $categories = array('Games', 'Adult', 'Autos');
        $offer_type = array('VAS', 'App Owners', 'Branding', 'Lead Generation');
        $currency = array('Peso', 'Dolar', 'Euro', 'Real');
        $budget_type = array(
            array('label'=>'Open'), 
            array('label'=>'Fixed'), 
            array('label'=>'Payment'), 
        );
        $status = array('Active', 'Paused', 'Inactive');

        echo $form->textFieldRow($model, 'name', array('class'=>'span4'));
        echo $form->radioButtonListInlineRow($model, 'status', $status);
        echo '<hr/>';
        echo $form->dropDownListRow($model, 'campaign_categories_id', $categories);
        echo $form->dropDownListRow($model, 'offer_type', $offer_type);
        echo $form->dropDownListRow($model, 'currency', $currency);
        echo '<hr/>';
        ?>
        <div class="control-group ">
            <label class="control-label required" for="Campaigns_name">Budget Type <span class="required">*</span>
            </label>
            <div class="controls">
            <?php
                $this->widget('bootstrap.widgets.TbButtonGroup', array(
                    'type' => 'primary',
                    'toggle' => 'radio',
                    'buttons' => $budget_type,
                ));
            ?>
            </div>
        </div>
        <?php
        
        echo $form->textFieldRow($model, 'budget', array('prepend'=>'$'));
        echo $form->textFieldRow($model, 'cap', array('prepend'=>'$'));
        echo $form->radioButtonListInlineRow($model, 'model', array('CPC','CPM'));
        echo $form->textFieldRow($model, 'bid', array('prepend'=>'$'));
        echo '<hr/>';
        ?>

        <div class="control-group ">
            <label class="control-label required" for="Campaigns_name">Start Date <span class="required">*</span>
            </label>
            <div class="controls">
            <?php
                $this->widget('ext.rezvan.RDatePicker',array(
                    'model' => $model,
                    'attribute' => 'date_start',
                    'options' => array(
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'placement' => 'right',
                    ),
                    'htmlOptions'=>array(
                        'class'=>'span2'
                    )
                ));
            ?>
            </div>
        </div>
        <div class="control-group ">
            <label class="control-label required" for="Campaigns_name">End Date <span class="required">*</span>
            </label>
            <div class="controls">
            <?php
                $this->widget('ext.rezvan.RDatePicker',array(
                    'model' => $model,
                    'attribute' => 'date_end',
                    'options' => array(
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'placement' => 'right',
                    ),
                    'htmlOptions'=>array(
                        'class'=>'span2'
                    )
                ));
            ?>
            </div>
        </div>

        <?php
        echo '<hr/>';
        echo $form->textAreaRow($model, 'comment', array('class'=>'span4', 'rows'=>5)); 

        ?>
        <div class="row">
    </div>
            <?php //echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
    <div class="form-actions">
        <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'type'=>'primary', 'label'=>'Submit')); ?>
        <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'reset', 'label'=>'Reset')); ?>
    </div>
    </fieldset>

</div>

<div class="modal-footer">
    Edit campaign attributes. Fields with <span class="required">*</span> are required.
</div>

    <?php $this->endWidget(); ?>
