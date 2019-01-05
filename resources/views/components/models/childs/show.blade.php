@section('parent-content-footer')
    <div class="pull-right">
        @if (Route::has($resource_route.'.edit'))
            @if ((auth()->check() && auth()->user()->can('update', $model)) || auth()->guest())
                <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                   class="btn btn-primary">{{ __('Edit') }}</a>
            @endif
        @endif
        @if (Route::has(array_last(explode('.', $resource_route)).'.show'))
            @if ((auth()->check() && auth()->user()->can('view', $model)) || auth()->guest())
                <a href="{{ route(array_last(explode('.', $resource_route)).'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                   class="btn btn-default">{{ __('Detail') }}</a>
            @endif
        @endif
    </div>
    @if (Route::has($resource_route.'.index'))
        @if ((auth()->check() && auth()->user()->can('view', $model)) || auth()->guest())
            <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
               class="btn btn-default">{{ __('List') }}</a>
        @endif
    @endif
@endsection

@section('parent-content')
    <table class="table">
        <colgroup>
            <col class="col-xs-4">
            <col class="col-xs-8">
        </colgroup>
        <tbody>
        @foreach ($visibles[$model_variable] as $key => $column)
            @if (!empty($column['column']))
                <tr>
                    <th>{{ !empty($column['label']) ? $column['label'] : ucwords(str_replace('_', ' ', snake_case($column['name']))) }}</th>
                    <td>
                        @if ($model->{$column['name']})
                            <a href="{{ Route::has(str_plural($column['name']).'.show') && (!auth()->check() || auth()->user()->can('view', $model->{$column['name']})) ? route(str_plural($column['name']).'.show', [ $model->{$column['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
                                @if ($model->{$column['name']}->{$column['column']} instanceof \Illuminate\Support\HtmlString)
                                    {!! $model->{$column['name']}->{$column['column']} !!}
                                @else
                                    {{ $model->{$column['name']}->{$column['column']} }}
                                @endif
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @else
                <tr>
                    <th>{{ !empty($column['label']) ? $column['label'] : ucwords(str_replace('_', ' ', snake_case($column['name']))) }}</th>
                    <td>
                        @if ($model->{$column['name']} instanceof \Illuminate\Support\HtmlString)
                            {!! $model->{$column['name']} !!}
                        @else
                            {{ $model->{$column['name']} }}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    @yield('parent-content-footer')
@endsection
