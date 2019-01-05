@section('parent-tools')
    @if (Route::has($resource_route.'.create'))
        @if ((auth()->check() && auth()->user()->can('create', $model_class ?? 'App\Model')) || auth()->guest())
            <a href="{{ route($resource_route.'.create', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}"
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
                    @if (array_search($column['name'].'.'.$column['column'].',desc', explode('|', request()->sort)) === false)
                        <a href="{{ route($resource_route.'.index', array_merge([ $parent->getKey() ], request()->query(), [ 'sort' => $column['name'].'.'.$column['column'].',desc' ])) }}">
                            <i class="fa fa-sort text-muted"></i></a>
                    @else
                        <a href="{{ route($resource_route.'.index', array_merge([ $parent->getKey() ], request()->query(), [ 'sort' => $column['name'].'.'.$column['column'].',asc' ])) }}">
                            <i class="fa fa-sort text-muted"></i></a>
                    @endif
                </th>
            @else
                <th class="text-center">
                    {{ !empty($column['label']) ? $column['label'] : ucwords(str_replace('_', ' ', snake_case($column['name']))) }}
                    @if (array_search($column['name'].',desc', explode('|', request()->sort)) === false)
                        <a href="{{ route($resource_route.'.index', array_merge([ $parent->getKey() ], request()->query(), [ 'sort' => $column['name'].',desc' ])) }}">
                            <i class="fa fa-sort text-muted"></i></a>
                    @else
                        <a href="{{ route($resource_route.'.index', array_merge([ $parent->getKey() ], request()->query(), [ 'sort' => $column['name'].',asc' ])) }}">
                            <i class="fa fa-sort text-muted"></i></a>
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
                        @else
                            {{ $model->{$column['name']} }}
                        @endif
                    </td>
                @endif
            @endforeach
            <td class="action text-nowrap">
                @if (Route::has($resource_route.'.show'))
                    @if ((auth()->check() && auth()->user()->can('view', $model)) || auth()->guest())
                        <a href="{{ route($resource_route.'.show', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                           class="btn btn-primary btn-xs">{{ __('Show') }}</a>
                    @endif
                @endif
                @if (Route::has($resource_route.'.edit'))
                    @if ((auth()->check() && auth()->user()->can('update', $model)) || auth()->guest())
                        <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? url(request()->redirect) : null ]) }}"
                           class="btn btn-success btn-xs">{{ __('Edit') }}</a>
                    @endif
                @endif
                @if (Route::has($resource_route.'.destroy'))
                    @if ((auth()->check() && auth()->user()->can('delete', $model)) || auth()->guest())
                        <form style="display:inline"
                              action="{{ route($resource_route.'.destroy', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->fullUrl() ]) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('Are you sure you want to :do?', [ 'do' => ucwords(__('Delete')) ]) }}');">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger btn-xs" name="_method"
                                    value="DELETE">{{ __('Delete') }}</button>
                        </form>
                    @endif
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td class="text-center" colspan="{{ count($visibles[$model_variable]) + 2 }}">{{ __('Empty') }}</td>
        </tr>
    @endforelse
@endsection

@section('parent-content')
    <div class="row" style="margin-bottom:5px">
        <div class="col-xs-12">
            <div class="pull-right">
                @yield('parent-tools')
            </div>
        </div>
    </div>
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
