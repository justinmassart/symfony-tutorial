<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $repo): Response
    {
        if ($request->query->has('duration-max')) {
            $maxDuration = (int) $request->query->get('duration-max');
            $recipes = $repo->findWithDurationLowerThan($maxDuration);
        } else {
            $page = $request->query->has('page') ? (int) $request->query->get('page') : 1;
            $recipes = $repo->getTenResults($page);
        }

        $totalDuration = $repo->findTotalDuration()['total'];

        return $this->render('recipe/index.html.twig', compact('recipes', 'totalDuration'));
    }

    #[Route('/recipes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $repo): Response
    {
        $recipe = $repo->find($id);

        if ($recipe->getSlug() !== $slug) {
            return $this->redirectToRoute('recipe.show', ['slug' => $recipe->getSlug(), 'id' => $id]);
        }

        return $this->render('recipe/show.html.twig', compact('recipe'));
    }
}
