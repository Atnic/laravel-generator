@php
    $children = [];
    foreach ([ 'belongsToMany', 'hasMany', 'hasOne' ] as $relation){
        $relations[$model_variable][$relation] = !empty($relations[$model_variable][$relation]) ? $relations[$model_variable][$relation] : [];
        $children = array_merge($children, $relations[$model_variable][$relation]);
    }
@endphp

@section('parent-content')
    <table class="table">
        <colgroup>
            <col class="col-xs-4">
            <col class="col-xs-8">
        </colgroup>
        <tbody>
        @foreach ([ 'belongsToMany', 'hasMany' ] as $key => $relation_type)
            @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                <tr>
                    <th>{{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}</th>
                    <td>
                        <a href="{{ Route::has($resource_route.'.'.$relation['name'].'.index') ? route($resource_route.'.'.$relation['name'].'.index', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) : '#' }}">
                            {{ $model->{$relation['name']}->count() }}
                        </a>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
@endsection

@section('content')
    <div class="row">
        @if (session('status'))
            <div class="{{ empty($children) ?
                'col-md-8 col-md-offset-2' : 'col-xs-12' }}">
                <div class="alert alert-{{ session('status-type') ? : 'success' }}">
                    {{ session('status') }}
                </div>
            </div>
        @endif
        <div class="{{ (empty($children)) ?
                'col-md-8 col-md-offset-2' : 'col-md-4' }}">
            @component(config('generator.view_component').'components.panel')
                @slot('title')
                    {{ __('Detail') }} {{ !empty($panel_title) ? $panel_title : title_case(__($resource_route.'.singular')) }}
                @endslot

                <table class="table">
                    <colgroup>
                        <col class="col-xs-4">
                        <col class="col-xs-8">
                    </colgroup>
                    <tbody>
                    @foreach ($visibles[$model_variable] as $key => $column)
                        @if (!empty($column['column']))
                            <tr>
                                <th>{{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}</th>
                                <td>
                                    @if ($model->{$column['name']})
                                        <a href="{{ Route::has(str_plural($column['name']).'.show') ? route(str_plural($column['name']).'.show', [ $model->{$column['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
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
                                <th>{{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}</th>
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

                @slot('footer')
                    @if (Route::has($resource_route.'.edit'))
                        <div class="pull-right">
                            <a href="{{ route($resource_route.'.edit', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                               class="btn btn-primary">{{ __('Edit') }}</a>
                        </div>
                    @endif
                    <a href="{{ request()->filled('redirect') ? request()->redirect : route($resource_route.'.index') }}"
                       class="btn btn-default">{{ __('Back') }}</a>
                @endslot
            @endcomponent
        </div>
        @if (!empty($children))
            <div class="col-md-8">
                @component(config('generator.view_component').'components.tabs')
                    @slot('nav_tabs')
                        <li role="presentation" style="display: inline-block; float: none"
                            class="{{ request()->routeIs($resource_route.'.show') ? 'active' : '' }}">
                            <a href="{{ route($resource_route.'.show', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                               style="cursor:pointer">{{ title_case(__($resource_route.'.singular')) }}</a>
                        </li>
                        @foreach ([ 'belongsToMany', 'hasMany' ] as $key => $relation_type)
                            @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                                @if (Route::has($resource_route.'.'.$relation['name'].'.index'))
                                    <li role="presentation" style="display: inline-block; float: none"
                                        class="{{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}">
                                        <a href="{{ route($resource_route.'.'.$relation['name'].'.index', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                                           style="cursor:pointer">
                                            {{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endforeach
                        @foreach ([ 'hasOne' ] as $key => $relation_type)
                            @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                                @if (Route::has($resource_route.'.'.$relation['name'].'.show'))
                                    <li role="presentation"
                                        class="{{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}">
                                        <a href="{{ route($resource_route.'.'.$relation['name'].'.show', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
                                           style="cursor:pointer">
                                            {{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endforeach
                    @endslot

                    <div class="tab-pane active">
                        <div class="panel-body">

                            @yield('parent-content')

                        </div>
                    </div>
                @endcomponent
            </div>
        @endif
    </div>
@endsection
