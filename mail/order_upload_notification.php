<?php
defined('C5_EXECUTE') or die("Access Denied.");

$locale = $order->getLocale();
if ($locale) {
    \Concrete\Core\Localization\Localization::changeLocale($locale);
}

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');
$csm = $app->make('cs/helper/multilingual');
$subject = t("Order #%s File(s) Uploaded", $order->getOrderID());

/*
 * HTML BODY START
 */
ob_start();

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<head>
</head>
<body>

<h3><?= t('Order #%s has had file uploads', $order->getOrderID() ); ?></h3>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Product Name') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Options') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('Qty') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('File Uploads') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $items = $order->getOrderItems();
    if ($items) {
        foreach ($items as $item) {
            $product = $item->getProductObject();
            if ($product && $product->getAttribute('file_upload')) {
            ?>
            <tr>
                <td style="vertical-align: top; padding: 5px 10px 5px 0"><?= $item->getProductName() ?>
                    <?php if ($sku = $item->getSKU()) {
                        echo '(' . $sku . ')';
                    } ?>
                </td>
                <td style="vertical-align: top; padding: 5px 10px 5px 0;">
                    <?php
                    $options = $item->getProductOptions();
                    if ($options) {
                        $optionOutput = array();
                        foreach ($options as $option) {
                            $optionOutput[] =  "<strong>" . $option['oioKey'] . ": </strong>" . ($option['oioValue'] ? $option['oioValue'] : '<em>' . t('None') . '</em>');
                        }
                        echo implode('<br>', $optionOutput);
                    }
                    ?>
                </td>
                <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right"><?= $item->getQuantity() ?> <?= h($item->getQuantityLabel());?></td>
                <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right">
                    <?php
                    $orderItemFiles = \Concrete\Package\CommunityStoreFileUploads\Src\CommunityStore\Order\OrderItemFile::getAllByOrderItem($item);

                    foreach ($orderItemFiles as $orderItemFile) {
                        $file = $orderItemFile->getFile();

                        if ($file) {
                            echo '<a href="' . $file->getForceDownloadUrl() . '">' . $file->getTitle() . '</a><br />';
                        }
                    }
                    ?>
                </td>

            </tr>
        <?php }
        }
    }
    ?>

</table>



<p><a href="<?= \Concrete\Core\Support\Facade\Url::to('/dashboard/store/orders/order/'. $order->getOrderID());?>"><?=t('View this order within the Dashboard');?></a></p>

</body>
</html>

<?php
$bodyHTML = ob_get_clean();
/*
 * HTML BODY END
 *
 */
?>
