@section('panel-tools')
    @if (Route::has($resource_route.'.create'))
        @if ((auth()->check() && auth()->user()->can('create', $model_class ?? 'App\Model')) || auth()->guest())
            <a href="{{ route($resource_route.'.create', [ 'redirect' => request()->fullUrlWithQuery([ 'search' => null ]) ]) }}"
               class="btn btn-default btn-xs">{{ __('Create') }}</a>
        @endif
    @endif
@endsection

@section('thead')
    <tr>
        <th class="text-center" width="1px">No</th>
        @foreach ($visibles[$model_variable] as $key => $column)
            @if (!empty($column['column']))
                <th class="text-center">
                    {{ !empty($column['label']) ? $column['label'] : ucwords(str_replace('_', ' ', snake_case($column['name']))) }}
                    @if(!isset($column['sortable']) || $column['sortable'])
                        @if (array_search($column['name'].'.'.(isset($column['sort']) ? $column['sort'] : $column['column']).',desc', explode('|', request()->sort)) === false)
                            <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $column['name'].'.'.(isset($column['sort']) ? $column['sort'] : $column['column']).',desc' ])) }}">
                                <i class="fa fa-sort text-muted"></i></a>
                        @else
                            <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $column['name'].'.'.(isset($column['sort']) ? $column['sort'] : $column['column']).',asc' ])) }}">
                                <i class="fa fa-sort text-muted"></i></a>
                        @endif
                    @endif
                </th>
            @else
                <th class="text-center">
                    {{ !empty($column['label']) ? $column['label'] : ucwords(str_replace('_', ' ', snake_case($column['name']))) }}
                    @if(!isset($column['sortable']) || $column['sortable'])
                        @if (array_search((isset($column['sort']) ? $column['sort'] : $column['name']).',desc', explode('|', request()->sort)) === false)
                            <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => (isset($column['sort']) ? $column['sort'] : $column['name']).',desc' ])) }}">
                                <i class="fa fa-sort text-muted"></i></a>
                        @else
                            <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => (isset($column['sort']) ? $column['sort'] : $column['name']).',asc' ])) }}">
                                <i class="fa fa-sort text-muted"></i></a>
                        @endif
                    @endif
                </th>
            @endif
        @endforeach
        <th class="text-center action" width="1px"></th>
    </tr>
@endsection

@section('tbody')
    @forelse ($models as $key => $model)
        <tr>
            <td class="text-right">{{ $key + 1 + $models->perPage() * ($models->currentPage() - 1) }}.</td>
            @foreach ($visibles[$model_variable] as $key => $column)
                @if (!empty($column['column']))
                    <td class="{{ !empty($column['class']) ? $column['class'] : '' }}">
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
                @else
                    <td class="{{ !empty($column['class']) ? $column['class'] : '' }}">
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
                @endif
            @endforeach
            <td class="action text-nowrap text-right">
                @component($action_buttons_view ?? 'generator::components.models.index.action_buttons')
                    @slot('resource_route', $resource_route)
                    @slot('model', $model)
                @endcomponent
            </td>
        </tr>
    @empty
        <tr>
            <td class="text-center"
                colspan="{{ count($visibles[$model_variable]) + 2 }}">{{ __('Empty') }}</td>
        </tr>
    @endforelse
@endsection

@section('panel-content')
    <form class="form" method="GET">
        <div class="row" style="margin-bottom:15px">
            <div class="col-xs-6 col-md-4">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button class="btn btn-sm btn-default" type="submit">
                            &nbsp;<i class="fa fa-search"></i>&nbsp;
                        </button>
                    </span>
                    <input type="text" name="search" placeholder="{{ __('Search') }}"
                           class="input-sm form-control" value="{{ request()->search }}" autofocus>
                </div>
            </div>
            <div class="col-xs-6 col-md-8">
                <div class="text-right">
                    <span>
                        {{ ($models->count() ? 1 : 0) + ($models->perPage() * ($models->currentPage() - 1)) }} -
                        {{ $models->count() + ($models->perPage() * ($models->currentPage() - 1)) }} {{ strtolower(__('Of')) }}
                        {{ $models->total() }}
                    </span>&nbsp;
                    <div style="display: inline-block">
                        <select class="form-control input-sm" name="per_page" id="per_page" onchange="this.form.submit()" title="per page">
                            @foreach ([ 15, 50, 100, 250 ] as $value)
                                <option value="{{ $value }}" {{ $value == $models->perPage() ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="table-responsive">
        <table id="{{ str_plural($model_variable) }}" class="table table-striped table-hover table-condensed">
            <thead class="text-nowrap">
            @stack('thead-prepend')
            @yield('thead')
            @stack('thead-append')
            </thead>
            <tbody>
            @stack('tbody-prepend')
            @yield('tbody')
            @stack('tbody-append')
            </tbody>
        </table>
    </div>
    <div class="text-center">
        {{ $models->links() }}
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="{{ !empty($col_class) ? $col_class : 'col-md-8 col-md-offset-2' }}">
            @if (session('status'))
                @component(config('generator.view_component').'components.alert')
                    @slot('type', session('status-type'))
                    @if (session('status') instanceof \Illuminate\Support\HtmlString)
                        {!! session('status') !!}
                    @else
                        {{ session('status') }}
                    @endif
                @endcomponent
            @endif
            @component(config('generator.view_component').'components.panel')
                @slot('title')
                    {{ __('List') }} {{ !empty($panel_title) ? $panel_title : ucwords(__($resource_route.'.plural')) }}
                @endslot
                @slot('tools')
                    @yield('panel-tools')
                @endslot

                @yield('panel-content')

            @endcomponent
        </div>
    </div>
@endsection
