<?php

namespace App\Controller;

use App\Annotation\ProtectedArea;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    /**
     * @ProtectedArea(name="critical-access")
     * @Route("/account", name="account")
     */
    public function account(): Response
    {
        return $this->render('account/index.html.twig');
    }
}
