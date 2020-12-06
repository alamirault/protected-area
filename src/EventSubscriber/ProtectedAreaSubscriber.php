<?php

namespace App\EventSubscriber;

use App\Annotation\ProtectedArea;
use App\Service\ProtectedAreaSessionManager;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ProtectedAreaSubscriber implements EventSubscriberInterface
{
    private Reader $reader;
    private ProtectedAreaSessionManager $protectedAreaSessionManager;
    private HttpKernelInterface $httpKernel;

    public function __construct(Reader $reader, ProtectedAreaSessionManager $protectedAreaSessionManager, HttpKernelInterface $httpKernel)
    {
        $this->reader = $reader;
        $this->protectedAreaSessionManager = $protectedAreaSessionManager;
        $this->httpKernel = $httpKernel;
    }

    public function onKernelController(ControllerEvent $event)
    {
        if (!is_array($controllers = $event->getController())) {
            return;
        }
        $request = $event->getRequest();

        list($controller, $methodName) = $controllers;

        $protectedAreaAnnotation = $this->getAnnotation($controller, $methodName);
        if(!$protectedAreaAnnotation){
            return;
        }

        $sessionKey = $this->protectedAreaSessionManager->getSessionKey($protectedAreaAnnotation);

        // If no protected area process, or process is not finished
        if (!$request->getSession()->get($sessionKey) || $request->getSession()->get($sessionKey)["status"] != ProtectedAreaSessionManager::OK) {
            $this->protectedAreaSessionManager->setRequestedProtectedAreaInSession($protectedAreaAnnotation, $request);

            //Internal redirect to form otp when user has not secured area in session.
            $event->setController(function () use ($request, $protectedAreaAnnotation) {
                return $this->forward($request, 'App\\Controller\\ProtectedAreaController::form', [
                    'protected-area' => $protectedAreaAnnotation->name,
                ]);
            });
        }
    }

    private function getAnnotation(object $controller, string $methodName): ?ProtectedArea
    {
        $reflectionClass = new \ReflectionClass($controller);
        $classAnnotation = $this->reader
            ->getClassAnnotation($reflectionClass, ProtectedArea::class);

        if($classAnnotation){
            return $classAnnotation;
        }

        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        $methodAnnotation = $this->reader
            ->getMethodAnnotation($reflectionMethod, ProtectedArea::class);

       return $methodAnnotation;
    }

    private function forward(Request $request, $controller, array $query = [])
    {
        $path = [];
        $path['_forwarded'] = $request->attributes;
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);

        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
