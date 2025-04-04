<?php

namespace Tests\Unit;
use App\APIResponse;
use App\Enums\StatusTypeEnum;
use App\Order;
use App\Enums\OrderTypeEnum;
use PHPUnit\Framework\TestCase;

class APIResponseTest extends TestCase
{
    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var APIResponse
     */
    private APIResponse $apiResponse;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        // Create an Order object with sample data
        $this->order = new Order(1, OrderTypeEnum::A, 100, true);

        // Create an APIResponse object with the order
        $this->apiResponse = new APIResponse(StatusTypeEnum::SUCCESS, $this->order);
    }

    /**
     * Tear given valid status and data when constructed then properties are initialized correctly
     */
    public function test_given_valid_status_and_data_when_constructed_then_properties_are_initialized_correctly(): void
    {
        // Given: APIResponse is constructed with a status and order data
        $apiResponse = new APIResponse(StatusTypeEnum::SUCCESS, $this->order);

        // When: The APIResponse is constructed
        // Then: The status and data should be initialized correctly
        $this->assertEquals(StatusTypeEnum::SUCCESS, $apiResponse->getStatus());
        $this->assertEquals($this->order, $apiResponse->getData());
    }

    /**
     * Tear given apiResponse when getStatus is called then correct status is returned
     */
    public function test_given_apiResponse_when_getStatus_is_called_then_correct_status_is_returned(): void
    {
        // Given: An APIResponse with a specific status
        $status = StatusTypeEnum::SUCCESS;

        // When: The getStatus method is called
        $result = $this->apiResponse->getStatus();

        // Then: The returned status should match the expected value
        $this->assertEquals($status, $result);
    }

    /**
     * Tear given apiResponse when getData is called then correct data is returned
     */
    public function test_given_apiResponse_when_getData_is_called_then_correct_data_is_returned(): void
    {
        // Given: An APIResponse with a specific order data
        $expectedOrder = $this->order;

        // When: The getData method is called
        $result = $this->apiResponse->getData();

        // Then: The returned data should match the expected order
        $this->assertEquals($expectedOrder, $result);
    }
}