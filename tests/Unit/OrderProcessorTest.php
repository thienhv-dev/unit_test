<?php

namespace Tests\Unit;

use App\FileWriter;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\OrderProcessor;
use App\Order;
use App\APIClient;
use App\Enums\StatusTypeEnum;
use App\APIException;
use App\ApiResponse;

class OrderProcessorTest extends TestCase
{
    /**
     * @var OrderProcessor
     */
    private OrderProcessor $orderProcessor;

    /**
     * @var Order
     */
    private Order $mockOrder;


    /**
     * Set up the test environment.
     */

    protected function setUp(): void
    {
        $this->mockOrder = $this->createMock(Order::class);
        $this->mockFileWriter = $this->createMock(FileWriter::class);
        $this->orderProcessor = new OrderProcessor($this->mockOrder, $this->mockFileWriter);
    }

    /**
     * Test given a valid order when processTypeA then exported status is set.
     */
    public function test_given_a_valid_order_when_processTypeA_then_exported_status_is_set(): void
    {
        // Given
        $userId = 1;
        $this->mockOrder->method('getId')->willReturn(1);
        $this->mockOrder->method('getType')->willReturn('Type A');
        $this->mockOrder->method('getAmount')->willReturn(100);
        $this->mockOrder->method('getFlag')->willReturn(false);
        $this->mockOrder->method('getStatus')->willReturn(StatusTypeEnum::PENDING);
        $this->mockOrder->method('getPriority')->willReturn('Normal');

        // Mock file writing behavior
        $this->mockFileWriter->method('open')->willReturn(true);
        $this->mockFileWriter->method('writeCsv')->willReturn(true);
        $this->mockFileWriter->method('close')->willReturn(true);

        // Expectation: the setStatus method should be called with StatusTypeEnum::EXPORTED
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::EXPORTED);

