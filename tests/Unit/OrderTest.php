<?php

namespace Tests\Unit;

use App\Order;
use App\Enums\PriorityTypeEnum;
use App\Enums\StatusTypeEnum;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private Order $order;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        // Create a new order object with sample values
        $this->order = new Order(1, 'A', 100.50, true);
    }

    /**
     * Tear given valid order when constructed then properties are initialized correctly.
     */
    public function test_given_valid_order_when_constructed_then_properties_are_initialized_correctly(): void
    {
        // Given: An order with valid data
        $order = new Order(1, 'A', 100, true);

        // When: The order object is constructed
        // Then: The properties should be correctly initialized
        $this->assertEquals(1, $order->getId());
        $this->assertEquals('A', $order->getType());
        $this->assertEquals(100, $order->getAmount());  // Compare as a int
        $this->assertTrue($order->getFlag());
        $this->assertEquals(StatusTypeEnum::NEW, $order->getStatus());  // Default status is NEW
        $this->assertEquals(PriorityTypeEnum::LOW, $order->getPriority());  // Default priority is LOW
    }

    /**
     * Tear given order when getters are called then correct values are returned.
     */
    public function test_given_order_when_getters_are_called_then_correct_values_are_returned(): void
    {
        // Given: An order with specific values
        $order = new Order(1, 'A', 100, true);

        // When: Getter methods are called
        // Then: They should return the correct values
        $this->assertEquals(1, $order->getId());
        $this->assertEquals('A', $order->getType());
        $this->assertEquals(100, $order->getAmount());
        $this->assertTrue($order->getFlag());
    }

    /**
     * Tear given valid order when setStatus is called then status is updated correctly.
     */
    public function test_given_valid_status_when_setStatus_is_called_then_status_is_updated_correctly(): void
    {
        // Given: An order with initial status
        $order = new Order(1, 'A', 100.50, true);

        // When: The setStatus method is called with a new status
        $order->setStatus(StatusTypeEnum::EXPORTED);

        // Then: The order's status should be updated correctly
        $this->assertEquals(StatusTypeEnum::EXPORTED, $order->getStatus());
    }

    /**
     * Tear given valid priority when setPriority is called then priority is updated correctly.
     */
    public function test_given_valid_priority_when_setPriority_is_called_then_priority_is_updated_correctly(): void
    {
        // Given: An order with initial priority
        $order = new Order(1, 'A', 100.50, true);

        // When: The setPriority method is called with a new priority
        $order->setPriority(PriorityTypeEnum::HIGH);

        // Then: The order's priority should be updated correctly
        $this->assertEquals(PriorityTypeEnum::HIGH, $order->getPriority());
    }

    /**
     * Tear given status updated when getters are called then updated values are returned.
     */
    public function test_given_status_updated_when_getters_are_called_then_updated_values_are_returned(): void
    {
        // Given: An order with initial status and priority
        $order = new Order(1, 'A', 100.50, true);

        // When: The status and priority are updated using setters
        $order->setStatus(StatusTypeEnum::EXPORTED);
        $order->setPriority(PriorityTypeEnum::HIGH);

        // Then: The updated values should be returned by getters
        $this->assertEquals(StatusTypeEnum::EXPORTED, $order->getStatus());
        $this->assertEquals(PriorityTypeEnum::HIGH, $order->getPriority());
    }
}