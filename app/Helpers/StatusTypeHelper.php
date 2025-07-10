<?php

namespace App\Helpers;

use App\Services\Status;

class StatusTypeHelper
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getName(int $id): ?string
    {
        return Status::getName($this->type, $id);
    }

    public function getId(string $name): ?int
    {
        return Status::getId($this->type, $name);
    }

    public function getAll(): array
    {
        return Status::getAll($this->type);
    }

    public function getAllNames(): array
    {
        return Status::getAllNames($this->type);
    }

    public function getAllIds(): array
    {
        return Status::getAllIds($this->type);
    }

    public function exists(int $id): bool
    {
        return Status::exists($this->type, $id);
    }

    public function nameExists(string $name): bool
    {
        return Status::nameExists($this->type, $name);
    }

    public function getFormatted(int $id, bool $withIcon = false): array
    {
        return Status::getFormatted($this->type, $id, $withIcon);
    }

    public function isValidTransition(int $from, int $to): bool
    {
        return Status::isValidTransition($this->type, $from, $to);
    }

    public function getNextPossibleStatuses(int $currentStatus): array
    {
        return Status::getNextPossibleStatuses($this->type, $currentStatus);
    }

    public function getStatistics(array $statusCounts): array
    {
        return Status::getStatistics($this->type, $statusCounts);
    }

    /**
     * Magic method to handle status-specific methods
     */
    public function __call(string $method, array $arguments): mixed
    {
        $statuses = Status::getAll($this->type);

        // Handle methods like active(), pending(), etc.
        $statusName = strtoupper($method);
        if (in_array($statusName, $statuses)) {
            return Status::getId($this->type, $statusName);
        }

        // Handle methods like isActive($status), isPending($status), etc.
        if (str_starts_with($method, 'is') && strlen($method) > 2) {
            $statusName = strtoupper(substr($method, 2));
            if (in_array($statusName, $statuses)) {
                if (empty($arguments)) {
                    throw new InvalidArgumentException("Method {$method} requires a status argument");
                }
                $statusId = Status::getId($this->type, $statusName);
                return $arguments[0] === $statusId;
            }
        }

        throw new InvalidArgumentException("Method {$method} does not exist for {$this->type}");
    }
}
