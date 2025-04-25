<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    // protected function getEntityManager(): EntityManagerInterface
    // {
    //     return $this->service(EntityManagerInterface::class);
    // }
    protected function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    protected function service(string $id): object
    {
        // return $this->client->getContainer()->get($id);
        return static::getContainer()->get($id);
    }

    /**
     * @param array<string, string|int|bool|float|array<string, scalar>> $parameters
     */
    protected function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }

    // protected function login(string $email = 'user+0@email.com'): void
    // {
    //     $user = $this->service(EntityManagerInterface::class)->getRepository(User::class)->findOneByEmail($email);

    //     $this->client->loginUser($user);
    // }
    protected function login(string $email = 'user+0@email.com'): void
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail($email);
        if (!$user) {
            throw new \RuntimeException("Utilisateur de test '$email' non trouvé."); // Ajout d'une vérification
        }
        $this->client->loginUser($user);
    }

    /**
     * @param array<string, int|string> $formData
     */
    // protected function submit(string $buttonLabel, array $formData, string $method = 'POST'): void
    // {
    //     $crawler = $this->client->request('GET', $this->client->getRequest()->getUri());
    //     $button = $crawler->selectButton($buttonLabel);
    //     $form = $button->form($formData, $method);
    //     $this->client->submit($form);
    // }

    protected function submit(string $url, string $buttonLabel, array $formData, string $method = 'POST'): void
    {
        $crawler = $this->client->request('GET', $url);
        $button = $crawler->selectButton($buttonLabel);
        $form = $button->form($formData, $method);
        $this->client->submit($form);
    }

}
