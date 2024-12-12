<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RecipeController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger, EntityManagerInterface $entityManager)
    {
        $this->slugger = $slugger;
    }

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
        $totalRecipes = $repo->findTotalCount()['count'];

        return $this->render('recipe/index.html.twig', compact('recipes', 'totalDuration', 'totalRecipes'));
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

    #[Route('/recipes/create', name: 'recipe.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $recipe->setSlugger($this->slugger);
        $recipeForm = $this->createForm(RecipeType::class, $recipe);
        $recipeForm->handleRequest($request);

        if ($recipeForm->isSubmitted() && $recipeForm->isValid()) {
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'The recipe was successfully created !');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/create.html.twig', compact('recipeForm'));
    }

    #[Route('/recipes/{id}/edit', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $em): Response
    {
        $recipe->setSlugger($this->slugger);
        $recipeForm = $this->createForm(RecipeType::class, $recipe);
        $recipeForm->handleRequest($request);

        if ($recipeForm->isSubmitted() && $recipeForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The recipe was successfully updated !');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/edit.html.twig', compact('recipe', 'recipeForm'));
    }

    #[Route('/recipes/{id}/delete', name: 'recipe.delete_form', methods: ['GET', 'POST'])]
    public function confirmDelete(Recipe $recipe): Response
    {
        return $this->render('recipe/confirm-delete.html.twig', compact('recipe'));
    }

    #[Route('/recipes/{id}', name: 'recipe.delete', methods: ['DELETE'])]
    public function delete(Recipe $recipe, EntityManagerInterface $em): Response
    {
        $this->addFlash('success', 'The recipe "'.$recipe->getTitle().'" was successfully deleted.');
        $em->remove($recipe);
        $em->flush();
        return $this->redirectToRoute('recipe.index');
    }
}
