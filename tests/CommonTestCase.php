<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommonTestCase extends WebTestCase
{
    protected function passProtectedArea(string $name = "critical-changes")
    {
        $session = static::$kernel->getContainer()->get("session");
        $session->set("protected-area-" . $name, [
            "status" => "OK",
        ]);
    }

}