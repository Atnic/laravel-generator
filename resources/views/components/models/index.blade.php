@section('content')
<div class="row">
  <div class="{{ $col_class ? : 'col-md-8 col-md-offset-2' }}">
    @component(config('generator.view_component').'components.panel')
      @slot('title')
        {{ __('List') }} {{ $panel_title ? : title_case(__($resource_route.'.plural')) }}
      @endslot
      @slot('tools')
        <a href="{{ route($resource_route.'.create', [ 'redirect' => request()->fullUrlWithQuery([ 'search' => null ]) ]) }}" class="btn btn-default btn-xs">{{ __('Create') }}</a>
      @endslot

      @if (session('status'))
      <div class="alert alert-{{ session('status-type') ? : 'success' }}">
        {{ session('status') }}
      </div>
      @endif
      <div class="row" style="margin-bottom:15px">
        <div class="col-sm-4">
          <form class="form" method="GET">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-search"></i>
              </span>
              <input type="text" name="search" placeholder="{{ __('Search') }}" class="input-sm form-control" value="{{ request()->search }}" autofocus>
            </div>
          </form>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              @foreach ($relations[$model_variable]['belongsTo'] as $key => $relation)
              <th class="text-center">
                {{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}
                @if (array_search($relation['name'].'.'.$relation['column'].',desc', explode('|', request()->sort)) === false)
                <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $relation['name'].'.'.$relation['column'].',desc' ])) }}"> <i class="fa fa-sort text-muted"></i></a>
                @else
                <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $relation['name'].'.'.$relation['column'].',asc' ])) }}"> <i class="fa fa-sort text-muted"></i></a>
                @endif
              </th>
              @endforeach
              @foreach ($visibles[$model_variable] as $key => $column)
              <th class="text-center">
                {{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}
                @if (array_search($column['name'].',desc', explode('|', request()->sort)) === false)
                <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $column['name'].',desc' ])) }}"> <i class="fa fa-sort text-muted"></i></a>
                @else
                <a href="{{ route($resource_route.'.index', array_merge(request()->query(), [ 'sort' => $column['name'].',asc' ])) }}"> <i class="fa fa-sort text-muted"></i></a>
                @endif
              </th>
              @endforeach
              <th class="text-center action" width="1px"></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($models as $key => $model)
            <tr>
              @foreach ($relations[$model_variable]['belongsTo'] as $key => $relation)
              <td>
                @if ($model->{$relation['name']})
                <a href="{{ Route::has(str_plural($relation['name']).'.show') ? route(str_plural($relation['name']).'.show', [ $model->{$relation['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
                  {{ $model->{$relation['name']}->{$relation['column']} }}
                </a>
                @else
                -
                @endif
              </td>
              @endforeach
              @foreach ($visibles[$model_variable] as $key => $column)
              <td>{{ $model->{$column['name']} }}</td>
              @endforeach
              <td class="action text-nowrap">
                <a href="{{ route($resource_route.'.show', [ $model->getKey() ]) }}" class="btn btn-primary btn-xs">{{ __('Show') }}</a>
                <a href="{{ route($resource_route.'.edit', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}" class="btn btn-success btn-xs">{{ __('Edit') }}</a>
                <form style="display:inline" action="{{ route($resource_route.'.destroy', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
                  {{ csrf_field() }}
                  <button type="submit" class="btn btn-danger btn-xs" name="_method" value="DELETE">{{ __('Delete') }}</button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td class="text-center" colspan="{{ count($relations[$model_variable]['belongsTo']) + count($visibles[$model_variable]) + 1 }}">{{ __('Empty') }}</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="text-center">
        {{ $models->links() }}
      </div>
    @endcomponent
  </div>
</div>
@endsection
