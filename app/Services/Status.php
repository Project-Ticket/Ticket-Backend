<?php

namespace App\Services;

use InvalidArgumentException;

class Status
{
    /**
     * Status configuration data
     */
    private static array $statusConfig;

    /**
     * Cache for reverse mapping (name to id)
     */
    private static array $reverseCache = [];

    /**
     * Cache for constant mapping
     */
    private static array $constantCache = [];

    /**
     * Initialize status configuration
     */
    private static function init(): void
    {
        if (!isset(self::$statusConfig)) {
            self::$statusConfig = config('ticketStatus', []);
            self::buildReverseCache();
            self::buildConstantCache();
        }
    }

    /**
     * Build reverse mapping cache for better performance
     */
    private static function buildReverseCache(): void
    {
        foreach (self::$statusConfig as $type => $statuses) {
            self::$reverseCache[$type] = array_flip($statuses);
        }
    }

    /**
     * Build constant cache for dynamic method calls
     */
    private static function buildConstantCache(): void
    {
        foreach (self::$statusConfig as $type => $statuses) {
            $typePrefix = str_replace('Status', '', $type); // userStatus -> user

            foreach ($statuses as $id => $name) {
                $methodName = strtolower($typePrefix . ucfirst(strtolower($name)));
                self::$constantCache[$methodName] = $id;

                // Also create checker methods
                $checkerName = 'is' . ucfirst($typePrefix) . ucfirst(strtolower($name));
                self::$constantCache[$checkerName] = ['type' => $type, 'id' => $id, 'checker' => true];
            }
        }
    }

    /**
     * Magic method to handle dynamic status methods
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        self::init();

        // Handle constant methods like orderPending()
        if (isset(self::$constantCache[$method])) {
            $cached = self::$constantCache[$method];

            // If it's a checker method like isOrderPending($status)
            if (is_array($cached) && isset($cached['checker'])) {
                if (empty($arguments)) {
                    throw new InvalidArgumentException("Method {$method} requires a status argument");
                }
                return $arguments[0] === $cached['id'];
            }

            // If it's a constant method like orderPending()
            return $cached;
        }

        // Handle type-based methods like user(), order(), ticket()
        $possibleType = $method . 'Status';
        if (self::typeExists($possibleType)) {
            return new StatusTypeHelper($possibleType);
        }

        throw new InvalidArgumentException("Method {$method} does not exist");
    }

    /**
     * Get status name by ID and type
     */
    public static function getName(string $type, int $id): ?string
    {
        self::init();
        return self::$statusConfig[$type][$id] ?? null;
    }

    /**
     * Get status ID by name and type
     */
    public static function getId(string $type, string $name): ?int
    {
        self::init();
        $name = strtoupper($name);
        return self::$reverseCache[$type][$name] ?? null;
    }

    /**
     * Get all statuses for a specific type
     */
    public static function getAll(string $type): array
    {
        self::init();
        return self::$statusConfig[$type] ?? [];
    }

    /**
     * Get all status names for a specific type
     */
    public static function getAllNames(string $type): array
    {
        self::init();
        return array_values(self::$statusConfig[$type] ?? []);
    }

    /**
     * Get all status IDs for a specific type
     */
    public static function getAllIds(string $type): array
    {
        self::init();
        return array_keys(self::$statusConfig[$type] ?? []);
    }

    /**
     * Check if status exists
     */
    public static function exists(string $type, int $id): bool
    {
        self::init();
        return isset(self::$statusConfig[$type][$id]);
    }

    /**
     * Check if status name exists
     */
    public static function nameExists(string $type, string $name): bool
    {
        self::init();
        $name = strtoupper($name);
        return isset(self::$reverseCache[$type][$name]);
    }

    /**
     * Check if status type exists
     */
    public static function typeExists(string $type): bool
    {
        self::init();
        return isset(self::$statusConfig[$type]);
    }

    /**
     * Get formatted status with icon/badge class
     */
    public static function getFormatted(string $type, int $id, bool $withIcon = false): array
    {
        $name = self::getName($type, $id);

        if (!$name) {
            return [
                'id' => $id,
                'name' => 'UNKNOWN',
                'class' => 'badge-secondary',
                'icon' => 'question-circle'
            ];
        }

        return [
            'id' => $id,
            'name' => $name,
            'class' => self::getBadgeClass($name),
            'icon' => $withIcon ? self::getIcon($name) : null
        ];
    }

    /**
     * Get CSS class for badge styling
     */
    private static function getBadgeClass(string $status): string
    {
        return match (strtoupper($status)) {
            'ACTIVE', 'PUBLISHED', 'PAID', 'DELIVERED', 'VERIFIED' => 'badge bg-success',
            'PENDING', 'DRAFT', 'PROCESSING' => 'badge bg-warning',
            'INACTIVE', 'CANCELLED', 'SUSPENDED', 'REJECTED', 'REFUNDED', 'EXPIRED' => 'badge bg-danger',
            'USED', 'TRANSFERRED', 'SHIPPED', 'COMPLETED' => 'badge bg-info',
            'TIP' => 'badge bg-primary',
            default => 'badge bg-secondary'
        };
    }


    /**
     * Get icon for status
     */
    private static function getIcon(string $status): string
    {
        return match ($status) {
            'ACTIVE', 'PUBLISHED' => 'check-circle',
            'INACTIVE', 'SUSPENDED' => 'x-circle',
            'PENDING' => 'clock',
            'VERIFIED' => 'shield-check',
            'REJECTED' => 'shield-x',
            'DRAFT' => 'edit',
            'CANCELLED' => 'x',
            'COMPLETED' => 'check',
            'PAID' => 'credit-card',
            'USED' => 'check-square',
            'TRANSFERRED' => 'arrow-right',
            'PROCESSING' => 'loader',
            'SHIPPED' => 'truck',
            'DELIVERED' => 'package-check',
            'REFUNDED' => 'arrow-left',
            'EXPIRED' => 'calendar-x',
            'TIP' => 'map-pin',
            default => 'circle'
        };
    }

