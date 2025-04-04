<?php

namespace App;

interface APIClient
{
    /**
     * Call the API with the given order ID.
     *
     * @param string $orderId The order ID to be used in the API call.
     *
     * @return APIResponse The response from the API.
     */
    public function callAPI(string $orderId): APIResponse;
}