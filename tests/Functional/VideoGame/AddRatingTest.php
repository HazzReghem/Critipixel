<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AddRatingTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function login(): void
    {
        /** @var User $user */
        $user = self::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneByUsername('user+0');

        $this->client->loginUser($user);
    }

    // test l'ajout d'une note valide
    public function testShouldPostReview(): void
    {
        $this->login();
        $crawler = $this->client->request('GET', '/jeu-video-49');

        self::assertResponseIsSuccessful();

        $this->client->submitForm('Poster', [
            'review[rating]' => 4,
            'review[comment]' => 'Mon commentaire',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
        self::assertSelectorTextContains('div.list-group-item:last-child p', 'Mon commentaire');
        self::assertSelectorTextContains('div.list-group-item:last-child span.value', '4');
    }

    // test l'ajout d'une note valide avec un commentaire vide
    public function testShouldNotAllowMultipleReviewsFromSameUser(): void
    {
        $this->login();
        $this->client->request('GET', '/jeu-video-49');

        $this->client->submitForm('Poster', [
            'review[rating]' => 5,
            'review[comment]' => 'Première note',
        ]);

        $this->client->followRedirect();

        // Vérifie que le formulaire n’est plus affiché
        self::assertSelectorNotExists('form[name="review"]');
    }


    // test erreur de validation
    public function testShouldRejectInvalidReview(): void
    {
        $this->login();
        $this->client->request('GET', '/jeu-video-49');

        // Test note manquante
        $this->client->submitForm('Poster', [
            'review[rating]' => '',
            'review[comment]' => 'Note manquante',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('.form-error-message');

        // Test commentaire trop long
        $this->client->submitForm('Poster', [
            'review[rating]' => 3,
            'review[comment]' => str_repeat('A', 2001), // 2001 caractères
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('.form-error-message');
    }

    // test restriction d'accès
    public function testFormShouldBeHiddenForGuest(): void
    {
        $this->client->request('GET', '/jeu-video-49');
        self::assertSelectorNotExists('form[name="review"]');
    }

    public function testGuestCannotPostReview(): void
    {
        $this->client->request('POST', '/jeu-video/49/avis', [
            'review[rating]' => 5,
            'review[comment]' => 'Tentative non autorisée',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
