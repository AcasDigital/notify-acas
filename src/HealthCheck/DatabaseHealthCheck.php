<?php

namespace App\HealthCheck;

use Doctrine\ORM\EntityManagerInterface;

class DatabaseHealthCheck implements HealthCheckInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function check(): HealthCheckResponse
    {
        $response = new HealthCheckResponse();
        $response->setLabel('Database');
        try {
            $this->em->getConnection()->connect();
        } catch (\Exception $e) {
            $response->addError('Error connecting to database: '.$e->getMessage());
        }

        return $response;
    }
}
