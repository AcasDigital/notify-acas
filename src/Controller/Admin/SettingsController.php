<?php

namespace App\Controller\Admin;

use App\Services\SettingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/idr-bst/admin')]
class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'admin_settings', methods: ['GET', 'POST'])]
    public function adminIndex(Request $request, SettingManager $settingManager, string $crmSubmissionEndpoint): Response
    {
        $form = $this->createFormBuilder(
            ['submission_pause' => $settingManager->getBool(SettingManager::SUBMISSION_PAUSED, false),
                'submission_cleanup' => $settingManager->getInt(SettingManager::SUBMISSION_CLEANUP, 0), ])
            ->add('submission_pause', CheckboxType::class, ['required' => false])
            ->add('submission_cleanup', IntegerType::class, ['required' => false])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Settings saved.');
            $data = $form->getData();
            assert(is_array($data));
            $settingManager->set(SettingManager::SUBMISSION_PAUSED, (string) $data['submission_pause']);
            $settingManager->set(SettingManager::SUBMISSION_CLEANUP, (string) $data['submission_cleanup']);
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
            'crmEndpoint' => $crmSubmissionEndpoint,
        ]);
    }
}
