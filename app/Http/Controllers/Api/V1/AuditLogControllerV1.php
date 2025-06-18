<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AuditLog;
use App\Filters\AuditLogFilter;
use App\Services\Api\V1\AuditLogServiceV1;
use App\Http\Controllers\Api\V1\ApiControllerV1;
use App\Http\Resources\Api\V1\AuditLog\AuditLogResourceV1;
use App\Http\Requests\Api\V1\AuditLog\StoreAuditLogRequestV1;
use App\Http\Requests\Api\V1\AuditLog\UpdateAuditLogRequestV1;

class AuditLogControllerV1 extends ApiControllerV1
{
    public function __construct(private AuditLogServiceV1 $auditLogService) {}

    public function index(AuditLogFilter $filters)
    {
        try {
            $perPage = request()->input('per_page', 10);
            $auditLogs = $this->auditLogService->getAllAuditLogs($filters, $perPage);

            return $this->ok('AuditLogs retrieved successfully', AuditLogResourceV1::collection($auditLogs));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(StoreAuditLogRequestV1 $request)
    {
        try {
            $auditLog = $this->auditLogService->createAuditLog($request->validated());
            return $this->ok('AuditLog created successfully', new AuditLogResourceV1($auditLog));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(AuditLog $auditLog)
    {
        try {
            return $this->ok('AuditLog retrieved successfully', new AuditLogResourceV1($auditLog));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateAuditLogRequestV1 $request, AuditLog $auditLog)
    {
        try {
            $auditLog = $this->auditLogService->updateAuditLog($auditLog, $request->validated());
            return $this->ok('AuditLog updated successfully', new AuditLogResourceV1($auditLog));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(AuditLog $auditLog)
    {
        try {
            $this->auditLogService->deleteAuditLog($auditLog);
            return $this->ok('AuditLog deleted successfully');
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}