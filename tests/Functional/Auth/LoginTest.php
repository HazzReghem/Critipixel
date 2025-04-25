<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class LoginTest extends FunctionalTestCase
{
    public function testThatLoginShouldSucceeded(): void
    {
        $crawler = $this->get('/auth/login');

        echo $this->client->getResponse()->getContent();
        // $this->get('/auth/login');

        // $this->client->submit('Se connecter', [
        //     'email' => 'user+1@email.com',
        //     'password' => 'password'
        // ]);
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'user@email.com',
            'password' => 'password',
        ]);

        $this->client->submit($form);

        // vérifie l'authentification
        $authorizationChecker = $this->service(AuthorizationCheckerInterface::class);
        self::assertTrue($authorizationChecker->isGranted('IS_AUTHENTICATED'));

        $this->get('/auth/logout');

        $authorizationChecker = $this->service(AuthorizationCheckerInterface::class);
        self::assertFalse($authorizationChecker->isGranted('IS_AUTHENTICATED'));
    }

    public function testThatLoginShouldFailed(): void
    {
        $crawler = $this->get('/auth/login');
        // $this->get('/auth/login');

        // $this->client->submit('/auth/login', 'Se connecter', [
        //     'email' => 'user+1@email.com',
        //     'password' => 'fail'
        // ]);
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'user@email.com',
            'password' => 'password',
        ]); 

        $this->client->submit($form);

        $authorizationChecker = $this->service(AuthorizationCheckerInterface::class);

        self::assertFalse($authorizationChecker->isGranted('IS_AUTHENTICATED'));
    }
}