    /**
     * Validate status transition
     */
    public static function isValidTransition(string $type, int $fromStatus, int $toStatus): bool
    {
        return match ($type) {
            'orderStatus' => self::validateOrderTransition($fromStatus, $toStatus),
            'ticketStatus' => self::validateTicketTransition($fromStatus, $toStatus),
            'eventStatus' => self::validateEventTransition($fromStatus, $toStatus),
            'merchandiseOrderStatus' => self::validateMerchandiseTransition($fromStatus, $toStatus),
            default => true
        };
    }

    /**
     * Get next possible statuses
     */
    public static function getNextPossibleStatuses(string $type, int $currentStatus): array
    {
        $allStatuses = self::getAll($type);
        $possible = [];

        foreach ($allStatuses as $id => $name) {
            if ($id !== $currentStatus && self::isValidTransition($type, $currentStatus, $id)) {
                $possible[$id] = $name;
            }
        }

        return $possible;
    }

    /**
     * Get status statistics
     */
    public static function getStatistics(string $type, array $statusCounts): array
    {
        $stats = [];
        $total = array_sum($statusCounts);

        foreach ($statusCounts as $statusId => $count) {
            $name = self::getName($type, $statusId);
            $percentage = $total > 0 ? round(($count / $total) * 100, 2) : 0;

            $stats[] = [
                'id' => $statusId,
                'name' => $name,
                'count' => $count,
                'percentage' => $percentage,
                'class' => self::getBadgeClass($name ?? 'UNKNOWN')
            ];
        }

        return [
            'total' => $total,
            'breakdown' => $stats
        ];
    }

    // ========================= VALIDATION METHODS =========================

    /**
     * Order status transition validation
     */
    private static function validateOrderTransition(int $from, int $to): bool
    {
        $validTransitions = [
            1 => [2, 3, 5], // PENDING -> PAID, CANCELLED, EXPIRED
            2 => [4],       // PAID -> REFUNDED
            3 => [],        // CANCELLED -> (no transitions)
            4 => [],        // REFUNDED -> (no transitions)
            5 => []         // EXPIRED -> (no transitions)
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Ticket status transition validation
     */
    private static function validateTicketTransition(int $from, int $to): bool
    {
        $validTransitions = [
            1 => [2, 3, 4], // ACTIVE -> USED, CANCELLED, TRANSFERRED
            2 => [],        // USED -> (no transitions)
            3 => [],        // CANCELLED -> (no transitions)
            4 => [2, 3]     // TRANSFERRED -> USED, CANCELLED
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Event status transition validation
     */
    private static function validateEventTransition(int $from, int $to): bool
    {
        $validTransitions = [
            1 => [2, 3],    // DRAFT -> PUBLISHED, CANCELLED
            2 => [3, 4],    // PUBLISHED -> CANCELLED, COMPLETED
            3 => [],        // CANCELLED -> (no transitions)
            4 => []         // COMPLETED -> (no transitions)
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Merchandise order status transition validation
     */
    private static function validateMerchandiseTransition(int $from, int $to): bool
    {
        $validTransitions = [
            1 => [2, 6],       // PENDING -> PAID, CANCELLED
            2 => [3, 6],       // PAID -> PROCESSING, CANCELLED
            3 => [4, 6],       // PROCESSING -> SHIPPED, CANCELLED
            4 => [5, 8],       // SHIPPED -> DELIVERED, TIP
            5 => [7],          // DELIVERED -> REFUNDED
            6 => [],           // CANCELLED -> (no transitions)
            7 => [],           // REFUNDED -> (no transitions)
            8 => [5]           // TIP -> DELIVERED
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    // ========================= UTILITY METHODS =========================

    /**
     * Get human readable status name
     */
    public static function getStatusName(string $type, int $id): string
    {
        return self::getName($type, $id) ?? 'Unknown';
    }

    /**
     * Get status badge HTML
     */
    public static function getBadgeHtml(string $type, int $id, string $customClass = ''): string
    {
        $formatted = self::getFormatted($type, $id, true);
        $class = $customClass ?: $formatted['class'];

        return sprintf(
            '<span class="%s"><i class="%s"></i> %s</span>',
            $class,
            $formatted['icon'],
            $formatted['name']
        );
    }

    /**
     * Check if status is final (no more transitions possible)
     */
    public static function isFinalStatus(string $type, int $status): bool
    {
        return empty(self::getNextPossibleStatuses($type, $status));
    }

    /**
     * Get active/enabled statuses (typically status 1)
     */
    public static function getActiveStatuses(): array
    {
        self::init();
        $active = [];

        foreach (self::$statusConfig as $type => $statuses) {
            if (isset($statuses[1])) {
                $active[str_replace('Status', '', $type)] = 1;
            }
        }

        return $active;
    }

    /**
     * Bulk status check for collections
     */
    public static function filterByStatuses(array $items, string $statusField, array $allowedStatuses): array
    {
        return array_filter($items, function ($item) use ($statusField, $allowedStatuses) {
            return in_array($item[$statusField] ?? null, $allowedStatuses);
        });
    }

    /**
     * Get available status methods (for debugging)
     */
    public static function getAvailableMethods(): array
    {
        self::init();
        return array_keys(self::$constantCache);
    }
}

/**
 * Helper class for specific status type operations
 */
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
