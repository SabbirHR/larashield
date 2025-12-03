<?php

namespace Larashield\Traits;

use Illuminate\Database\Eloquent\Model;

trait ResolvesModel
{
    /**
     * Resolve the model from ID or return the model instance
     *
     * @param mixed $modelOrId The model instance or its ID
     * @param string $modelClass The fully qualified model class name
     * @return Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function resolveModel($modelOrId, string $modelClass): Model
    {
        // If it's already a model instance of the expected class, return it
        if ($modelOrId instanceof $modelClass) {
            return $modelOrId;
        }

        // If it's a Model but wrong class, throw exception
        if ($modelOrId instanceof Model) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected instance of %s, got %s',
                    $modelClass,
                    get_class($modelOrId)
                )
            );
        }

        // If it's an ID (integer or string), find the model or fail
        if (is_numeric($modelOrId) || is_string($modelOrId)) {
            return $modelClass::findOrFail($modelOrId);
        }

        // If it's null or other type, fail
        throw new \InvalidArgumentException(
            sprintf(
                'Cannot resolve model: expected %s instance or ID, got %s',
                $modelClass,
                gettype($modelOrId)
            )
        );
    }
}
