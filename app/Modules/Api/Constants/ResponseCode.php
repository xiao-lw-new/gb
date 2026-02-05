<?php

namespace App\Modules\Api\Constants;

class ResponseCode
{
    const SUCCESS = 200;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const VALIDATION_ERROR = 422;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;
    const BUSINESS_ERROR = 600;
}

