<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class RecipeController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
    ) {
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

    #[Route('/recipes/{slug}-by-{username}', name: 'recipe.show', requirements: [
        'username' => '^[a-zA-Z0-9\-\_\.\'\, ]+$', 'slug' => '[a-z0-9-]+'
    ])]
    public function show(
        Request $request,
        string $slug,
        string $username,
        RecipeRepository $repo,
        UserRepository $userRepo
    ): Response {
        $user = $userRepo->findOneBy([
            'username' => $username
        ]);

        $recipe = $repo->findOneBy([
            'slug' => $slug,
            'user' => $user,
        ]);

        return $this->render('recipe/show.html.twig', compact('recipe'));
    }

    #[IsGranted('IS_AUTHENTICATED'), Route('/recipes/create', name: 'recipe.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $recipe->setUser($this->getUser());
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

    #[IsGranted('IS_AUTHENTICATED'), Route('/recipes/{slug}-by-{username}/edit', name: 'recipe.edit', requirements: [
        'username' => '^[a-zA-Z0-9\-\_\.\'\, ]+$', 'slug' => '[a-z0-9-]+'
    ], methods: [
        'GET', 'POST'
    ])]
    public function edit(
        Request $request,
        string $slug,
        string $username,
        UserRepository $userRepo,
        RecipeRepository $recipeRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepo->findOneBy([
            'username' => $username
        ]);
        $recipe = $recipeRepo->findOneBy([
            'slug' => $slug,
            'user' => $user,
        ]);

        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

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

    #[IsGranted('IS_AUTHENTICATED'), Route('/recipes/{slug}-by-{username}/confirm-delete', name: 'recipe.delete_form', requirements: [
        'username' => '^[a-zA-Z0-9\-\_\.\'\, ]+$', 'slug' => '[a-z0-9-]+'
    ], methods: [
        'GET', 'POST'
    ])]
    public function confirmDelete(
        string $slug,
        string $username,
        UserRepository $userRepo,
        RecipeRepository $recipeRepo,
    ): Response {
        $user = $userRepo->findOneBy([
            'username' => $username
        ]);
        $recipe = $recipeRepo->findOneBy([
            'slug' => $slug,
            'user' => $user,
        ]);

        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('recipe/confirm-delete.html.twig', compact('recipe'));
    }

    #[IsGranted('IS_AUTHENTICATED'), Route('/recipes/{slug}-by-{username}/delete', name: 'recipe.delete', requirements: [
        'username' => '^[a-zA-Z0-9\-\_\.\'\, ]+$', 'slug' => '[a-z0-9-]+'
    ], methods: ['DELETE'])]
    public function delete(
        string $slug,
        string $username,
        UserRepository $userRepo,
        RecipeRepository $recipeRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepo->findOneBy([
            'username' => $username
        ]);
        $recipe = $recipeRepo->findOneBy([
            'slug' => $slug,
            'user' => $user,
        ]);

        if ($recipe->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $recipe->setSlugger($this->slugger);

        $this->addFlash('success', 'The recipe "'.$recipe->getTitle().'" was successfully deleted.');
        $recipe->setDeletedAt(new DateTimeImmutable());
        $em->flush();
        return $this->redirectToRoute('recipe.index');
    }
}
