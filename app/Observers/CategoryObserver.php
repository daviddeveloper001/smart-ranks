<?php
namespace App\Observers;

use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class CategoryObserver
{
    public function created(Category $category): void
    {
        $this->log('created', $category);
    }

    public function updated(Category $category): void
    {
        $this->log('updated', $category, $category->getChanges());
    }

    public function deleted(Category $category): void
    {
        $this->log('deleted', $category);
    }

    protected function log(string $action, Model $model, array $changes = []): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_id'   => $model->id,
            'auditable_type' => get_class($model),
            'changes'        => !empty($changes) ? json_encode($changes) : null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
