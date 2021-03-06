<?php

namespace Kubithon\ShopBundle\Service;

use AppBundle\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class PaypalPaymentService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var ApiContext
     */
    private $apiContext;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Router $router)
    {
        $this->em = $em;
        $this->container = $container;
        $this->router = $router;

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->container->getParameter('paypal_id'),
                $this->container->getParameter('paypal_secret')
            )
        );

        $sandbox = $this->container->getParameter('paypal_sandbox');
        if (!$sandbox) {
            $this->apiContext->setConfig(
                array(
                    'mode' => 'live',
                )
            );
        }

    }

    public function generateUrl($price)
    {
        $payment = new Payment();
        $payment->setIntent('sale');

        $redirectUrls = (new RedirectUrls())
            ->setReturnUrl($this->router->generate('payment_execute', ['price' => $price], Router::ABSOLUTE_URL))
            ->setCancelUrl($this->router->generate('shop_credit', [], Router::ABSOLUTE_URL));

        $payment->setRedirectUrls($redirectUrls);
        $payment->setPayer((new Payer())->setPaymentMethod('paypal'));

        $payment->addTransaction($this->generateItem($price));
        $payment->create($this->apiContext);
        return $payment;
    }

    public function generateItem($price)
    {
        $item = (new Item())
            ->setName('Don telethon')
            ->setPrice($price)
            ->setDescription('Don pour le telethon')
            ->setCurrency('EUR')
            ->setQuantity(1);

        $list = (new ItemList())->setItems(array($item));

        $details = (new Details())
            ->setSubtotal($price);

        $amount = (new Amount())
            ->setTotal($price)
            ->setCurrency("EUR")
            ->setDetails($details);

        return (new Transaction())
            ->setItemList($list)
            ->setDescription('Kubithon')
            ->setAmount($amount)
            ->setCustom($price);

    }

    public function executePayment(Request $request)
    {
        $payment = Payment::get($request->get('paymentId'), $this->apiContext);
        $execution = (new PaymentExecution())
            ->setPayerId($request->get('PayerID'))
            ->addTransaction($payment->getTransactions()[0]);

        $payment->execute($execution, $this->apiContext);
        return $payment;


    }
}