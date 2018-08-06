<?php

namespace Atnic\LaravelGenerator\Filters;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smartisan\Filters\Filter;

/**
 * Base Filters
 */
class BaseFilter extends Filter
{
    /** @var array Array Searchable */
    protected $searchables = [];

    /** @var array Array Sortable */
    protected $sortables = [];

    /** @var string|null Default sort */
    protected $default_sort = null;

    /** @var int|null Default per page */
    protected $default_per_page = null;

    /**
     * Filter constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = clone $request;
        if (!$this->request->exists('sort') && $this->default_sort) {
            $this->request->merge([ 'sort' => $this->default_sort ]);
        }
        if (!$this->request->exists('per_page') && $this->default_per_page) {
            $this->request->merge([ 'per_page' => $this->default_per_page ]);
        }
    }

    /**
     * Search
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($value)
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'string' ]);
        return $this->builder->when(!$validator->fails(), function ($query) use($value) {
            $query->where(function ($query) use($value) {
                $this->buildSearch($query, $this->searchables, $value);
            });
        });
    }

    /**
     * Recursive Build Search
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|string $searchables
     * @param  string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildSearch($query, $searchables, $value)
    {
        foreach ($searchables as $key => $searchable) {
            if (is_array($searchable)) {
                $query->orWhereHas($key, function ($query) use($searchable, $value) {
                    $query->where(function ($query) use($searchable, $value) {
                        $this->buildSearch($query, $searchable, $value);
                    });
                });
            } else {
                $query->orWhere($query->qualifyColumn($searchable), 'like', '%'.str_replace(' ', '%', $value).'%');
            }
        }
    }

    /**
     * Sort
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sort($value)
    {
        $sorted_columns = $value ? explode('|', $value) : [];
        $sorts = [];
        foreach ($sorted_columns as $key => $sorted_column) {
            $sort = $sorted_column ? explode(',', $sorted_column) : [];
            if (!is_array($sort) || !in_array($sort[0], $this->sortables)) continue;
            array_push($sorts, [
                'column' => $sort[0],
                'dir' => isset($sort[1]) ? $sort[1] : 'asc'
            ]);
        }
        $validator = validator([ 'sorts' => $sorts ], [
            'sorts.*.column' => 'in:'.implode(',', $this->sortables),
            'sorts.*.dir' => 'in:asc,desc'
        ]);
        return $this->builder->when(!$validator->fails(), function ($query) use($sorts) {
            $query->select($query->qualifyColumn('*'));
            foreach ($sorts as $key => $sort) {
                if (str_contains($sort['column'], '.')) {
                    $join = explode('.', $sort['column']);
                    $relation = $query->getModel()->{$join[0]}();
                    if (in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo', 'HasOne', 'MorphOne', 'BelongsToOne' ])) {
                        foreach ($relation->getRelated()->getGlobalScopes() as $key => $scope) {
                            $query->withGlobalScope($key, $scope);
                        }
                        if (in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo' ])) {
                            if (!collect($query->getQuery()->joins)->pluck('table')->contains($relation->getQuery()->getQuery()->from))
                                $this->joinOnSort($query, $relation, $relation->getQuery()->getQuery()->from, $relation->getQualifiedForeignKey(), $relation->getQualifiedOwnerKeyName());
                        } elseif (in_array(class_basename($relation), [ 'HasOne', 'MorphOne' ])) {
                            if (!collect($query->getQuery()->joins)->pluck('table')->contains($relation->getQuery()->getQuery()->from))
                                $this->joinOnSort($query, $relation, $relation->getQuery()->getQuery()->from, $relation->getQualifiedForeignKeyName(), $relation->getQualifiedParentKeyName());
                        } elseif (in_array(class_basename($relation), [ 'BelongsToOne' ])) {
                            if (!collect($query->getQuery()->joins)->pluck('table')->contains($relation->getTable()))
                                $this->joinOnSort($query, $relation, $relation->getTable(), $relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignPivotKeyName());
                            if (!collect($query->getQuery()->joins)->pluck('table')->contains($relation->getQuery()->getQuery()->from))
                                $this->joinOnSort($query, $relation, $relation->getQuery()->getQuery()->from, $relation->getQualifiedRelatedPivotKeyName(), $relation->getRelated()->getQualifiedKeyName());
                        }
                    } else {
                        continue;
                    }
                    $query->orderBy($relation->getRelated()->qualifyColumn($join[1]), $sort['dir']);
                    $query->addSelect(DB::raw($relation->getRelated()->qualifyColumn($join[1]).' as '.$join[0].'_'.$join[1]));
                } else {
                    $query->orderBy($query->qualifyColumn($sort['column']), $sort['dir']);
                }
            }
        });
    }

    /**
     * Join on sort
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param  string $table
     * @param  string $first
     * @param  string $second
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function joinOnSort($query, $relation, $table, $first, $second)
    {
        $query->leftJoin($table, function ($query) use($relation, $first, $second) {
            if (count($wheres = $relation->getQuery()->getQuery()->wheres) > 1) {
                for ($i=(count($wheres) - 1); $i >= 1; $i--) {
                    array_unshift($query->wheres, $wheres[$i]);
                }
            }
            $query->whereColumn($first, $second);
        });
    }

    /**
     * Total per page on pagination
     * @param  int $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function per_page($value)
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'numeric|min:1|max:1000' ]);
        return $this->builder->when(!$validator->fails(), function ($query) use($value) {
            $model = $query->getModel();
            $model->setPerPage($value);
            $query->setModel($model);
        });
    }

    /**
     * Get collection of resources by keys
     * @param  string $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function keys($values)
    {
        $keys = explode(',', $values);
        $model = $this->builder->getModel();
        $validator = validator([ 'values' => $keys ], [ 'values.*' => 'exists:'.$model->getTable().','.$model->getKeyName() ]);
        return $this->builder->when(!$validator->fails(), function ($query) use($keys) {
            $query->whereKey($keys);
        });
    }

    /**
     * With
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($value)
    {
        $withs = explode(',', $value);
        $model = $this->builder->getModel();
        $withs = collect($withs)->filter(function ($with) use($model) {
            if (str_contains($with, '.')) return true;
            return ($model->{$with}() instanceof Relation);
        })->toArray();
        return $this->builder->when(count($withs), function ($query) use($withs) {
            $query->with($withs);
        });
    }
}

