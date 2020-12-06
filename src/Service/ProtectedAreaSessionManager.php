<?php


namespace App\Service;


use App\Annotation\ProtectedArea;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ProtectedAreaSessionManager
{
    public const ON_GOING = 'ON_GOING';
    public const OK = 'OK';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }


    public function setRequestedProtectedAreaInSession(ProtectedArea $protectedArea, Request $request): array
    {

        //You can defined also referer has dynamic url to redirect
        $urlToRedirectWhenCanceled = $this->router->generate('homepage');
        $protectedUrl = $request->getUri();

        $data = [
            "status" => self::ON_GOING,
            "cancel_url" => $urlToRedirectWhenCanceled,
            "protected_url" => $protectedUrl,
        ];

        $request->getSession()->set($this->getSessionKey($protectedArea), $data);

        return $data;
    }

    public function removeProtectedAreaInSession(ProtectedArea $protectedArea, Request $request)
    {
        $request->getSession()->remove($this->getSessionKey($protectedArea));
    }

    public function getSessionKey(ProtectedArea $protectedArea): string
    {
        return $this->getSessionKeyFromString($protectedArea->name);
    }

    public function setAuthorizedProtectedAreaInSession(string $protectedAreaName, Request $request): array
    {
        $key = $this->getSessionKeyFromString($protectedAreaName);
        $data = $request->getSession()->get($key);

        $data["status"] = self::OK;
        // $this->otpManager->removeOTP($this->otpManager->generateOtpKey($protectedAreaName, $this->tokenStorage->getToken()->getUser()));

        $request->getSession()->set($key, $data);

        return $data;
    }

    public function getSessionKeyFromString(string $protectedAreaName): string
    {
        return 'protected-area-' . $protectedAreaName;
    }
}