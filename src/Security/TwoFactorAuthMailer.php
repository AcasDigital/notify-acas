<?php

namespace App\Security;

use App\Message\GovukNotify\TwoFactorAuthCodeEmailMessage;
use App\Repository\Admin\UserRepository;
use App\Services\GovukNotify\NotificationManager;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class TwoFactorAuthMailer implements AuthCodeMailerInterface
{
    public function __construct(
        private string $adminAuthCodeOverride,
        private NotificationManager $notificationManager,
        private LoggerInterface $logger,
        private CodeGeneratorInterface $codeGenerator,
        private Security $security,
        private UserRepository $userRepository
    ) {
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        if ($this->adminAuthCodeOverride) {
            $authCode = $this->adminAuthCodeOverride;
            $user->setEmailAuthCode($authCode);
            $this->userRepository->add($user, true);
        } else {
            $authCode = $user->getEmailAuthCode();
        }

        $personalisation = [
            'authCode' => $authCode,
        ];

        $email = $user->getEmailAuthRecipient();

        $message = new TwoFactorAuthCodeEmailMessage($email, $personalisation);

        $this->notificationManager->sendEmail($message);

        $this->logger->info("Admin auth code sent to $email");
    }

    public function resendAuthCode(): void
    {
        $user = $this->security->getUser();
        assert($user instanceof TwoFactorInterface);
        $this->codeGenerator->generateAndSend($user);

        $this->logger->info(sprintf('Admin auth code re-sent to %s started', $user->getEmailAuthRecipient()));
    }
}
