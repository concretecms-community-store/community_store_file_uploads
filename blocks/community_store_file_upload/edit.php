<?php defined('C5_EXECUTE') or die("Access Denied."); ?>


<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="hidden" value="0" name="allowSearching" />
            <?= $form->checkbox('allowSearching', 1, isset($allowSearching) ? $allowSearching : false); ?>
            <?= t('Allow Searching for Orders'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="hidden" value="0" name="allowReplacing" />
            <?= $form->checkbox('allowReplacing', 1, isset($allowReplacing) ? $allowReplacing : false); ?>
            <?= t('Allow Replacing Existing Uploads'); ?>
        </label>
    </div>
</div>

<script>

    document.querySelector('#allowReplacing').addEventListener('change',function(){

        let checkbox = document.querySelector('#allowReplacing')
        if (checkbox.checked) {
            document.querySelector('#replacingHoursField').classList.remove('hidden')
        } else {
            document.querySelector('#replacingHoursField').classList.add('hidden')
        }

    });

</script>

<div class="form-group <?= ($allowReplacing ? '' : 'hidden'); ?>" id="replacingHoursField">
    <?= $form->label('replacingHours', t('Limit Duration Replacing Files Allowed')); ?>
    <div class="input-group">
    <?= $form->number('replacingHours', $replacingHours); ?>
    <div class="input-group-addon"><?= t('hours since order placed'); ?></div>
    </div>
    <span class="help-block"><?= t('Leave blank or 0 for no time restriction on replacing'); ?></span>
</div>

<fieldset>
    <legend><?=t('Labelling'); ?></legend>

    <div class="form-group">
        <?= $form->label('buttonLabel', t('Button Label')); ?>
        <?= $form->text('buttonLabel', $buttonLabel, ['placeholder'=>t('Upload')]); ?>
    </div>


    <div class="form-group">
        <label class="control-label"><?php echo t('Prompt Text'); ?></label>
        <?php
        $editor = $app->make('editor');
        echo $editor->outputStandardEditor('promptText', $promptText);
        ?>
    </div>


    <div class="form-group">
        <label class="control-label"><?php echo t('Success Text'); ?></label>
        <?php
        echo $editor->outputStandardEditor('successText', $successText);
        ?>
    </div>


    <div class="form-group">
        <?= $form->label('completedText', t('All Files Uploaded Text')); ?>
        <?= $form->text('completedText', $completedText, ['placeholder'=>t('All files have been uploaded')]); ?>
    </div>

</fieldset>


<fieldset>
    <legend><?=t('Files'); ?></legend>
    <div class="form-group">
        <label class="control-label" for="ccm-form-fileset"><?=t('Add uploaded files to a set?'); ?></label>
        <?php

        $fileSets = Concrete\Core\File\Set\Set::getMySets();
        $sets = [0 => t('None')];
        foreach ($fileSets as $fileSet) {
            $sets[$fileSet->getFileSetID()] = $fileSet->getFileSetDisplayName();
        }
        echo $form->select('addFilesToSet', $sets, $addFilesToSet);
        ?>
    </div>
    <div class="form-group">
        <label class="control-label"><?=t('Add uploaded files to folder'); ?></label>
        <?php
        $selector = new \Concrete\Core\Form\Service\Widget\FileFolderSelector();
        echo $selector->selectFileFolder('addFilesToFolder', $addFilesToFolder);
        ?>
    </div>
</fieldset>



<fieldset>
    <legend><?=t('Email Notification'); ?></legend>
    <div class="form-group">
        <?=$form->label('recipientEmail', t('Send uploads submission notifications to email addresses')); ?>
        <div class="input-group">
				<span class="input-group-addon" style="z-index: 2000">
				<?=$form->checkbox('emailNotification', 1, 1 == $emailNotification); ?>
				</span><?=$form->text('recipientEmail', $recipientEmail, ['autocomplete' => 'off', 'style' => 'z-index:2000;']); ?>
        </div>
        <span class="help-block"><?=t('(Separate multiple emails with a comma)'); ?></span>
    </div>
    <div data-view="form-options-email-reply-to"></div>
</fieldset>


