<?php

namespace Atnic\LaravelGenerator\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

trait CascadeSoftDeletes
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            foreach ($model->getCascadeSoftDeletes() as $cascadeSoftDelete) {
                if (!method_exists($model, $cascadeSoftDelete) || !($model->{$cascadeSoftDelete}() instanceof Relation)) continue;
                $related = $model->{$cascadeSoftDelete}()->getRelated();
                if ($related && in_array(SoftDeletes::class, class_uses_recursive($related))) {
                    if ($model->isForceDeleting())
                        $model->{$cascadeSoftDelete}()->forceDelete();
                    else
                        $model->{$cascadeSoftDelete}()->delete();
                }
            }
        });

        static::restoring(function ($model) {
            foreach ($model->getCascadeSoftDeletes() as $cascadeSoftDelete) {
                if (!method_exists($model, $cascadeSoftDelete) || !($model->{$cascadeSoftDelete}() instanceof Relation)) continue;
                $related = $model->{$cascadeSoftDelete}()->getRelated();
                if ($related && in_array(SoftDeletes::class, class_uses_recursive($related))) {
                    $model->{$cascadeSoftDelete}()->where($related->getQualifiedDeletedAtColumn(), '>=', $model->deleted_at ? $model->deleted_at->subSecond() : null)->restore();
                }
            }
        });
    }

    /**
     * @return array
     */
    public function getCascadeSoftDeletes()
    {
        return isset($this->cascadeSoftDeletes) ? $this->cascadeSoftDeletes : [];
    }
}
