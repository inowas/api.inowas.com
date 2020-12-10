<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

class ReactivateUserCommand extends Command
{
    private ?self $userId;

    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->userId = $payload['user_id'] ?? null;
        return $self;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }
}
