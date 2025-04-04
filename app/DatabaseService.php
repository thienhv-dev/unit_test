<?php

namespace App;

interface DatabaseService
{
    /**
     * Retrieves orders for a specific user.
     *
     * @param int $userId The unique identifier of the user
     *
     * @return array An array of orders associated with the user
     */
    public function getOrdersByUser(int $userId): array;

    /**
     * Updates the status and priority of a specific order.
     *
     * @param int $orderId The unique identifier of the order
     * @param string $status The new status of the order
     * @param string $priority The new priority of the order
     *
     * @return bool True if the update was successful, false otherwise
     */
    public function updateOrderStatus(int $orderId, string $status, string $priority): bool;
}