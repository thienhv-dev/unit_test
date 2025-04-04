<?php

namespace App;

class APIResponse
{
    /**
     * @var string $status
     */
    private string $status;

    /**
     * @var Order $data
     */
    private Order $data;

    /**
     * APIResponse constructor.
     *
     * @param string $status The status of the API response
     * @param Order $data The data returned by the API
     */
    public function __construct(string $status, Order $data)
    {
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * Gets the status of the API response.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Gets the data returned by the API.
     *
     * @return Order
     */
    public function getData(): Order
    {
        return $this->data;
    }
}