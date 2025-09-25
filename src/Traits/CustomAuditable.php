<?php

namespace Larashield\Traits;

use OwenIt\Auditing\Contracts\Auditable;

trait CustomAuditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * Modify audit log before storing.
     */
    public function transformAudit(array $data): array
    {
        $user = auth()->user();

        $data['user_id'] = $user?->id;       // null if no user
        $data['user_type'] = $user?->getMorphClass(); // or get_class($user)
        $data['user_agent'] = request()->header('User-Agent');
        $modelName = class_basename($this);

        if (empty($data['tags'])) {
            if (method_exists($this, 'trashed') && $this->trashed()) {
                $data['tags'] = "{$modelName} Soft Deleted";
            } elseif ($this->wasRecentlyCreated) {
                $data['tags'] = "{$modelName} Created";
            } elseif (method_exists($this, 'trashed') && !$this->trashed() && $this->exists) {
                $data['tags'] = "{$modelName} Restored";
            } elseif ($this->exists && !$this->wasRecentlyCreated) {
                $data['tags'] = "{$modelName} Updated";
            } elseif (!$this->exists) {
                $data['tags'] = "{$modelName} Hard Deleted";
            } else {
                $data['tags'] = "{$modelName} Unknown Event";
            }
        }

        return $data;
    }
}
