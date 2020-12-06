<?php

namespace App\Controller;

use App\Form\OtpType;
use App\Service\ProtectedAreaSessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProtectedAreaController extends AbstractController
{
    /**
     * @Route("/protected-area")
     */
    public function form(Request $request, ProtectedAreaSessionManager $protectedAreaSessionManager): Response
    {
        $protectedAreaName = $request->query->get("protected-area");

        $protectedAreaData = $request->getSession()->get($protectedAreaSessionManager->getSessionKeyFromString($protectedAreaName));

        //Here send otp how you cant
        $otpSent = 'ABC-DEF';

        $form = $this->createForm(OtpType::class, null, [
            "otpSent" => $otpSent,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $protectedAreaSessionManager->setAuthorizedProtectedAreaInSession($protectedAreaName, $request);

            //Return to original requested url
            return $this->redirect($data["protected_url"]);
        }
        return $this->render('protected_area/index.html.twig', [
            'form' => $form->createView(),
            'cancelUrl' => $protectedAreaData["cancel_url"],
            'protectedArea' => $protectedAreaName,
        ]);
    }
}
