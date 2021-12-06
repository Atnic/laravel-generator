<?php

namespace Atnic\LaravelGenerator\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait ResolveRouteBindingWithFilter
{
    public function resolveRouteBinding($value, $field = null)
    {
        if ($this->hasCast($this->getKeyName()))
            $value = $this->castAttribute($this->getKeyName(), $value);
        $request = new Request(Arr::only(request()->query->all(), [ 'select', 'with', 'with_count', 'appends' ]));
        return (new $this->filters($request))->apply($this->newQuery())->where($this->qualifyColumn($field ?: $this->getRouteKeyName()), $value)->first();
    }
}
