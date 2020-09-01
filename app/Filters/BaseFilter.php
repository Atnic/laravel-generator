<?php

namespace Atnic\LaravelGenerator\Filters;

use Atnic\LaravelGenerator\Database\Eloquent\Relations\BelongsToOne;
use Atnic\LaravelGenerator\Database\Eloquent\Relations\BelongsToThrough;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Smartisan\Filters\Filter;

/**
 * Base Filters
 */
class BaseFilter extends Filter
{
    /** @var array Array Findable */
    protected $findables = [];

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
    public function __construct(Request $request = null)
    {
        parent::__construct(clone ($request ?? request()));
        if (!$this->request->exists('sort') && $this->default_sort) {
            $this->request->merge([ 'sort' => $this->default_sort ]);
        }

        $this->default_per_page = $this->default_per_page ? : config('filters.default_per_page', null);
        if (!$this->request->exists('per_page') && $this->default_per_page) {
            $this->request->merge([ 'per_page' => $this->default_per_page ]);
        }
    }

    public function __call($name, $arguments)
    {
        if (Str::contains($name, '__')) {
            $operations = explode('__', $name, 2);
            return $this->{$operations[1]}($operations[0], $arguments[0]);
        }
        elseif (isset($this->findables[$name]) || in_array($name, $this->findables))
            return $this->find("{$name}:{$arguments[0]}");
        elseif (isset($this->searchables[$name]) || in_array($name, $this->searchables))
            return $this->search($arguments[0], isset($this->searchables[$name]) ? [ $name => $this->searchables[$name] ] : [ $name ]);
    }

    /**
     * Fetch all relevant filters from the request.
     *
     * @return array
     */
    protected function getFilters()
    {
        $filters = array_diff(get_class_methods($this), [
            '__construct', '__call', 'apply', 'getFilters', 'getFindables', 'getSearchables', 'getSortables', 'buildSearch', 'buildSql',
            'eq', 'ne', 'lt', 'lte', 'gt', 'gte', 'between', 'in', 'null', 'not_null', 'begins_with', 'contains'
        ]);
        $filters = array_merge($filters, array_map(function ($findable, $key) {
            return is_array($findable) ? $key : $findable;
        }, $this->findables, array_keys($this->findables)));
        $filters = array_merge($filters, array_map(function ($searchable, $key) {
            return is_array($searchable) ? $key : $searchable;
        }, $this->searchables, array_keys($this->searchables)));
        $filters = array_merge($filters, array_filter($this->request->keys(), function ($key) {
            return Str::contains($key, '__');
        }));

        return Arr::only($this->request->query(), array_unique($filters));
    }

    /**
     * @return array
     */
    public function getFindables()
    {
        return $this->findables;
    }

    /**
     * @return array
     */
    public function getSearchables()
    {
        return $this->searchables;
    }

    /**
     * @return array
     */
    public function getSortables()
    {
        return $this->sortables;
    }

