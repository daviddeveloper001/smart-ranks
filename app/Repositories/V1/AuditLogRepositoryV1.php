<?php

namespace App\Repositories\V1;

use App\Models\AuditLog;
use App\Repositories\V1\BaseRepositoryV1;

class AuditLogRepositoryV1 extends BaseRepositoryV1
{
    const RELATIONS = [];

    public function __construct(AuditLog $auditLog)
    {
        parent::__construct($auditLog, self::RELATIONS);
    }
}