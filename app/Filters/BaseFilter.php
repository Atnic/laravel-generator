<?php

namespace Atnic\LaravelGenerator\Filters;

use Illuminate\Database\Eloquent\Builder;
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
        parent::__construct(clone $request);
        if (!$this->request->exists('sort') && $this->default_sort) {
            $this->request->merge([ 'sort' => $this->default_sort ]);
        }

        $this->default_per_page = $this->default_per_page ? : config('filters.default_per_page', null);
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
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($value) {
            $query->where(function (Builder $query) use($value) {
                $this->buildSearch($query, $this->searchables, $value);
            });
        });
    }

    /**
     * Recursive Build Search
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|string $searchables
     * @param  string $value
     */
    protected function buildSearch($query, $searchables, $value)
    {
        foreach ($searchables as $key => $searchable) {
            if (is_array($searchable)) {
                $query->orWhereHas($key, function (Builder $query) use($searchable, $value) {
                    $query->where(function (Builder $query) use($searchable, $value) {
                        $this->buildSearch($query, $searchable, $value);
                    });
                });
            } else {
                if ($query->getConnection()->getDriverName() == 'pgsql')
                    $query->orWhere($query->qualifyColumn($searchable), 'ilike', '%'.str_replace(' ', '%', $value).'%');
                else
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
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($sorts) {
            $query->select($query->qualifyColumn('*'));
            $relations = [];
            foreach ($sorts as $sort) {
                if (str_contains($sort['column'], '.')) {
                    $join = explode('.', $sort['column']);
                    $relation = Relation::noConstraints(function () use($query, $join) {
                        return $query->getModel()->{$join[0]}();
                    });
                    if (!in_array($relation, $relations) &&
                        in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo', 'HasOne', 'MorphOne', 'BelongsToOne', 'BelongsToThrough' ])) {
                        if (in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo' ])) {
                            $query->leftJoin(DB::raw('('. $this->buildSql($relation->getQuery()).') as '.$relation->getQuery()->getQuery()->from), $relation->getQualifiedOwnerKeyName(), $relation->getQualifiedForeignKey());
                        } elseif (in_array(class_basename($relation), [ 'HasOne', 'MorphOne' ])) {
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()).') as '.$relation->getQuery()->getQuery()->from), $relation->getQualifiedForeignKeyName(), $relation->getQualifiedParentKeyName());
                        } elseif (in_array(class_basename($relation), [ 'BelongsToOne' ])) {
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()->addSelect([
                                    '*' => $relation->getRelated()->qualifyColumn('*') ,
                                    $relation->getForeignPivotKeyName() => $relation->getTable().'.'.$relation->getForeignPivotKeyName()
                                ])).') as '.$relation->getQuery()->getQuery()->from), $relation->getQualifiedParentKeyName(), $relation->getQuery()->getQuery()->from.'.'.$relation->getForeignPivotKeyName());
                        } elseif (in_array(class_basename($relation), [ 'BelongsToThrough' ])) {
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()->addSelect([
                                    '*' => $relation->getRelated()->qualifyColumn('*') ,
                                    $relation->getSecondKeyName() => $relation->getQualifiedSecondOwnerKeyName().' as '.$relation->getSecondKeyName()
                                ])).') as '.$relation->getQuery()->getQuery()->from), $relation->getQualifiedFarKeyName(), $relation->getQuery()->getQuery()->from.'.'.explode('.', $relation->getQualifiedForeignKeyName())[1]);
                        }
                        $query->orderBy($relation->getQuery()->getQuery()->from.'.'.$join[1], $sort['dir']);
                        $query->addSelect(DB::raw($relation->getQuery()->getQuery()->from.'.'.$join[1].' as '.$join[0].'_'.$join[1]));
                    }
                    $relations[] = $relation;
                } else {
                    $query->orderBy($query->qualifyColumn($sort['column']), $sort['dir']);
                }
            }
        });
    }

    /**
     * @param Builder $query
     * @return null|string|string[]
     */
    protected function buildSql(Builder $query)
    {
        $sql = $query->toSql();
        foreach($query->getBindings() as $binding)
        {
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    /**
     * Total per page on pagination
     * @param  int $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function per_page($value)
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'numeric|min:1|max:1000' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($value) {
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
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($keys) {
            $query->whereKey($keys);
        });
    }

    /**
     * Get collection of resources excepts resources with keys
     * @param  string $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function except_keys($values)
    {
        $keys = explode(',', $values);
        $model = $this->builder->getModel();
        $validator = validator([ 'values' => $keys ], [ 'values.*' => 'exists:'.$model->getTable().','.$model->getKeyName() ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($keys) {
            $query->whereKeyNot($keys);
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
            if (str_contains($with, '.')) return ($model->{explode('.', $with)[0]}() instanceof Relation);
            return ($model->{$with}() instanceof Relation);
        })->toArray();
        return $this->builder->when(count($withs), function (Builder $query) use($withs) {
            $query->with($withs);
        });
    }

    /**
     * Appends
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function appends($value)
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'string' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($value) {
            $appends = explode(',', $value);
            $model = $query->getModel();
            $model->setAppends($appends);
            $query->setModel($model);
        });
    }

    /**
     * Get collection of resources by has relation
     * @param  string $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function has($values)
    {
        $hases = explode(',', $values);
        $model = $this->builder->getModel();
        $hases = collect($hases)->filter(function ($has) use($model) {
            if (str_contains($has, '.')) return ($model->{explode('.', $has)[0]}() instanceof Relation);
            return ($model->{$has}() instanceof Relation);
        })->toArray();
        $validator = validator([ 'values' => $hases ], [ 'values' => 'array|min:1' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($hases) {
            foreach ($hases as $has) {
                $query->has($has);
            }
        });
    }


    /**
     * Get collection of resources by doesnt_have relation
     * @param  string $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function doesnt_have($values)
    {
        $doesnt_haves = explode(',', $values);
        $model = $this->builder->getModel();
        $doesnt_haves = collect($doesnt_haves)->filter(function ($doesnt_have) use($model) {
            if (str_contains($doesnt_have, '.')) return ($model->{explode('.', $doesnt_have)[0]}() instanceof Relation);
            return ($model->{$doesnt_have}() instanceof Relation);
        })->toArray();
        $validator = validator([ 'values' => $doesnt_haves ], [ 'values' => 'array|min:1' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($doesnt_haves) {
            foreach ($doesnt_haves as $doesnt_have) {
                $query->doesntHave($doesnt_have);
            }
        });
    }
}
