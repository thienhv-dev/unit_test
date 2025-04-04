<?php

namespace App;

use App\Enums\OrderTypeEnum;
use App\Enums\PriorityTypeEnum;
use App\Enums\StatusTypeEnum;

class OrderProcessingService
{
    /**
     * @var int $HIGH_PRIORITY_THRESHOLD
     */
    private const HIGH_PRIORITY_THRESHOLD = 200;

    /**
     * @var DatabaseService $dbService
     */
    private DatabaseService $dbService;

    /**
     * @var APIClient $apiClient
     */
    private APIClient $apiClient;

    /**
     * @var OrderProcessor $orderProcessor
     */
    private OrderProcessor $orderProcessor;

    /**
     * OrderProcessingService constructor.
     *
     * @param DatabaseService $dbService
     * @param APIClient       $apiClient
     * @param OrderProcessor  $orderProcessor
     */
    public function __construct(
        DatabaseService $dbService,
        APIClient $apiClient,
        OrderProcessor $orderProcessor,
    ) {
        $this->dbService = $dbService;
        $this->apiClient = $apiClient;
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * Processes a single order.
     *
     * @param Order $order
     * @param int   $userId
     *
     * @return void
     */
    public function processOrder(Order $order, int $userId): void
    {
        switch ($order->getType()) {
            case OrderTypeEnum::A:
                $this->orderProcessor->processTypeA($userId);
                break;
            case OrderTypeEnum::B:
                $this->orderProcessor->processTypeB($this->apiClient);
                break;
            case OrderTypeEnum::C:
                $this->orderProcessor->processTypeC();
                break;
            default:
                $order->setStatus(StatusTypeEnum::UNKNOWN_TYPE);
                break;
        }
        $this->updateOrderPriority($order);
        $this->updateOrderInDatabase($order);
    }

    /**
     * Updates the priority of an order.
     *
     * @param Order $order The order to update
     * @return void
     */
    private function updateOrderPriority(Order $order): void
    {
        if ($order->getAmount() > self::HIGH_PRIORITY_THRESHOLD) {
            $order->setPriority(PriorityTypeEnum::HIGH);
        } else {
            $order->setPriority(PriorityTypeEnum::LOW);
        }
    }

    /**
     * Updates the order status and priority in the database.
     *
     * @param Order $order The order to update
     * @return void
     */
    public function updateOrderInDatabase(Order $order): void
    {
        try {
            $this->dbService->updateOrderStatus($order->getId(), $order->getStatus(), $order->getPriority());
        } catch (DatabaseException $e) {
            $order->setStatus(StatusTypeEnum::DB_ERROR);
        }
    }
}