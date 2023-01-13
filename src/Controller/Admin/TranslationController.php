<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Translation;
use App\Form\Admin\TranslationType;
use App\Repository\Admin\TranslationRepository;
use App\Services\Pagination\Pagination;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/idr-bst/admin/translation')]
class TranslationController extends AbstractController
{
    public function __construct(private TranslationRepository $repository, private LoggerInterface $logger, private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'translation_index', methods: ['GET'])]
    public function index(Request $request, Pagination $paginationService): Response
    {
        $options = $paginationService->createOptionsFromRequest($request);
        $pagination = $paginationService->generatePagination($this->repository, $options);

        return $this->render('admin/translation/index.html.twig', [
            'pagination' => $pagination,
            'options' => $options,
        ]);
    }

    #[Route('/new', name: 'translation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $editor = (string) $request->query->get('editor', 'plain');

        $translation = new Translation();
        if ($default_key = $request->query->get('key')) {
            $translation->setKey($default_key);
        }
        $form = $this->createForm(TranslationType::class, $translation, ['editor' => $editor]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translation->setKey($translation->getKey());

            $this->doctrine->getManager()->persist($translation);
            $this->doctrine->getManager()->flush();

            $this->clearTranslationCache();
            if ($destination = $request->query->get('destination')) {
                return $this->redirect($destination, 302);
            }

            return $this->redirectToRoute('translation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/translation/new.html.twig', [
            'translation' => $translation,
            'form' => $form,
            'editor' => $editor,
        ]);
    }

    #[Route('/edit', name: 'translation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $key = $request->query->get('key');
        if (!$key) {
            return $this->redirectToRoute('translation_index');
        }

        $translation = $this->repository->getOneByLowerCasedKey($key);
        if (!$translation) {
            return $this->new($request);
        }

        $editor = (string) $request->query->get('editor', 'plain');
        $form = $this->createForm(TranslationType::class, $translation, ['editor' => $editor]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translation->setKey($translation->getKey());
            $this->doctrine->getManager()->flush();

            $this->clearTranslationCache();

            if ($destination = $request->query->get('destination')) {
                return $this->redirect($destination, 302);
            }

            return $this->redirectToRoute('translation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/translation/edit.html.twig', [
            'translation' => $translation,
            'form' => $form,
            'editor' => $editor,
        ]);
    }

    #[Route('/', name: 'translation_delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $key = $request->query->get('key');
        if (!$key) {
            throw new \InvalidArgumentException('No key found in delete request');
        }

        $translation = $this->repository->getOneByLowerCasedKey($key);
        if (!$translation) {
            throw new \InvalidArgumentException("No translation found for: $key");
        }

        if ($this->isCsrfTokenValid('delete'.$translation->getKey(), (string) $request->request->get('_token'))) {
            $this->doctrine->getManager()->remove($translation);
            $this->doctrine->getManager()->flush();

            $this->clearTranslationCache();
            if ($destination = $request->query->get('destination')) {
                return $this->redirect($destination, 302);
            }
        }

        return $this->redirectToRoute('translation_index', [], Response::HTTP_SEE_OTHER);
    }

    private function clearTranslationCache(): bool
    {
        $finder = new Finder();
        $cacheDir = $this->getParameter('kernel.cache_dir').'/translations';
        $this->logger->error("Clearing down translation files in $cacheDir");
        foreach ($finder->files()->in($cacheDir)->name('*.php') as $file) {
            $path = $file->getRealpath();
            $this->logger->error("Clearing $path");
            if (false !== $path) {
                unlink($path);
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
            }
        }

        return true;
    }
}
