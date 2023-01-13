<?php

namespace App\Controller\Admin;

use App\Entity\Admin\User;
use App\Form\Admin\UserType;
use App\Repository\Admin\UserRepository;
use App\Services\Pagination\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/idr-bst/admin')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function adminUsers(Request $request, UserRepository $repository, Pagination $paginationService): Response
    {
        $options = $paginationService->createOptionsFromRequest($request);
        $pagination = $paginationService->generatePagination($repository, $options);

        return $this->render('admin/pages/admin-users.html.twig', [
            'pagination' => $pagination,
            'options' => $options,
        ]);
    }

    #[Route('/users/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $entityManager
        ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['isNew' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = is_string($form->get('plainPassword')->getData()) ? $form->get('plainPassword')->getData() : '';
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $plainPassword
                )
            );
            $user->setRoles(['ROLE_ADMIN']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_users', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = is_string($form->get('plainPassword')->getData()) ? $form->get('plainPassword')->getData() : '';
            if ($plainPassword) {
                $user->setPassword(
                    $passwordEncoder->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }
            $entityManager->flush();

            return $this->redirectToRoute('admin_users', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(string $id, Security $security, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $canDeleteUser = true;
        $currentUser = $security->getUser();

        if ($currentUser instanceof User) {
            if ($id === (string) $currentUser->getId()) {
                $canDeleteUser = false;
                $this->addFlash('notice', 'You can not delete the logged in user, please contact an administrator.');
            }

            if (true === $canDeleteUser && $this->isCsrfTokenValid('delete'.$user->getId(), (string) $request->request->get('_token'))) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('admin_users', [], Response::HTTP_SEE_OTHER);
    }
}
