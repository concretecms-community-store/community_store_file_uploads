<?php defined('C5_EXECUTE') or die("Access Denied."); ?>


<?php if (\Concrete\Core\Page\Page::getCurrentPage()->isEditMode()) { ?>
    <div class="ccm-edit-mode-disabled-item"><?= t('File Uploads'); ?></div>
<?php } else { ?>
<form class="store-fileupload" method="post" enctype="multipart/form-data" action="<?= $offerUploads ? $view->action('upload') : $view->action('find'); ?>">
    <?= $token->output('community_store'); ?>

    <?php

    $fieldcount = 0;

    if ($offerUploads) { ?>

        <h2><?= t('Upload Files for Order #%s', $order->getOrderID()); ?></h2>

        <?php if (isset($actions)) { ?>
            <?= $successText; ?>
        <?php } else { ?>
            <?= $promptText; ?>
        <?php } ?>

        <table class="table table-striped" id="store-fileupload-table">
            <thead>
            <tr>
                <th><strong><?= t("Item"); ?></strong></th>
                <th class=""><?= t("File Uploads"); ?></th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($fields as $field) { ?>
                <tr>
                    <?php
                    $file = $field['file'];
                    $item = $field['item'];
                    ?>

                    <td><p><?= h($item->getProductName()) ?>
                        <?php if ($sku = $item->getSKU()) {
                            echo '(' . h($sku) . ')';
                        } ?>
                        </p>

                        <?php
                        $options = $item->getProductOptions();
                        if ($options) {
                            echo "<ul class='list-unstyled'>";
                            foreach ($options as $option) { ?>
                                <li><strong><?= h($option['oioKey']); ?></strong> <?= h($option['oioValue']) ? h($option['oioValue']) : '<em>' . t('None') . '</em>'; ?></li>
                            <?php }
                            echo "</ul>";
                        }
                        ?>

                    </td>

                    <td>
                        <div class="form-group">
                            <?php if ($file || ($file && $allowReplacing)) { ?>
                            <label class="control-label" for="<?= h($field['field']); ?>">
                                <?php if ($file && $allowReplacing) { ?>
                                    <?= t('Replace'); ?>
                                <?php } ?>

                                <?php if ($file) { ?>
                                    <em><?= h($file->getTitle()); ?></em>
                                <?php } ?>
                            </label>
                            <?php } ?>
                            <?php if (!$file || $allowReplacing) { ?>
                                <input type="file" class="form-control" name="<?= h($field['field']); ?>" id="<?= h($field['field']); ?>">

                                <?php $fieldcount++;
                            } ?>

                            <?php if ($field['label']) { ?>
                            <span class="help-block"><?= h($field['label']); ?></span>
                            <?php } ?>

                        </div>
                    </td>

                </tr>

            <?php } ?>
        </table>

        <?php if ($fieldcount > 0) { ?>
            <p class="text-right"><button type="submit" class="btn btn-primary"><?= $buttonLabel ? h($buttonLabel) : t('Upload') ?></button></p>
        <?php } else { ?>
            <p class="alert alert-info"><?= $completedText ? h($completedText) : t('All files have been uploaded'); ?></p>
            <style>
                #store-fileupload-table {
                    display: none;
                }
            </style>
        <?php } ?>

    <?php } elseif ($allowSearching) { ?>

        <h2><?= t('Find Order'); ?></h2>

        <?php if (isset($notFound)) { ?>
            <p class="alert alert-danger"><?= t('No order was found matching that order number and email'); ?></p>
        <?php } ?>

        <div class="form-group">
            <label class="control-label" for="email"><?= t('Order Number'); ?></label>
            <input type="number" name="order_number" <?php if (isset($submittedOrderNumber)) { ?> value="<?= h($submittedOrderNumber); ?>"<?php } ?> required class="form-control"/>
        </div>

        <div class="form-group">
            <label class="control-label" for="email"><?= t('Email'); ?></label>
            <input type="email" name="email" <?php if (isset($submittedEmail)) { ?> value="<?= h($submittedEmail); ?>"<?php } ?> required class="form-control"/>
        </div>

        <button type="submit" class="btn btn-primary"><?= t('Find Order') ?></button>

    <?php } ?>

</form>
<?php } ?>
