<?php

namespace App\Admin\DataFixtures;

use App\Entity\Admin\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $details = [
            ['email' => 'acas@test.dev', 'password' => 'DummyPw12'],
            ['email' => 'app@test.dev', 'password' => '1234'],
        ];

        $users = [];
        foreach ($details as $detail) {
            $user = new User();
            $user->setEmail($detail['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $detail['password']));
            $user->setRoles(['ROLE_ADMIN']);
            $users[] = $user;
        }

        foreach ($users as $user) {
            $manager->persist($user);
        }

        $manager->flush();
    }
}
