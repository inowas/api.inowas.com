<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserManager
{

    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public function encryptPassword(string $password): string
    {
        return $this->passwordEncoder->encodePassword(new User('encryptMyPassword', $password), $password);
    }

    /**
     * @param string $username
     * @return bool
     */
    public function usernameIsValidAndAvailable(string $username): bool
    {

        if (strlen($username) <= 3) {
            return false;
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        return !($userRepository->findOneBy(['username' => $username]) instanceof User);
    }

    public function findUserById(string $id): ?User
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        return $user;
    }

    public function findAllUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    public function findUserByUsername(string $username): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function saveUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function createRandomLoginToken(string $userId): void
    {
        $user = $this->findUserById($userId);
        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $userId));
        }

        $user->createRandomLoginToken();
        $this->saveUser($user);
    }
}
