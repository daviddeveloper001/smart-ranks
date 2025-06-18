<?php

namespace App\Services\Api\V1;

use App\Models\AuditLog;
use App\Exceptions\AuditLogException;
use App\Repositories\V1\AuditLogRepositoryV1;
use Illuminate\Http\Response;

class AuditLogServiceV1
{
    public function __construct(private AuditLogRepositoryV1 $auditLogRepository) {}

    public function getAllAuditLogs($filters, $perPage)
    {
        try {
            return AuditLog::filter($filters)->paginate($perPage);
        } catch (\Exception $e) {
            throw new AuditLogException(
                'Failed to retrieve AuditLogs',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function getAuditLogById(AuditLog $auditLog)
    {
        try {
            $result = $this->auditLogRepository->find($auditLog);
            if (!$result) {
                throw new AuditLogException('AuditLog not found', Response::HTTP_NOT_FOUND);
            }
            return $result;
        } catch (\Exception $e) {
            throw new AuditLogException(
                'Failed to retrieve AuditLog',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function createAuditLog(array $data)
    {
        try {
            return $this->auditLogRepository->create($data);
        } catch (\Exception $e) {
            throw new AuditLogException(
                'Failed to create AuditLog',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function updateAuditLog(AuditLog $auditLog, array $data)
    {
        try {
            return $this->auditLogRepository->update($auditLog, $data);
        } catch (\Exception $e) {
            throw new AuditLogException(
                'Failed to update AuditLog',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function deleteAuditLog(AuditLog $auditLog)
    {
        try {
            return $this->auditLogRepository->delete($auditLog);
        } catch (\Exception $e) {
            throw new AuditLogException(
                'Failed to delete AuditLog',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }
}