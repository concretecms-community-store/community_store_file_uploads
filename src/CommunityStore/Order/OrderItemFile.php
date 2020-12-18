<?php
namespace Concrete\Package\CommunityStoreFileUploads\Src\CommunityStore\Order;

use Concrete\Core\File\File;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderItemFiles")
 */
class OrderItemFile
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oifID;


    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem")
     * @ORM\JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order")
     * @ORM\JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;


    /**
     * @ORM\Column(type="integer")
     */
    protected $fID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $quantityCount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $uploaded;

    /**
     * @return mixed
     */
    public function getOifID()
    {
        return $this->oifID;
    }

    /**
     * @param mixed $oifID
     */
    public function setOifID($oifID)
    {
        $this->oifID = $oifID;
    }

    /**
     * @ORM\return mixed
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @ORM\param mixed $orderItem
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }


    /**
     * @ORM\return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @ORM\param mixed $orderItem
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }


    /**
     * @return mixed
     */
    public function getFID()
    {
        return $this->fID;
    }

    /**
     * @return mixed
     */
    public function getQuantityCount()
    {
        return $this->quantityCount;
    }

    /**
     * @param mixed $quantityCount
     */
    public function setQuantityCount($quantityCount)
    {
        $this->quantityCount = $quantityCount;
    }


    /**
     * @param mixed $fID
     */
    public function setFID($fID)
    {
        $this->fID = $fID;
    }

    /**
     * @return mixed
     */
    public function getUploaded()
    {
        return $this->uploaded;
    }

    /**
     * @param mixed $uploaded
     */
    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    public function getFile() {
        return File::getByID($this->getFID());
    }


    public function save()
    {
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function getByOrder($order)
    {
        $em = dbORM::entityManager();
        return $em->getRepository(get_class())->findBy(['order' => $order]);
    }

    public static function getByOrderItem($orderItem, $quantityCount = 1)
    {
        $em = dbORM::entityManager();
        return $em->getRepository(get_class())->findOneBy(['orderItem' => $orderItem, 'quantityCount' => $quantityCount]);
    }

    public static function getAllByOrderItem($orderItem)
    {
        $em = dbORM::entityManager();
        return $em->getRepository(get_class())->findBy(['orderItem' => $orderItem]);
    }
}
