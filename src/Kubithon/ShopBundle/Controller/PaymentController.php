<?php

namespace Kubithon\ShopBundle\Controller;

use AppBundle\Entity\Invoice;
use AppBundle\Entity\Order;
use AppBundle\Entity\Transaction;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends Controller
{
    /**
     * @Route("/payment/generate/{price}", name="payment_generate")
     * @param $price
     * @return Response
     */
    public function payAction($price)
    {
        $payment = $this->get('app.service.paypal.payment')->generateUrl($price);
        return new RedirectResponse($payment->getApprovalLink());
    }

    /**
     * @Route("/payment/{price}/execute", name="payment_execute")
     * @param Request $request
     * @return Response
     */
    public function payNextAction($price, Request $request)
    {

        $payment = $this->get('app.service.paypal.payment')->executePayment($request);

        if ($payment->getState() == 'failed') {
            $this->addFlash('erreur', 'Une erreur c\'est produite ! Pas de panique votre compte n\'a pas été débité');
            return $this->redirectToRoute('shop_credit');
        }

        $transaction = new Transaction();
        $transaction->setType('paypal');
        $transaction->setPrice($payment->getTransactions()[0]->getCustom());
        $transaction->setDetails(json_encode($payment));
        $transaction->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        $user = $this->getUser();
        $user->increaseBalance($payment->getTransactions()[0]->getCustom());
        $this->get('fos_user.user_manager')->updateUser($user);

        $this->addFlash('success', '<strong>Super,</strong> la transaction c\'est déroulée avec succès. <br>Merci pour votre don <i class="fa fa-heart text" style="color: #FF3860;"></i>');
        return $this->redirectToRoute('shop_credit');
    }


}