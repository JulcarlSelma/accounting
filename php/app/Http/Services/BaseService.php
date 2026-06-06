<?php

namespace App\Http\Services;

class BaseService
{
    // For Repository
    protected $repository = null;

    // For other services needed to call
    protected $service = null;

    // For multiple services needed to call
    protected $services = [];

    // For Helper
    protected $helper = null;

    // For multiple helpers
    protected $helpers = [];
}
