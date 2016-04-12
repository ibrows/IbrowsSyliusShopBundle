<?php

namespace Ibrows\SyliusShopBundle\Cart;

use Ibrows\SyliusShopBundle\Model\Product\ProductInterface;
use Sylius\Bundle\InventoryBundle\Model\StockableInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Sylius\Bundle\CartBundle\Model\CartItemInterface;
use Sylius\Bundle\CartBundle\Resolver\ItemResolverInterface;
use Sylius\Bundle\CartBundle\Resolver\ItemResolvingException;
use Sylius\Bundle\InventoryBundle\Checker\AvailabilityCheckerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class BaseItemResolver implements ItemResolverInterface
{
    /**
     * Product manager.
     *
     * @var ObjectRepository
     */
    private $productRepository;

    /**
     * Form factory.
     *
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Stock availability checker.
     *
     * @var AvailabilityCheckerInterface
     */
    private $availabilityChecker;

    /**
     * @param RegistryInterface            $registry
     * @param string                       $classname
     * @param FormFactory                  $formFactory
     * @param AvailabilityCheckerInterface $availabilityChecker
     */
    public function __construct(RegistryInterface $registry, $classname, FormFactory $formFactory, AvailabilityCheckerInterface $availabilityChecker)
    {
        $manager = $registry->getManagerForClass($classname);
        $this->productRepository = $manager->getRepository($classname);
        $this->formFactory = $formFactory;
        $this->availabilityChecker = $availabilityChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Here we create the item that is going to be added to cart, basing on the current request.
     * This method simply has to return false value if something is wrong.
     */
    public function resolve(CartItemInterface $item, Request $request)
    {
        /*
         * We're getting here product id via query but you can easily override route
         * pattern and use attributes, which are available through request object.
         */
        if (!$id = $request->query->get('id')) {
            throw new ItemResolvingException('Error while trying to add item to cart');
        }

        /* @var ProductInterface $product */
        if (!$product = $this->productRepository->find($id)) {
            throw new ItemResolvingException('Requested product was not found');
        }

        if (!$request->isMethod('POST')) {
            throw new ItemResolvingException('Wrong request method');
        }

        // We use forms to easily set the quantity and pick variant but you can do here whatever is required to create the item.
        $form = $this->formFactory->create('sylius_cart_item', null, array('data_class' => 'Ibrows\SyliusShopBundle\Entity\CartItem')); //'product' => $product

        $form->bind($request);
        $item = $form->getData(); // Item instance, cool.

        // If all is ok with form, quantity and other stuff, simply return the item.
        if ($form->isValid()) {
            if (null === $product || !$product->isEnabled()) {
                throw new ItemResolvingException('Requested product was not found or is disabled');
            }

            $this->isStockAvailable($product);
            $this->isStockSufficient($product, $item->getQuantity());
            $item->setProduct($product);

            return $item;
        }

        throw new ItemResolvingException('Submitted form is invalid');
    }

    /**
     * @param $stockable
     * @param $quantity
     *
     * @throws ItemResolvingException
     */
    private function isStockSufficient(StockableInterface $stockable, $quantity)
    {
        if (!$this->availabilityChecker->isStockSufficient($stockable, $quantity)) {
            throw new ItemResolvingException('Selected item has not enough');
        }
    }

    /**
     * @param StockableInterface $stockable
     *
     * @throws ItemResolvingException
     */
    private function isStockAvailable(StockableInterface $stockable)
    {
        if (!$this->availabilityChecker->isStockAvailable($stockable)) {
            throw new ItemResolvingException('Selected item is out of stock');
        }
    }
}
