<?php

declare(strict_types=1);

namespace App\Domain\User\Projection;

use App\Model\User;
use App\Model\Projector;
use App\Domain\User\Aggregate\UserAggregate;
use App\Domain\User\Event\UserHasBeenArchived;
use App\Domain\User\Event\UserHasBeenCreated;
use App\Domain\User\Event\UserHasBeenDeleted;
use App\Domain\User\Event\UserHasBeenReactivated;
use App\Domain\User\Event\UsernameHasBeenChanged;
use App\Domain\User\Event\UserPasswordHasBeenChanged;
use App\Domain\User\Event\UserProfileHasBeenChanged;
use App\Repository\UserRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

final class UserProjector extends Projector
{

    /** @var EntityManager */
    private $entityManager;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    public function aggregateName(): string
    {
        return UserAggregate::NAME;
    }

    /**
     * @param UserHasBeenArchived $event
     * @throws Exception
     */
    protected function onUserHasBeenArchived(UserHasBeenArchived $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $user->setArchived(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UserHasBeenCreated $event
     * @throws Exception
     */
    protected function onUserHasBeenCreated(UserHasBeenCreated $event): void
    {
        $user = User::withAggregateId($event->aggregateId(), $event->username(), $event->password());
        $user->setRoles($event->roles());
        $user->setEnabled($event->isEnabled());
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UserHasBeenDeleted $event
     * @throws Exception
     */
    protected function onUserHasBeenDeleted(UserHasBeenDeleted $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UserHasBeenReactivated $event
     * @throws Exception
     */
    protected function onUserHasBeenReactivated(UserHasBeenReactivated $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $user->setArchived(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UsernameHasBeenChanged $event
     * @throws Exception
     */
    protected function onUsernameHasBeenChanged(UsernameHasBeenChanged $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $user->setUsername($event->username());
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UserPasswordHasBeenChanged $event
     * @throws Exception
     */
    protected function onUserPasswordHasBeenChanged(UserPasswordHasBeenChanged $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $user->setPassword($event->password());
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @param UserProfileHasBeenChanged $event
     * @throws Exception
     */
    protected function onUserProfileHasBeenChanged(UserProfileHasBeenChanged $event): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $event->aggregateId()]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('User with id: %s not found.', $event->aggregateId()));
        }

        $user->setProfile($event->profile());
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * @throws DBALException
     */
    protected function truncateTable(): void
    {
        $cmd = $this->entityManager->getClassMetadata(User::class);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
    }
}
