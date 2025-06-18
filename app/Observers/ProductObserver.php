<?php
namespace App\Observers;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->log('created', $product);
    }

    public function updated(Product $product): void
    {
        $this->log('updated', $product, $product->getChanges());
    }

    public function deleted(Product $product): void
    {
        $this->log('deleted', $product);
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
