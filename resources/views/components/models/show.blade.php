@section('panel-content')
    <table class="table">
        <colgroup>
            <col class="col-xs-4 col-4" style="width: 33%">
            <col class="col-xs-8 col-8" style="width: 66%">
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
                                @elseif ($model->{$column['name']}->{$column['column']} instanceof \Carbon\Carbon)
                                    <span title="{{ $model->{$column['name']}->{$column['column']}->toAtomString() }}">
                                        <script>
                                            date = new Date("{{ $model->{$column['name']}->{$column['column']}->toAtomString() }}");
                                            document.write(date.toLocaleString("{{ app()->getLocale() }}", {
                                                year: 'numeric', month: '2-digit', day: '2-digit',
                                                hour: '2-digit', minute: '2-digit', second: '2-digit',
                                            }));
                                        </script>
                                    </span>
                                @elseif (is_bool($model->{$column['name']}->{$column['column']}))
                                    <i class="fa fa-{{ $model->{$column['name']}->{$column['column']} ? 'check-' : '' }}square-o"></i>
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
                        @elseif ($model->{$column['name']} instanceof \Carbon\Carbon)
                            <span title="{{ $model->{$column['name']}->toAtomString() }}">
                                <script>
                                    date = new Date("{{ $model->{$column['name']}->toAtomString() }}");
                                    document.write(date.toLocaleString("{{ app()->getLocale() }}", {
                                        year: 'numeric', month: '2-digit', day: '2-digit',
                                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                                    }));
                                </script>
                            </span>
                        @elseif (is_bool($model->{$column['name']}))
                            <i class="fa fa-{{ $model->{$column['name']} ? 'check-' : '' }}square-o"></i>
                        @else
                            {{ $model->{$column['name']} }}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
@endsection

@section('panel-footer')
    @if (Route::has($resource_route.'.edit'))
        @if ((auth()->check() && auth()->user()->can('update', $model)) || auth()->guest())
            <div class="pull-right float-right">
                <a href="{{ route($resource_route.'.edit', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                   class="btn btn-primary">{{ __('Edit') }}</a>
            </div>
        @endif
    @endif
    <a href="{{ request()->filled('redirect') ? url(request()->redirect) : route($resource_route.'.index') }}"
       class="btn btn-default btn-secondary">{{ __('Back') }}</a>
@endsection

@if (!empty($relations[$model_variable]['belongsToMany']) || !empty($relations[$model_variable]['hasMany']))
    @section('parent-content')
        <table class="table">
            <colgroup>
                <col class="col-xs-4 col-4" style="width: 33%">
                <col class="col-xs-8 col-8" style="width: 66%">
            </colgroup>
            <tbody>
            @foreach ([ 'belongsToMany', 'hasMany' ] as $key => $relation_type)
                @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                    <tr>
                        <th>{{ !empty($relation['label']) ? $relation['label'] : ucwords(str_replace('_', ' ', snake_case($relation['name']))) }}</th>
                        <td>
                            <a href="{{ Route::has($resource_route.'.'.$relation['name'].'.index') && (!auth()->check() || auth()->user()->can('index', $model->{$relation['name']}()->getRelated())) ? route($resource_route.'.'.$relation['name'].'.index', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) : '#' }}">
                                {{ $model->{$relation['name']}->count() }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    @endsection
@endif

@section('content')
    <div class="row">
        @if (session('status'))
            <div class="
                @hasSection('parent-content')
                    col-xs-12 col-12
                @else
                    col-md-8 col-md-offset-2 offset-md-2
                @endif
                ">
                @component(config('generator.view_component').'components.alert')
                    @slot('type', session('status-type'))
                    @if (session('status') instanceof \Illuminate\Support\HtmlString)
                        {!! session('status') !!}
                    @else
                        {{ session('status') }}
                    @endif
                @endcomponent
            </div>
        @endif
        <div class="
            @hasSection('parent-content')
                col-md-4
            @else
                col-md-8 col-md-offset-2 offset-md-2
            @endif
                ">
            @component(config('generator.view_component').'components.panel')
                @slot('title')
                    {{ __('Detail') }} {{ !empty($panel_title) ? $panel_title : ucwords(__($resource_route.'.singular')) }}
                @endslot

                @yield('panel-content')

                @slot('footer')
                    @yield('panel-footer')
                @endslot
            @endcomponent
        </div>
        @hasSection('parent-content')
            <div class="col-md-8">
                @component(config('generator.view_component').'components.tabs')
                    @slot('nav_tabs')
                        <li role="presentation" style="display: inline-block; float: none"
                            class="nav-item {{ request()->routeIs($resource_route.'.show') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs($resource_route.'.show') ? 'active' : '' }}" href="{{ route($resource_route.'.show', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                               style="cursor:pointer">{{ ucwords(__($resource_route.'.singular')) }}</a>
                        </li>
                        @foreach ([ 'belongsToMany', 'hasMany' ] as $key => $relation_type)
                            @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                                @if (Route::has($resource_route.'.'.$relation['name'].'.index'))
                                    @if ((auth()->check() && auth()->user()->can('index', $model->{$relation['name']}()->getRelated())) || auth()->guest())
                                        <li role="presentation" style="display: inline-block; float: none"
                                            class="nav-item {{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}">
                                            <a class="nav-link {{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}" href="{{ route($resource_route.'.'.$relation['name'].'.index', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                                               style="cursor:pointer">
                                                {{ !empty($relation['label']) ? $relation['label'] : ucwords(str_replace('_', ' ', snake_case($relation['name']))) }}
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                        @foreach ([ 'hasOne' ] as $key => $relation_type)
                            @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
                                @if (Route::has($resource_route.'.'.$relation['name'].'.show'))
                                    @if ((auth()->check() && auth()->user()->can('view', $model)) || auth()->guest())
                                        <li role="presentation"
                                            class="nav-item {{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}">
                                            <a class="nav-link {{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}" href="{{ route($resource_route.'.'.$relation['name'].'.show', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                                               style="cursor:pointer">
                                                {{ !empty($relation['label']) ? $relation['label'] : ucwords(str_replace('_', ' ', snake_case($relation['name']))) }}
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                    @endslot

                    <div class="tab-pane active">
                        <div class="panel-body card-body">

                            @yield('parent-content')

                        </div>
                    </div>
                @endcomponent
            </div>
        @endif
    </div>
@endsection
