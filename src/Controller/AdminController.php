<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Comment;
use App\Repository\CommentRepository;

class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users', methods: ['GET', 'POST'])]
    public function users(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $userId = $request->request->get('user_id');
            $action = $request->request->get('action');
            $user = $userRepository->find($userId);

            if ($user) {
                if ($action === 'deactivate') {
                    $user->setIsActive(false);
                } elseif ($action === 'activate') {
                    $user->setIsActive(true);
                } elseif ($action === 'change_role') {
                    $role = $request->request->get('role');
                    $user->setRoles([$role]);
                }
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/posts', name: 'app_admin_posts', methods: ['GET', 'POST'])]
    public function posts(Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $postId = $request->request->get('post_id');
            $post = $postRepository->find($postId);

            if ($post) {
                $entityManager->remove($post);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_admin_posts');
        }

        return $this->render('admin/posts.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/comments/{postId}', name: 'app_admin_comments', methods: ['GET', 'POST'])]
    public function comments(Request $request, int $postId, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post = $postRepository->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        if ($request->isMethod('POST')) {
            $commentId = $request->request->get('comment_id');
            $comment = $post->getComments()->filter(fn($c) => $c->getId() === (int)$commentId)->first();

            if ($comment) {
                $entityManager->remove($comment);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_admin_comments', ['postId' => $post->getId()]);
        }

        return $this->render('admin/comments.html.twig', [
            'post' => $post,
            'comments' => $post->getComments(),
        ]);
    }

    #[Route('/comments/validate', name: 'app_admin_validate_comments', methods: ['GET'])]
    public function validateComments(CommentRepository $commentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $comments = $commentRepository->findBy(['status' => 'en attente']);

        return $this->render('admin/validate_comment.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/comments/validate/{id}', name: 'app_admin_validate_comment', methods: ['POST'])]
    public function validateComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('validate'.$comment->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');
            if ($action === 'approve') {
                $comment->setStatus('approuvé');
            } elseif ($action === 'reject') {
                $comment->setStatus('rejeté');
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_validate_comments');
    }
}
