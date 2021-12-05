<?php

namespace Atnic\LaravelGenerator\Traits;

use Illuminate\Http\Request;

trait ResolveRouteBindingWithFilter
{
    public function resolveRouteBinding($value)
    {
        $request = new Request(request()->only([ 'select', 'with', 'with_count', 'appends' ]));
        return (new $this->filters($request))->apply($this->newQuery())->where($this->qualifyColumn($this->getRouteKeyName()), $value)->first();
    }
}
