<?php

declare(strict_types=1);

namespace App\Domain\User\CommandHandler;

use App\Domain\User\Aggregate\UserAggregate;
use App\Model\ProjectorCollection;
use App\Repository\AggregateRepository;
use App\Domain\User\Command\ChangeUsernameCommand;
use App\Domain\User\Event\UsernameHasBeenChanged;
use App\Domain\User\Projection\UserProjector;
use App\Model\User;
use App\Service\UserManager;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use RuntimeException;

class ChangeUsernameCommandHandler
{
    private AggregateRepository $aggregateRepository;
    private ProjectorCollection $projectors;
    private UserManager $userManager;

    public function __construct(AggregateRepository $aggregateRepository, UserManager $userManager, ProjectorCollection $projectors)
    {
        $this->aggregateRepository = $aggregateRepository;
        $this->projectors = $projectors;
        $this->userManager = $userManager;
    }

    /**
     * @param ChangeUsernameCommand $command
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function __invoke(ChangeUsernameCommand $command)
    {
        $isAdmin = $command->metadata()['is_admin'];
        $userId = $command->metadata()['user_id'];

        if (($isAdmin && $command->userId())) {
            $userId = $command->userId();
        }

        // Is it different from the old one?
        $user = $this->userManager->findUserById($userId);

        if (!$user instanceof User) {
            throw new RuntimeException('User not found', 404);
        }

        if ($user->getUsername() === $command->username()) {
            // Nothing to change
            return;
        }

        $user = $this->userManager->findUserByUsername($command->username());
        if ($user instanceof User) {
            throw new RuntimeException('Username already in use.', 400);
        }

        $aggregateId = $userId;
        $event = UsernameHasBeenChanged::fromParams($aggregateId, $command->username());
        $aggregate = $this->aggregateRepository->findAggregateById(UserAggregate::class, $aggregateId);
        $aggregate->apply($event);

        $this->aggregateRepository->storeEvent($event);
        $projector = $this->projectors->getProjector(UserProjector::class);
        if (!$projector) {
            throw new RuntimeException(sprintf('Projector %s not found.', UserProjector::class));
        }
        $projector->apply($event);
    }
}
