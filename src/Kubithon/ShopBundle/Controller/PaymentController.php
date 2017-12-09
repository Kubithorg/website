<?php

namespace Kubithon\ShopBundle\Controller;

use AppBundle\Entity\Invoice;
use AppBundle\Entity\Order;
use AppBundle\Entity\Transaction;
use Kubithon\ShopBundle\Entity\Product;
use Predis\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends Controller
{

    /**
     * @Route("/buy/{id}", name="shop_buy")
     * @param Product|null $product
     * @return RedirectResponse
     * @Security("has_role('ROLE_USER')")
     */
    public function buyAction(Product $product = null)
    {
        if (!$product)
            throw new NotFoundHttpException();

        if ($product->getPrice() > $this->getUser()->getBalance()) {
            $this->addFlash('error', '<strong>Erreur :</strong> Vous ne disposez pas d\'assez de credit sur votre compte pour effcuter cet achat.');
            return $this->redirectToRoute('shop_items');
        }

        $commands = $product->getCommand();
        $commands = explode('||', $commands);

        $final = array();
        foreach ($commands as $command) {
            $final[] = str_replace('%pseudo%', $this->getUser()->getUsername(), $command);
        }

        $redis = new Client([
            'scheme' => 'tcp',
            'host' => '31.172.164.45',
            'port' => 38714,
            'password' => 'T{]3}fKYyxXDH>dIfzvHVjij[>]F8N_jPL4JHxmhgNeg316YTH(8NOvmUvNtvIK}',
        ]);

        $redis->sadd('shop', json_encode([
            'id' => $this->getUser()->getUuid(),
            'commands' => $final
        ]));

        $user = $this->getUser();
        $user->decreaseBalance($product->getPrice());
        $this->get('fos_user.user_manager')->updateUser($user);

        $this->addFlash('success', '<strong>Succès :</strong> Votre commande va vous etre délivré en jeu d\'ici quelques instants.');
        return $this->redirectToRoute('shop_items');
    }

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
     * @Route("/payment/mcg", name="code_validate")
     * @Method("POST")
     * @param Request $request
     * @return RedirectResponse
     */
    public function validateAction(Request $request)
    {
        $palier = $request->get('palier');
        $code = $request->get('code');

        $response = file_get_contents("https://secure.mcgpass.com/api/v1/api_mcgpass?idclient=1325&idsite=286&cle_api=zx2653XunL3sML0UfY6ouoLL8oZwd15zbeLNuXUm&action=transaction&type=audiotel&code_palier=$palier&code=$code");
        $response = json_decode($response);

        if ($response->statut === "false") {
            $this->addFlash('error', '<strong>Erreur : </strong>Le code entré est invalide');
            return $this->redirectToRoute('shop_credit');
        }

        $user = $this->getUser();
        $user->increaseBalance(1.5);
        $this->get('fos_user.user_manager')->updateUser($user);

        $this->addFlash('success', '<strong>Super,</strong> la transaction c\'est déroulée avec succès. <br>Merci pour votre don <i class="fa fa-heart text" style="color: #FF3860;"></i>');
        return $this->redirectToRoute('shop_credit');

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