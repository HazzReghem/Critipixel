<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    // genérateur de données fictives
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    
    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();

        $users = array_chunk(
            $manager->getRepository(User::class)->findAll(),
            5
        );

        /** @var string $fakeText */
        $fakeText = $this->faker->paragraphs(5, true);

        // Création de 50 jeux vidéo avec des données fictives

        $videoGames = [];

        for ($i = 0; $i < 50; $i++) {
            $videoGame = (new VideoGame())
                ->setTitle(sprintf('Jeu vidéo %d', $i))
                ->setDescription($fakeText)
                ->setReleaseDate((new DateTimeImmutable())->sub(new DateInterval(sprintf('P%dD', $i))))
                ->setTest($fakeText)
                ->setRating(($i % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $i))
                ->setImageSize(2_098_872);

            $videoGames[] = $videoGame;
        }

        // $videoGames = array_fill_callback(0, 50, fn (int $index): VideoGame => (new VideoGame)
        //     ->setTitle(sprintf('Jeu vidéo %d', $index))
        //     ->setDescription($fakeText)
        //     ->setReleaseDate((new DateTimeImmutable())->sub(new DateInterval(sprintf('P%dD', $index))))
        //     ->setTest($fakeText)
        //     ->setRating(($index % 5) + 1)
        //     ->setImageName(sprintf('video_game_%d.png', $index))
        //     ->setImageSize(2_098_872)
        // );  

        // Ajout de 5 tags à chaque jeu vidéo

        // array_walk($videoGames, static function (VideoGame $videoGame, int $index) use ($tags) {
        foreach ($videoGames as $index => $videoGame) {
            for ($tagIndex = 0; $tagIndex < 5; $tagIndex++) {
                $videoGame->getTags()->add($tags[($index + $tagIndex) % count($tags)]);
            }
        };

        foreach ($videoGames as $videoGame) {
            $manager->persist($videoGame);
        }
        
        // array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        // création de 5 avis pour chaque jeu vidéo
        // chaque avis est associé à un utilisateur différent

        // array_walk($videoGames, function (VideoGame $videoGame, int $index) use ($users, $manager) {
        foreach ($videoGames as $index => $videoGame) {
            $filteredUsers = $users[$index % count($users)];

            foreach ($filteredUsers as $i => $user) {
                /** @var string $comment */
                $comment = $this->faker->paragraphs(1, true);

                $review = (new Review())
                    ->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($comment)
                ;

                $videoGame->getReviews()->add($review);

                $manager->persist($review);

                $this->calculateAverageRating->calculateAverage($videoGame);
                $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            }
        };

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TagFixtures::class, UserFixtures::class];
    }
}