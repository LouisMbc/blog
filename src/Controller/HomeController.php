<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, PostRepository $postRepository): Response
    {
        $categories = $categoryRepository->findAllWithPosts();
        $latestPosts = $postRepository->findLatestPosts();

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'latestPosts' => $latestPosts,
        ]);
    }
}