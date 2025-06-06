<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

final class TagFixtures extends Fixture
{
    // public function load(ObjectManager $manager): void
    // {
    //     $tags = array_fill_callback(
    //         0,
    //         25,
    //         static fn (int $index): Tag => (new Tag)->setName(sprintf('Tag %d', $index))
    //     );

    //     array_walk($tags, [$manager, 'persist']);

    //     $manager->flush();
    // }

    public function load(ObjectManager $manager): void
    {
        $tags = [];
        for ($i = 0; $i < 25; $i++) {
            $tag = new Tag();
            $tag->setName(sprintf('Tag %d', $i));
            $tags[] = $tag;
            $manager->persist($tag);
        }

        $manager->flush();
    }

}