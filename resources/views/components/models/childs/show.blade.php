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
    <div class="pull-right">
        @if (Route::has($resource_route.'.edit'))
            @auth
                @can('update', $model)
                    <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                       class="btn btn-primary">{{ __('Edit') }}</a>
                @endcan
            @endauth
            @guest
                <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                   class="btn btn-primary">{{ __('Edit') }}</a>
            @endguest
        @endif
        @if (Route::has(array_last(explode('.', $resource_route)).'.show'))
            @auth
                @can('view', $model)
                    <a href="{{ route(array_last(explode('.', $resource_route)).'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                       class="btn btn-default">{{ __('Detail') }}</a>
                @endcan
            @endauth
            @guest
                <a href="{{ route(array_last(explode('.', $resource_route)).'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                   class="btn btn-default">{{ __('Detail') }}</a>
            @endguest
        @endif
    </div>
    @if (Route::has($resource_route.'.index'))
        @auth
            @can('index', $model)
                <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                   class="btn btn-default">{{ __('List') }}</a>
            @endcan
        @endauth
        @guest
            <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
               class="btn btn-default">{{ __('List') }}</a>
        @endguest
    @endif
@endsection