        // When
        $this->orderProcessor->processTypeA($userId);
    }

    /**
     * Test given a high value order when processTypeA then write high value note to CSV.
     */
    public function test_given_high_value_order_when_processTypeA_then_write_high_value_note_to_csv(): void
    {
        // Given
        $userId = 1;

        // Mock Order
        $this->mockOrder->method('getId')->willReturn(1);
        $this->mockOrder->method('getType')->willReturn('A');
        $this->mockOrder->method('getAmount')->willReturn(200); // Amount > 150
        $this->mockOrder->method('getFlag')->willReturn(false);
        $this->mockOrder->method('getStatus')->willReturn('');
        $this->mockOrder->method('getPriority')->willReturn('');

        // Mock FileWriter
        $fileHandle = fopen('php://temp', 'r+'); // Mở file giả lập
        $this->mockFileWriter->method('open')->willReturn($fileHandle);
        $this->mockFileWriter->method('close')->willReturn(true);

        $this->mockFileWriter->method('writeCsv')
            ->willReturnCallback(function ($handle, $data) {
                fputcsv($handle, $data);
            });

        // Expectation: setStatus should be called with StatusTypeEnum::EXPORTED
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::EXPORTED);

        // When
        $this->orderProcessor->processTypeA($userId);

        rewind($fileHandle);
        $csvContent = stream_get_contents($fileHandle);

        $this->assertStringContainsString(',,,,Note,"High value order"', $csvContent);

        fclose($fileHandle);
    }

    /**
     * Test given file cannot be opened when processTypeA then export failed status is set.
     */
    public function test_given_file_cannot_be_opened_when_processTypeA_then_export_failed_status_is_set(): void
    {
        // Given
        $userId = 1;
        $this->mockFileWriter->method('open')->willReturn(false); // Simulate file opening failure

        // Expect setStatus to be called with EXPORT_FAILED
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::EXPORT_FAILED);

        // When
        $this->orderProcessor->processTypeA($userId);
    }

    /**
     * Test given a value above threshold when processTypeB then processed status is set.
     */
    public function test_given_a_value_above_threshold_when_processTypeB_then_processed_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(2);

        // Mock the Order object to return a valid value for amount
        $mockApiOrder = $this->createMock(Order::class);
        $mockApiOrder->method('getAmount')->willReturn(60); // Valid value (greater than threshold)

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);
        $apiResponse->method('getData')->willReturn($mockApiOrder); // Returns a valid instance of Order

        // Mock API call of APIClient
        $apiClient->method('callAPI')->willReturn($apiResponse);

        // Expectation: setStatus method will be called with StatusTypeEnum::PROCESSED
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::PROCESSED);

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given an API call fails when processTypeB then API failure status is set.
     */
    public function test_given_an_api_call_fails_when_processTypeB_then_api_failure_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(3);

        // Mock APIClient to throw exception when calling callAPI
        $apiClient->method('callAPI')->willThrowException(new APIException());

        // Expectation: setStatus method will be called with StatusTypeEnum::API_FAILURE
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::API_FAILURE);

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given an API amount below threshold when processTypeB then pending status is set.
     */
    public function test_given_api_amount_below_threshold_when_processTypeB_then_pending_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(2);
        $this->mockOrder->method('getFlag')->willReturn(false); // Mock flag is false

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);
        $apiResponse->method('getData')->willReturn($this->createMock(Order::class));
        $apiResponse->getData()->method('getAmount')->willReturn(40); // Amount < THRESHOLD_AMOUNT

        $apiClient->method('callAPI')->willReturn($apiResponse);

        // Expectation: setStatus should be called with StatusTypeEnum::PENDING
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::PENDING);

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given order flag is true when processTypeB then pending status is set.
     */
    public function test_given_order_flag_is_true_when_processTypeB_then_pending_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(2);
        $this->mockOrder->method('getFlag')->willReturn(true);  // Mock flag is false

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);
        $apiResponse->method('getData')->willReturn($this->createMock(Order::class));
        $apiResponse->getData()->method('getAmount')->willReturn(40);  // Amount > THRESHOLD_AMOUNT

        $apiClient->method('callAPI')->willReturn($apiResponse);

        // Expectation: setStatus should be called with StatusTypeEnum::PENDING
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::PENDING);  // This is the expected state

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given API amount below threshold and flag is true when processTypeB then pending status is set.
     */
    public function test_given_api_amount_below_threshold_and_flag_is_true_when_processTypeB_then_pending_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(2);
        $this->mockOrder->method('getFlag')->willReturn(true); // Mock flag is false

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);
        $apiResponse->method('getData')->willReturn($this->createMock(Order::class));
        $apiResponse->getData()->method('getAmount')->willReturn(40); // Amount < THRESHOLD_AMOUNT

        $apiClient->method('callAPI')->willReturn($apiResponse);

        // Expectation: setStatus should be called with StatusTypeEnum::PENDING
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::PENDING);

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given API amount above threshold when processTypeB then error status is set.
     */
    public function test_given_api_amount_above_threshold_when_processTypeB_then_error_status_is_set(): void
    {
        // Given
        $apiClient = $this->createMock(APIClient::class);
        $this->mockOrder->method('getId')->willReturn(2);
        $this->mockOrder->method('getFlag')->willReturn(false); // Flag is false

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);

        // Mock data returned from API, making sure it doesn't fit the expected condition
        $apiResponse->method('getData')->willReturn($this->createMock(Order::class));
        $apiResponse->getData()->method('getAmount')->willReturn(200); // Amount > THRESHOLD_AMOUNT

        // Expectation: setStatus should be called with StatusTypeEnum::API_ERROR (not ERROR)
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::API_ERROR);

        // When
        $this->orderProcessor->processTypeB($apiClient);
    }

    /**
     * Test given API response when handleSuccess then error status is set.
     */
    public function test_given_api_response_when_handle_success_then_error_status_is_set(): void
    {
        // Given
        $this->mockOrder->method('getId')->willReturn(2);

        // Mock API Response
        $apiResponse = $this->createMock(ApiResponse::class);
        $apiResponse->method('getStatus')->willReturn(StatusTypeEnum::SUCCESS);

        // Mock data returned from API, ensuring it will hit the default case
        $apiResponse->method('getData')->willReturn($this->createMock(Order::class));
        $apiResponse->getData()->method('getAmount')->willReturn(100); // Amount is above the threshold
        $this->mockOrder->method('getAmount')->willReturn(200); // Order amount is above ADDITIONAL_AMOUNT
        $this->mockOrder->method('getFlag')->willReturn(false); // Flag is false

        // Expectation: setStatus should be called with StatusTypeEnum::ERROR
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::ERROR);

        // When
        $this->orderProcessor->handleSuccess($apiResponse);
    }

    /**
     * Test given order flag is true when processTypeC then completed status is set.
     */
    public function test_given_order_flag_is_true_when_processTypeC_then_completed_status_is_set(): void
    {
        // Given
        $this->mockOrder->method('getFlag')->willReturn(true);

        // Expectation: setStatus method will be called once with value StatusTypeEnum::COMPLETED
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::COMPLETED);

        // When
        $this->orderProcessor->processTypeC();
    }

    /**
     * Test given order flag is false when processTypeC then in progress status is set.
     */
    public function test_given_order_flag_is_false_when_processTypeC_then_in_progress_status_is_set(): void
    {
        // Given
        $this->mockOrder->method('getFlag')->willReturn(false);

        // When
        $this->mockOrder->expects($this->once())
            ->method('setStatus')
            ->with(StatusTypeEnum::IN_PROGRESS);

        // When
        $this->orderProcessor->processTypeC();
    }
}