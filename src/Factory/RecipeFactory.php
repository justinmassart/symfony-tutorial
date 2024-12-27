<?php

namespace App\Factory;

use App\Entity\Recipe;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Recipe>
 */
final class RecipeFactory extends PersistentProxyObjectFactory
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public static function class(): string
    {
        return Recipe::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $titleNbWords = rand(1, 4);
        $title = self::faker()->words($titleNbWords, true);
        $slug = $this->slugger->slug($title);
        $timestamp = DateTimeImmutable::createFromMutable(self::faker()->dateTime());

        return [
            'content' => self::faker()->text(),
            'createdAt' => $timestamp,
            'deletedAt' => null,
            'duration' => rand(10, 240),
            'slug' => $slug,
            'title' => $title,
            'updatedAt' => $timestamp,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Recipe $recipe): void {
            $recipe->setSlugger($this->slugger);
        });
    }
}
