<?php

namespace App;

use App\Enums\PriorityTypeEnum;
use App\Enums\StatusTypeEnum;

class Order
{
    /**
     * @var int $id
     */
    private int $id;

    /**
     * @var string $type
     */
    private string $type;

    /**
     * @var int $amount
     */
    private int $amount;

    /**
     * @var bool $flag
     */
    private bool $flag;

    /**
     * @var string $status
     */
    private string $status;

    /**
     * @var string $priority
     */
    private string $priority;

    /**
     * Order constructor.
     *
     * @param int $id
     * @param string $type
     * @param float $amount
     * @param bool $flag
     */
    public function __construct(int $id, string $type, float $amount, bool $flag)
    {
        $this->id = $id;
        $this->type = $type;
        $this->amount = $amount;
        $this->flag = $flag;
        $this->status = StatusTypeEnum::NEW;
        $this->priority = PriorityTypeEnum::LOW;
    }

    /**
     * Gets the unique identifier of the order.
     *
     * @return int The unique identifier of the order
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the type of the order.
     *
     * @return string The type of the order
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the amount associated with the order.
     *
     * @return int The amount associated with the order
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Gets the flag indicating a special condition for the order.
     *
     * @return bool The flag indicating a special condition for the order
     */
    public function getFlag(): bool
    {
        return $this->flag;
    }

    /**
     * Gets the current status of the order.
     *
     * @return string The current status of the order
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Sets the status of the order.
     *
     * @param string $status The new status of the order
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Gets the priority level of the order.
     *
     * @return string The priority level of the order
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * Sets the priority level of the order.
     *
     * @param string $priority The new priority level of the order
     */
    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }
}