    /**
     * Sort
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function find($value)
    {
        $finded_columns = $value ? explode('|', $value) : [];
        $finds = [];
        foreach ($finded_columns as $key => $finded_column) {
            $find = $finded_column ? explode('=', $finded_column) : [];
            if (!is_array($find) || !in_array($find[0], $this->findables) || !$find[1]) continue;
            array_push($finds, [
                'column' => $find[0],
                'values' => explode(',', $find[1])
            ]);
        }
        $validator = validator(['finds' => $finds], [
            'finds.*.column' => 'in:' . implode(',', $this->findables),
            'finds.*.values' => 'array'
        ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($finds) {
            foreach ($finds as $find) {
                $query->whereIn($query->qualifyColumn($find['column']), $find['values']);
            };
        });
    }

    /**
     * Search
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($value, $searchables = [])
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'string' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($value, $searchables) {
            $query->where(function (Builder $query) use($value, $searchables) {
                $this->buildSearch($query, empty($searchables) ? $this->searchables : $searchables, $value);
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
        /** @var Connection $connection */
        $connection = $query->getConnection();
        foreach ($searchables as $key => $searchable) {
            if (is_array($searchable)) {
                $query->orWhereHas($key, function (Builder $query) use($searchable, $value) {
                    $query->where(function (Builder $query) use($searchable, $value) {
                        $this->buildSearch($query, $searchable, $value);
                    });
                });
            } else {
                if ($connection->getDriverName() == 'pgsql')
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
            if (empty($query->getQuery()->columns)) $query->select([ '*' => $query->qualifyColumn('*') ]);
            foreach ($sorts as $sort) {
                if (str_contains($sort['column'], '.')) {
                    $join = explode('.', $sort['column']);
                    /** @var Relation $relation */
                    $relation = Relation::noConstraints(function () use($query, $join) {
                        return $query->getModel()->{$join[0]}();
                    });
                    $related = clone $relation->getModel();
                    $related->setTable('t'.strtolower(str_random(8)));
                    if (in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo', 'HasOne', 'MorphOne', 'BelongsToOne', 'BelongsToThrough' ])) {
                        if (in_array(class_basename($relation), [ 'BelongsTo', 'MorphTo' ])) {
                            /** @var BelongsTo|MorphTo $relation */
                            $query->leftJoin(DB::raw('('. $this->buildSql($relation->getQuery()).') as '.$related->getTable()), $related->qualifyColumn($relation->getOwnerKey()), $relation->getQualifiedForeignKey());
                        } elseif (in_array(class_basename($relation), [ 'HasOne', 'MorphOne' ])) {
                            /** @var HasOne|MorphOne $relation */
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()).') as '.$related->getTable()), $related->qualifyColumn($relation->getForeignKeyName()), $relation->getQualifiedParentKeyName());
                        } elseif (in_array(class_basename($relation), [ 'BelongsToOne' ])) {
                            /** @var BelongsToOne $relation */
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()->addSelect([
                                    '*' => $relation->getRelated()->qualifyColumn('*') ,
                                    $relation->getForeignPivotKeyName() => $relation->getTable().'.'.$relation->getForeignPivotKeyName()
                                ])).') as '.$related->getTable()), $relation->getQualifiedParentKeyName(), $related->getTable().'.'.$relation->getForeignPivotKeyName());
                        } elseif (in_array(class_basename($relation), [ 'BelongsToThrough' ])) {
                            /** @var BelongsToThrough $relation */
                            $query->leftJoin(DB::raw('('.$this->buildSql($relation->getQuery()->addSelect([
                                    '*' => $relation->getRelated()->qualifyColumn('*') ,
                                    $relation->getSecondKeyName() => $relation->getQualifiedSecondOwnerKeyName().' as '.$relation->getSecondKeyName()
                                ])).') as '.$related->getTable()), $relation->getQualifiedFarKeyName(), $related->getTable().'.'.explode('.', $relation->getQualifiedForeignKeyName())[1]);
                        }
                        $query->orderByRaw("({$related->getTable()}.{$join[1]} IS NULL)");
                        $query->orderBy($related->getTable().'.'.$join[1], $sort['dir']);
                        $query->addSelect(DB::raw($related->getTable().'.'.$join[1].' as '.$join[0].'_'.$join[1]));
                    }
                } else {
                    $query->orderByRaw("({$query->qualifyColumn($sort['column'])} IS NULL)");
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
            if (Str::contains($with, '.')) return method_exists($model, explode('.', $with)[0]) && ($model->{explode('.', $with)[0]}() instanceof Relation);
            return method_exists($model, $with) && $model->{$with}() instanceof Relation;
        })->toArray();
        return $this->builder->when(count($withs), function (Builder $query) use($withs) {
            $query->with($withs);
        });
    }

    /**
     * With Count
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with_count($value)
    {
        $with_counts = explode(',', $value);
        $model = $this->builder->getModel();
        $with_counts = collect($with_counts)->filter(function ($with_count) use($model) {
            if (Str::contains($with_count, '.')) return method_exists($model, explode('.', $with_count)[0]) && ($model->{explode('.', $with_count)[0]}() instanceof Relation);
            return method_exists($model, $with_count) && $model->{$with_count}() instanceof Relation;
        })->toArray();
        return $this->builder->when(count($with_counts), function (Builder $query) use($with_counts) {
            if (empty($query->getQuery()->columns)) $query->select([ '*' => $query->qualifyColumn('*') ]);
            $query->withCount($with_counts);
        });
    }

    /**
     * With
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function appends($value)
    {
        $appends = explode(',', $value);
        $validator = validator([ 'values' => $appends ], [ 'values.*' => 'string' ]);
        $appends = collect($appends)->mapWithKeys(function ($append) {
            return [ $append => DB::raw("NULL as $append") ];
        })->toArray();
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($appends) {
            if (empty($query->getQuery()->columns)) $query->select([ '*' => $query->qualifyColumn('*') ]);
            $query->addSelect($appends);
        });
    }

    /**
     * Select
     * @param $values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function select($values)
    {
        $selects = explode(',', $values);
        $selects = collect($selects)->flatMap(function ($select) {
            if (Str::contains($select, '.')) return [ $select => $select ];
            $column = $this->builder->qualifyColumn($select);
            if ($column instanceof Expression) return [ $select => DB::raw("{$column} as $select") ];
            else return [ $select => $column ];
        })->toArray();
        $validator = validator([ 'values' => $selects ], [ 'values' => 'array|min:1' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($selects) {
            $query->select($selects);
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
            if (Str::contains($has, '.')) return method_exists($model, explode('.', $has)[0]) && ($model->{explode('.', $has)[0]}() instanceof Relation);
            return method_exists($model, $has) && $model->{$has}() instanceof Relation;
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
            if (Str::contains($doesnt_have, '.')) return method_exists($model, explode('.', $doesnt_have)[0]) && ($model->{explode('.', $doesnt_have)[0]}() instanceof Relation);
            return method_exists($model, $doesnt_have) && $model->{$doesnt_have}() instanceof Relation;
        })->toArray();
        $validator = validator([ 'values' => $doesnt_haves ], [ 'values' => 'array|min:1' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($doesnt_haves) {
            foreach ($doesnt_haves as $doesnt_have) {
                $query->doesntHave($doesnt_have);
            }
        });
    }

    /**
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function without_global_scopes($value)
    {
        $validator = validator([ 'value' => $value ], [ 'value' => 'boolean' ]);
        return $this->builder->when(!$validator->fails(), function (Builder $query) use($value) {
            if ($value) $query->withoutGlobalScopes()->byUser();
        });
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function eq($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '=', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function ne($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '<>', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function lt($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '<', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function lte($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '<=', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function gt($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '>', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function gte($column, $value)
    {
        return $this->builder->where($this->builder->qualifyColumn($column), '>=', $value);
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function between($column, $value)
    {
        return $this->builder->whereBetween($this->builder->qualifyColumn($column), explode(',', $value));
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function in($column, $value)
    {
        return $this->builder->whereIn($this->builder->qualifyColumn($column), explode(',', $value));
    }

    /**
     * @param string $column
     * @return Builder
     */
    public function null($column)
    {
        return $this->builder->whereNull($this->builder->qualifyColumn($column));
    }

    /**
     * @param string $column
     * @return Builder
     */
    public function not_null($column)
    {
        return $this->builder->whereNotNull($this->builder->qualifyColumn($column));
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function begins_with($column, $value)
    {
        /** @var Connection $connection */
        $connection = $this->builder->getConnection();
        if ($connection->getDriverName() == 'pgsql')
            return $this->builder->where($this->builder->qualifyColumn($column), 'ilike', "%$value");
        else
            return $this->builder->where($this->builder->qualifyColumn($column), 'like', "%$value");
    }

    /**
     * @param string $column
     * @param string $value
     * @return Builder
     */
    public function contains($column, $value)
    {
        /** @var Connection $connection */
        $connection = $this->builder->getConnection();
        if ($connection->getDriverName() == 'pgsql')
            return $this->builder->where($this->builder->qualifyColumn($column), 'ilike', "%$value%");
        else
            return $this->builder->where($this->builder->qualifyColumn($column), 'like', "%$value%");
    }
}
