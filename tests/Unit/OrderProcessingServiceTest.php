<?php

namespace Tests\Unit;
use App\DatabaseException;
use App\Enums\StatusTypeEnum;
use PHPUnit\Framework\TestCase;
use App\OrderProcessingService;
use App\Order;
use App\Enums\OrderTypeEnum;
use App\Enums\PriorityTypeEnum;
use App\OrderProcessor;
use App\DatabaseService;
use App\APIClient;

class OrderProcessingServiceTest extends TestCase
{
    /**
     * @var DatabaseService
     */
    private DatabaseService $dbServiceMock;

    /**
     * @var APIClient
     */
    private APIClient $apiClientMock;

    /**
     * @var OrderProcessor
     */
    private OrderProcessor $orderProcessorMock;

    /**
     * @var OrderProcessingService
     */
    private OrderProcessingService $orderProcessingService;

    /**
     * @var Order
     */
    private Order $orderMock;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        // Mock dependencies
        $this->dbServiceMock = $this->createMock(DatabaseService::class);
        $this->apiClientMock = $this->createMock(APIClient::class);
        $this->orderProcessorMock = $this->createMock(OrderProcessor::class);

        // Mock order
        $this->orderMock = $this->createMock(Order::class);

        // Initialize OrderProcessingService
        $this->orderProcessingService = new OrderProcessingService(
            $this->dbServiceMock,
            $this->apiClientMock,
            $this->orderProcessorMock
        );
    }

    /**
     * Tear given order type A when processed then type A method is called
     */
    public function test_given_order_type_A_when_processed_then_type_A_method_is_called()
    {
        // Given
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::A);

        // Mock `setStatus` to expect 'exported'
        $this->orderMock->expects($this->once())->method('setStatus')->with(StatusTypeEnum::EXPORTED);

        // Mock OrderProcessor's processTypeA to be called once
        $this->orderProcessorMock->expects($this->once())->method('processTypeA')->with($this->anything())
            ->will($this->returnCallback(function($userId) {
                // Ensure that `setStatus('exported')` is called within processTypeA
                $this->orderMock->setStatus(StatusTypeEnum::EXPORTED);
            }));

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order type B when processed then type B method is called
     */
    public function test_given_order_type_B_when_processed_then_type_B_method_is_called()
    {
        // Given
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::B);
        $this->orderMock->expects($this->once())->method('setStatus')->with(StatusTypeEnum::EXPORTED);

        // Mock processTypeB to be called once
        $this->orderProcessorMock->expects($this->once())->method('processTypeB')->with($this->apiClientMock)
            ->will($this->returnCallback(function($apiClient) {
                // Ensure that setStatus('exported') is called within processTypeB
                $this->orderMock->setStatus(StatusTypeEnum::EXPORTED);
            }));

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order type C when processed then type C method is called
     */
    public function test_given_order_type_C_when_processed_then_type_C_method_is_called()
    {
        // Given
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::C);
        $this->orderMock->expects($this->once())->method('setStatus')->with(StatusTypeEnum::EXPORTED);

        // Mock processTypeC to be called once
        $this->orderProcessorMock->expects($this->once())->method('processTypeC')
            ->will($this->returnCallback(function() {
                // Ensure that setStatus('exported') is called after processTypeC
                $this->orderMock->setStatus(StatusTypeEnum::EXPORTED);
            }));

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order with high priority when processed then priority is set to high
     */
    public function test_given_order_with_high_priority_when_processed_then_priority_is_set_to_high()
    {
        // Given: Order with amount greater than the HIGH_PRIORITY_THRESHOLD
        $this->orderMock->method('getAmount')->willReturn(250);
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::A);

        // Expect priority to be set to HIGH
        $this->orderMock->expects($this->once())->method('setPriority')->with(PriorityTypeEnum::HIGH);

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order with low priority when processed then priority is set to low
     */
    public function test_given_order_with_low_priority_when_processed_then_priority_is_set_to_low()
    {
        // Given: Order with amount lower than the HIGH_PRIORITY_THRESHOLD
        $this->orderMock->method('getAmount')->willReturn(100);
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::A);

        // Expect priority to be set to LOW
        $this->orderMock->expects($this->once())->method('setPriority')->with(PriorityTypeEnum::LOW);

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order with unknown type when processed then status is unknown type
     */
    public function test_given_order_with_unknown_type_when_processed_then_status_is_unknown_type()
    {
        // Given: Order with unknown type
        $this->orderMock->method('getType')->willReturn(StatusTypeEnum::UNKNOWN_TYPE);

        // Expect status to be set to 'unknown_type'
        $this->orderMock->expects($this->once())->method('setStatus')->with(StatusTypeEnum::UNKNOWN_TYPE);

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }

    /**
     * Tear given order when API call fails then status is DB error
     */
    public function test_given_order_when_db_update_fails_then_status_is_db_error()
    {
        // Given: Mock DB service to throw DatabaseException
        $this->dbServiceMock->method('updateOrderStatus')->willThrowException(new DatabaseException('DB error'));

        // Mock the order type and status update
        $this->orderMock->method('getType')->willReturn(OrderTypeEnum::A);
        $this->orderMock->expects($this->once())->method('setStatus')->with(StatusTypeEnum::DB_ERROR);

        // When: Call processOrder
        $this->orderProcessingService->processOrder($this->orderMock, 1);
    }
}