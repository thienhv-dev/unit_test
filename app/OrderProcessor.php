<?php

namespace App;

use App\Enums\StatusTypeEnum;

class OrderProcessor
{
    private const THRESHOLD_AMOUNT = 50;
    private const ADDITIONAL_AMOUNT = 100;
    private const HIGH_VALUE_AMOUNT = 150;

    /**
     * @var Order $order
     */
    private Order $order;

    /**
     * @var FileWriter $fileWriter
     */
    private FileWriter $fileWriter;

    /**
     * OrderProcessor constructor.
     *
     * @param Order      $order
     * @param FileWriter $fileWriter
     */
    public function __construct(Order $order, FileWriter $fileWriter)
    {
        $this->order = $order;
        $this->fileWriter = $fileWriter;
    }

    /**
     * Processes Type A orders for a specific user.
     *
     * @param int $userId
     *
     * @return void
     */
    public function processTypeA(int $userId): void
    {
        // Code for processing Type A orders
        $csvFile = 'orders_type_A_' . $userId . '_' . time() . '.csv';
        $fileHandle = $this->fileWriter->open($csvFile, 'w');

        if ($fileHandle !== false) {
            $this->fileWriter->writeCsv($fileHandle, ['ID', 'Type', 'Amount', 'Flag', 'Status', 'Priority']);
            $this->fileWriter->writeCsv($fileHandle, [
                $this->order->getId(),
                $this->order->getType(),
                $this->order->getAmount(),
                $this->order->getFlag() ? 'true' : 'false',
                $this->order->getStatus(),
                $this->order->getPriority()
            ]);
            if ($this->order->getAmount() > self::HIGH_VALUE_AMOUNT) {
                $this->fileWriter->writeCsv($fileHandle, ['', '', '', '', 'Note', 'High value order']);
            }
            $this->fileWriter->close($fileHandle);
            $this->order->setStatus(StatusTypeEnum::EXPORTED);
        } else {
            $this->order->setStatus(StatusTypeEnum::EXPORT_FAILED);
        }
    }

    /**
     * Processes Type B orders using an API client.
     *
     * @param APIClient $apiClient
     * @return void
     */
    public function processTypeB(APIClient $apiClient): void
    {
        try {
            $apiResponse = $apiClient->callAPI($this->order->getId());
            switch ($apiResponse->getStatus()) {
                case StatusTypeEnum::SUCCESS:
                    $this->handleSuccess($apiResponse);
                    break;
                default:
                    $this->order->setStatus(StatusTypeEnum::API_ERROR);
                    break;
            }
        } catch (APIException $e) {
            $this->order->setStatus(StatusTypeEnum::API_FAILURE);
        }
    }

    /**
     * Handles a successful API response.
     *
     * @param ApiResponse $apiResponse
     *
     * @return void
     */
    public function handleSuccess(ApiResponse $apiResponse): void
    {
        switch (true) {
            case $apiResponse->getData()->getAmount() >= self::THRESHOLD_AMOUNT
                && $this->order->getAmount() < self::ADDITIONAL_AMOUNT:
                $this->order->setStatus(StatusTypeEnum::PROCESSED);
                break;
            case $apiResponse->getData()->getAmount() < self::THRESHOLD_AMOUNT || $this->order->getFlag():
                $this->order->setStatus(StatusTypeEnum::PENDING);
                break;
            default:
                $this->order->setStatus(StatusTypeEnum::ERROR);
                break;
        }
    }

    /**
     * Processes Type C orders.
     *
     * @return void
     */
    public function processTypeC(): void
    {
        if ($this->order->getFlag()) {
            $this->order->setStatus(StatusTypeEnum::COMPLETED);
        } else {
            $this->order->setStatus(StatusTypeEnum::IN_PROGRESS);
        }
    }
}