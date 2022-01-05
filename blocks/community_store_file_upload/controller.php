<?php

namespace Concrete\Package\CommunityStoreFileUploads\Block\CommunityStoreFileUpload;

use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Block\BlockController;
use Concrete\Core\File\File;
use Concrete\Core\File\Filesystem;
use Concrete\Core\File\Import\FileImporter;
use Concrete\Core\File\Set\Set;
use Concrete\Core\Support\Facade\Application as ApplicationFacade;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStoreFileUploads\Src\CommunityStore\Order\OrderItemFile;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreFileUpload';
    protected $btInterfaceWidth = "650";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "600";
    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add customer file uploading");
    }

    public function getBlockTypeName()
    {
        return t("File Upload");
    }

    public function add()
    {
        $app = ApplicationFacade::getFacadeApplication();
        $this->set('app', $app);
    }

    public function edit()
    {
        $app = ApplicationFacade::getFacadeApplication();
        $this->set('app', $app);
    }

    public function save($args)
    {
        if (!is_numeric($args['replacingHours']) || $args['replacingHours'] < 1) {
            $args['replacingHours'] = 0;
        }

        parent::save($args);
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
    }

    public function action_upload($bID = false)
    {
        $app = ApplicationFacade::getFacadeApplication();

        if ($this->bID != $bID) {
            return false;
        }

        $order = $this->getLastOrder();

        if (!$order) {
            return false;
        }

        $request = $this->app->make(\Concrete\Core\Http\Request::class);
        $count = 0;

        $actions = [];

        $fields = $this->getOrderUploadFields($order);
        $availableFields = [];

        foreach ($fields as $f) {
            $availableFields[ $f['field']] = $f;
        }

        $token = $this->app->make('token');

        if ($token->validate('community_store')) {
            foreach ($request->files as $key => $file) {
                // if one of the actual available file uploads for the order

                if (key_exists($key, $availableFields)) {
                    try {
                        $fv = $this->processFile($request->files->get($key));

                        $orderItemFile = OrderItemFile::getByOrderItem($availableFields[$key]['item'], $availableFields[$key]['count']);

                        if ($orderItemFile) {
                            $existingFile = $orderItemFile->getFile();
                            if ($existingFile) {
                                $existingFile->delete();
                            }
                            $actions[$key] = 'replaced';
                        } else {
                            $actions[$key] = 'uploaded';
                            $orderItemFile = new OrderItemFile();
                        }

                        $orderItemFile->setOrder($order);
                        $orderItemFile->setOrderItem($availableFields[$key]['item']);
                        $orderItemFile->setFID($fv->getFileID());
                        $orderItemFile->setUploaded(new \DateTime);
                        $orderItemFile->setQuantityCount($availableFields[$key]['count']);
                        $orderItemFile->save();

                        $count++;

                    } catch (\Concrete\Core\File\Import\ImportException $x) {
                        // Manage the import exception
                    }
                }
            }
        }

        if ($count > 0 && trim($this->recipientEmail)) {

            $mh = $app->make('helper/mail');

            $notificationEmails = explode(",", trim($this->recipientEmail));
            $notificationEmails = array_map('trim', $notificationEmails);
            $notificationEmails = array_unique($notificationEmails);

            $mh->addParameter('order', $order);

            $mh->load('order_upload_notification', 'community_store_file_uploads');

            $fromName = Config::get('community_store.emailalertsname');
            $fromEmail = Config::get('community_store.emailalerts');
            if (!$fromEmail) {
                $fromEmail = "store@" . str_replace('www.', '', $request->getHost());
            }

            if ($fromName) {
                $mh->from($fromEmail, $fromName);
            } else {
                $mh->from($fromEmail);
            }

            $validNotification = false;

            foreach ($notificationEmails as $notificationEmail) {
                if ($notificationEmail) {
                    $mh->to($notificationEmail);
                    $validNotification = true;
                }
            }

            if ($validNotification) {
                try {
                    $mh->sendMail();
                } catch (\Exception $e) {
                    \Log::addWarning(t('Community Store: a order upload notification failed sending to %s, with error %s', implode(', ', $notificationEmails), $e->getMessage()));
                }
            }
        }

        $this->set('actions', $actions);
        $this->view((bool)$order);
    }

    public function action_find($bID = false)
    {
        $foundOrder = false;
        $token = $this->app->make('token');

        if ($this->bID == $bID && $this->allowSearching) {

            $request = $this->app->make(\Concrete\Core\Http\Request::class);

            if ($token->validate('community_store')) {
                $email = trim($request->request('email'));
                $orderNumber = trim($request->request('order_number'));

                $this->set('submittedEmail', $email);
                $this->set('submittedOrderNumber', $orderNumber);

                $order = false;

                if ($email && $orderNumber) {
                    $order = Order::getByID($orderNumber);

                    if ($order && $order->getAttribute('email') != $email) {
                        $order = false;
                    }
                }

                if (!$order) {
                    $this->set('notFound', true);
                } else {
                    $foundOrder = true;
                    Session::set('community_foundOrderID', $order->getOrderID());
                }
            }
        }

        $this->view($foundOrder);
    }

    private function getLastOrder()
    {
        $customer = new Customer();
        $lastorderid = $customer->getLastOrderID();
        $order = false;

        if ($lastorderid) {
            $order = Order::getByID($customer->getLastOrderID());
        }

        return $order;
    }

    public function view($foundOrder = false)
    {
        $order = false;

        if ($this->allowSearching) {
            $orderID = Session::get('community_foundOrderID');

            if ($orderID) {
                $order = Order::getByID($orderID);
            }
        }

        if (!$order) {
            $order = $this->getLastOrder();
        }

        $this->set('order', $order);

        if ($order) {
            $fields = $this->getOrderUploadFields($order);
        } else {
            $fields = [];
        }

        $offerUploads = false;

        if ($this->allowSearching  ) {
            $offerUploads = $foundOrder;
        }  else {
            $offerUploads = $order && !empty($fields);
        }

        $allowReplacing = $this->allowReplacing;

        if ($order && $this->replacingHours > 0) {
            $now = new \DateTime();
            $placed = $order->getOrderDate();
            $diff = $placed->diff($now)->h;

            if ($diff > $this->replacingHours) {
                $allowReplacing = false;
            }
        }

        $this->set('allowReplacing', $allowReplacing);
        $this->set('offerUploads', $offerUploads);
        $this->set('foundOrder', $foundOrder);
        $this->set('fields', $fields);
        $this->set('token', $this->app->make('token'));
    }


    private function getOrderUploadFields($order)
    {
        $fields = [];

        foreach ($order->getOrderItems() as $item) {
            $product = $item->getProductObject();

            if ($product) {
                if ($product->getAttribute('file_upload')) {
                    $label = $product->getAttribute('file_upload_label');

                    $multiplier = $product->getAttribute('file_upload_number_uploads');

                    if (!($multiplier > 0)) {
                        $multiplier = 1;
                    }

                    $quantity = $item->getQuantity() * $multiplier;

                    if ($quantity == 1) {
                        $file = false;
                        $orderItemFile = OrderItemFile::getByOrderItem($item, 1);
                        if ($orderItemFile) {
                            $file = File::getByID($orderItemFile->getFID());
                        }

                        $fields[] = ['label' => $label, 'field' => 'order_item_' . $item->getID(), 'type' => 'order_item', 'item'=>$item, 'file'=>$file , 'count' => 1, 'quantity'=>$quantity];
                    } else {

                        for ($i = 1; $i <= $quantity; $i++) {

                            $file = false;
                            $orderItemFile = OrderItemFile::getByOrderItem($item, $i);
                            if ($orderItemFile) {
                                $file = File::getByID($orderItemFile->getFID());
                            }

                            $fields[] = ['label' => $label, 'field' => 'order_item_' . $item->getID() . '_' . $i, 'type' => 'order_item', 'item'=>$item, 'file'=>$file, 'count' => $i, 'quantity'=>$quantity ];
                        }

                    }
                }

            }
        }

        return $fields;
    }

    private function processFile($fileUpload)
    {
        $importer = $this->app->make(FileImporter::class);

        $set = null;
        $folder = null;
        $filesystem = new Filesystem();
        $rootFolder = $filesystem->getRootFolder();
        if ($this->addFilesToSet) {
            $set = Set::getByID($this->addFilesToSet);
        }
        if ($this->addFilesToFolder) {
            $folder = $filesystem->getFolder($this->addFilesToFolder);
        }

        $file = $importer->importUploadedFile($fileUpload);

        if ($set) {
            $set->addFileToSet($file);
        }
        if ($folder && $folder->getTreeNodeID() != $rootFolder->getTreeNodeID()) {
            $fileNode = $file->getFile()->getFileNodeObject();
            if ($fileNode) {
                $fileNode->move($folder);
            }
        }

        return $file;
    }
}
