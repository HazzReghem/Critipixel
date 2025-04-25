<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

// final class AddRatingTest extends WebTestCase
// {
//     private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

//     protected function setUp(): void
//     {
//         $this->client = static::createClient();
//     }

//     private function login(): void
//     {
//         /** @var User $user */
//         $user = self::getContainer()
//             ->get('doctrine')
//             ->getRepository(User::class)
//             ->findOneByUsername('user+0');

//         $this->client->loginUser($user);
//     }

//     // test l'ajout d'une note valide
//     public function testShouldPostReview(): void
//     {
//         $this->login();
//         $crawler = $this->client->request('GET', '/jeu-video-49');

//         self::assertResponseIsSuccessful();

//         $this->client->submitForm('Poster', [
//             'review[rating]' => 4,
//             'review[comment]' => 'Mon commentaire',
//         ]);

//         self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

//         $this->client->followRedirect();

//         self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
//         self::assertSelectorTextContains('div.list-group-item:last-child p', 'Mon commentaire');
//         self::assertSelectorTextContains('div.list-group-item:last-child span.value', '4');
//     }

//     // test l'ajout d'une note valide avec un commentaire vide
//     public function testShouldNotAllowMultipleReviewsFromSameUser(): void
//     {
//         $this->login();
//         // $this->client->request('GET', '/jeu-video-49');

//         // $this->client->submitForm('Poster', [
//         //     'review[rating]' => 5,
//         //     'review[comment]' => 'Première note',
//         // ]);
//         $crawler = $this->client->request('GET', '/jeu-video-49');
//         // echo $this->client->getResponse()->getContent();


//         $form = $crawler->selectButton('Poster')->form([
//             'review[rating]' => 5,
//             'review[comment]' => 'Première note',
//         ]);

//         $this->client->submit($form);

//         $this->client->followRedirect();

//         // Vérifie que le formulaire n’est plus affiché
//         self::assertSelectorNotExists('form[name="review"]');
//     }


//     // test erreur de validation
//     public function testShouldRejectInvalidReview(): void
//     {
//         $this->login();
//         // $this->client->request('GET', '/jeu-video-49');

//         // // Test note manquante
//         // $this->client->submitForm('Poster', [
//         //     'review[rating]' => '',
//         //     'review[comment]' => 'Note manquante',
//         // ]);

//         // self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
//         // self::assertSelectorExists('.form-error-message');

//         // // Test commentaire trop long
//         // $this->client->submitForm('Poster', [
//         //     'review[rating]' => 3,
//         //     'review[comment]' => str_repeat('A', 2001), // 2001 caractères
//         // ]);

//         // self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
//         // self::assertSelectorExists('.form-error-message');
//         $crawler = $this->client->request('GET', '/jeu-video-49');

//         // Premier test : note vide
//         $form1 = $crawler->selectButton('Poster')->form([
//             'review[rating]' => '',
//             'review[comment]' => 'Note manquante',
//         ]);
//         $this->client->submit($form1);
//         self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
//         self::assertSelectorExists('.form-error-message');

//         // Recharger le formulaire pour le second test
//         $crawler = $this->client->request('GET', '/jeu-video-49');

//         $form2 = $crawler->selectButton('Poster')->form([
//             'review[rating]' => 3,
//             'review[comment]' => str_repeat('A', 2001),
//         ]);
//         $this->client->submit($form2);
//         self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
//         self::assertSelectorExists('.form-error-message');

//     }

//     // test restriction d'accès
//     public function testFormShouldBeHiddenForGuest(): void
//     {
//         $this->client->request('GET', '/jeu-video-49');
//         self::assertSelectorNotExists('form[name="review"]');
//     }

//     public function testGuestCannotPostReview(): void
//     {
//         $this->client->request('POST', '/jeu-video/49/avis', [
//             'review[rating]' => 5,
//             'review[comment]' => 'Tentative non autorisée',
//         ]);

//         self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
//     }
// }

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

    // ✅ Test l'ajout d'une note valide
    public function testShouldPostReview(): void
    {
        $this->login();
        $crawler = $this->client->request('GET', '/jeu-video-49');

        self::assertResponseIsSuccessful();

        // Vérifie que le formulaire est présent
        if ($crawler->filter('form[name="review"]')->count() === 0) {
            self::fail('Le formulaire d’avis n’est pas visible pour l’utilisateur connecté.');
        }

        $form = $crawler->selectButton('Poster')->form([
            'review[rating]' => 4,
            'review[comment]' => 'Mon commentaire',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
        self::assertSelectorTextContains('div.list-group-item:last-child p', 'Mon commentaire');
        self::assertSelectorTextContains('div.list-group-item:last-child span.value', '4');
    }

    // ✅ Test qu’un même utilisateur ne peut pas poster deux avis
    public function testShouldNotAllowMultipleReviewsFromSameUser(): void
    {
        $this->login();

        // Soumission du premier avis
        $crawler = $this->client->request('GET', '/jeu-video-49');

        if ($crawler->filter('form[name="review"]')->count() === 0) {
            self::fail('Le formulaire d’avis n’est pas visible avant la première soumission.');
        }

        $form = $crawler->selectButton('Poster')->form([
            'review[rating]' => 5,
            'review[comment]' => 'Première note',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        // Après redirection, le formulaire doit avoir disparu
        $crawler = $this->client->request('GET', '/jeu-video-49');
        self::assertSelectorNotExists('form[name="review"]');
    }

    // ✅ Test erreurs de validation : note vide + commentaire trop long
    public function testShouldRejectInvalidReview(): void
    {
        $this->login();
        $crawler = $this->client->request('GET', '/jeu-video-49');

        if ($crawler->filter('form[name="review"]')->count() === 0) {
            self::fail('Le formulaire d’avis n’est pas visible pour tester les erreurs de validation.');
        }

        // ⚠️ Test 1 : note vide
        $form1 = $crawler->selectButton('Poster')->form([
            'review[rating]' => '',
            'review[comment]' => 'Note manquante',
        ]);
        $this->client->submit($form1);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('.form-error-message');

        // ⚠️ Test 2 : commentaire trop long
        $crawler = $this->client->request('GET', '/jeu-video-49');
        if ($crawler->filter('form[name="review"]')->count() === 0) {
            self::fail('Le formulaire n’est plus visible pour le second test de validation.');
        }

        $form2 = $crawler->selectButton('Poster')->form([
            'review[rating]' => 3,
            'review[comment]' => str_repeat('A', 2001),
        ]);
        $this->client->submit($form2);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('.form-error-message');
    }

    // ✅ Test que le formulaire est caché pour les invités
    public function testFormShouldBeHiddenForGuest(): void
    {
        $this->client->request('GET', '/jeu-video-49');
        self::assertSelectorNotExists('form[name="review"]');
    }

    // ✅ Test qu’un invité ne peut pas poster un avis
    public function testGuestCannotPostReview(): void
    {
        $this->client->request('POST', '/jeu-video/49/avis', [
            'review[rating]' => 5,
            'review[comment]' => 'Tentative non autorisée',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
