<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class StatusTypeEnum extends Enum
{
    // Order statuses
    const NEW = 'new';

    // General statuses
    const SUCCESS  = 'success';
    const PROCESSED = 'processed';
    const PENDING = 'pending';
    const ERROR = 'error';
    const DB_ERROR = 'db_error';

    // Order related statuses
    const COMPLETED = 'completed';
    const IN_PROGRESS = 'IN_PROGRESS';

    // API related statuses
    const API_ERROR = 'api_error';
    const API_FAILURE = 'api_failure';

    //
    const EXPORTED = 'exported';
    const EXPORT_FAILED = 'export_failed';

    const UNKNOWN_TYPE = 'unknown_type';
}
