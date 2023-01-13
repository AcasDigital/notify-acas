<?php

namespace App\HealthCheck;

use Doctrine\ORM\EntityManagerInterface;

class QueueHealthCheck implements HealthCheckInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function check(): HealthCheckResponse
    {
        $response = new HealthCheckResponse();
        $response->setLabel('Queues');
        $conn = $this->em->getConnection();

        $sql = '
                SELECT * FROM messenger_messages m
                WHERE queue_name = :queue
                ';

        $stmt = $conn->prepare($sql);
        $failedMessages = $stmt->executeQuery(['queue' => 'failed']);
        $messages = count($failedMessages->fetchAllAssociative());
        if ($messages > 0) {
            $response->addError("$messages messages on failed queue.");
        }

        $fifteenMinutesAgo = (new \DateTime())->modify('-15 minutes')->format('Y-m-d H:i:s');
        $sql = '
                SELECT * FROM messenger_messages m
                WHERE queue_name != :queue
                AND available_at < :cutoff
                ';
        $stmt = $conn->prepare($sql);
        $oldMessages = $stmt->executeQuery(['queue' => 'failed', 'cutoff' => $fifteenMinutesAgo]);
        $messagesToProcess = $oldMessages->fetchAllAssociative();
        $messageCount = count($messagesToProcess);
        if ($messageCount > 0) {
            $response->addError("$messageCount messages have been waiting to be processed for more than 15 minutes. Check consumers are running");

            $counter = 1;
            foreach ($messagesToProcess as $message) {
                $response->addError(sprintf("Message $counter/$messageCount waiting - queue name: %s, created at: %s, available at: %s, delivered at: %s", $message['queue_name'], $message['created_at'], $message['available_at'], $message['delivered_at']));
                ++$counter;
            }
        }

        return $response;
    }
}
