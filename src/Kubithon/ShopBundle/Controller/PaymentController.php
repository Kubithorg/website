<?php

namespace Kubithon\ShopBundle\Controller;

use AppBundle\Entity\Invoice;
use AppBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends Controller
{
    /**
     * @Route("/payment/generate/{id}", name="payment_generate")
     * @param $invoice
     * @return Response
     */
    public function payAction()
    {

        if ($invoice->getOrder()->getClient() != $this->getUser())
            throw new AccessDeniedException('Access denied');

        if ($invoice->getStatus() != 1)
            return new JsonResponse([
                'status' => 'error'
            ]);

        $payment = $this->get('app.service.paypal.payment')->generateUrl($invoice);

        return new JsonResponse([
            'status' => 'success',
            'id' => $payment->getId()
        ]);
    }

    /**
     * @Route("/payment/{id}/execute", name="payment_execute")
     * @Method("POST")
     * @param Request $request
     * @param null $invoice
     * @return Response
     */
    public function payNextAction(Request $request, $invoice = null)
    {
        if (!$invoice)
            throw new NotFoundHttpException('Commande introuvable');

        if ($invoice->getOrder()->getClient() != $this->getUser())
            throw new AccessDeniedException('Access denied');

        $logger = $this->get('logger');
        $payment = $this->get('app.service.paypal.payment')->executePayment($request, $logger);

        if ($payment->getTransactions()[0]->getCustom() != $invoice->getId() || $payment->getState() == 'failed')
            return new JsonResponse([
                'status' => 'error'
            ]);

        $em = $this->getDoctrine()->getManager();
        $i = $em->getRepository(Invoice::class)->find($invoice);
        $i->setStatus(Invoice::STATUS_PAYED);
        $i->setLeft(0);
        $i->setPayed($invoice->getTotal());
        $i->setPaypalId($payment->getId());
        $em->flush();

        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Order::class)->find($invoice->getOrder());
        $order->setStatus($invoice->getNext());
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
        ]);
    }


}