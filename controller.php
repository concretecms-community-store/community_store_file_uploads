<?php

namespace Concrete\Package\CommunityStoreFileUploads;

use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\BlockType\Set as BlockTypeSet;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Support\Facade\Route;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreProductKey;
use Whoops\Exception\ErrorException;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Support\Facade\Application as ApplicationFacade;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_file_uploads';
    protected $appVersionRequired = '8.4';
    protected $pkgVersion = '1.0';
    protected $packageDependencies = ['community_store'=>'2.0'];

    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStoreFileUploads\Src\CommunityStore',
    ];

    public function getPackageDescription()
    {
        return t("Community Store File Uploads");
    }

    public function getPackageName()
    {
        return t("Community Store File Uploads");
    }

    public function install()
    {
        $app = ApplicationFacade::getFacadeApplication();
        $pkg = parent::install();

        $orderCategory = Category::getByHandle('store_order');
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('image_file'));

        $blockType = BlockType::getByHandle('community_store_file_upload');
        if (!is_object($blockType)) {
            BlockType::installBlockType('community_store_file_upload', $pkg);
        }

        $boolean = AttributeType::getByHandle('boolean');
        $text = AttributeType::getByHandle('text');

        $productCatgegory = $app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');

        $attr = $productCatgegory->getAttributeKeyByHandle('file_upload');

        if (!is_object($attr)) {
            $key = new StoreProductKey();
            $key->setAttributeKeyHandle('file_upload');
            $key->setAttributeKeyName(t('File Upload'));
            $key = $productCatgegory->add($boolean, $key, null, $pkg);
        }

        $attr = $productCatgegory->getAttributeKeyByHandle('file_upload');

        if (!is_object($attr)) {
            $key = new StoreProductKey();
            $key->setAttributeKeyHandle('file_upload_label');
            $key->setAttributeKeyName(t('File Upload Label'));
            $key = $productCatgegory->add($text, $key, null, $pkg);
        }

    }
}
