@section('content')
<div class="row">
  <div class="{{ (empty(array_merge($relations[$model_variable]['belongsToMany'],
                                    $relations[$model_variable]['hasMany']))) ?
                'col-md-8 col-md-offset-2' : 'col-md-4' }}" >
    @component(config('generator.view_component').'components.panel')
      @slot('title')
        {{ __('Detail') }} {{ $panel_title ? : title_case(__($resource_route.'.singular')) }}
      @endslot

      @if (session('status'))
      <div class="alert alert-success">
        {{ session('status') }}
      </div>
      @endif
      <table class="table">
        <colgroup>
          <col class="col-xs-4">
          <col class="col-xs-8">
        </colgroup>
        <tbody>
          @foreach ($relations[$model_variable]['belongsTo'] as $key => $relation)
          <tr>
            <th>{{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}</th>
            <td>
              @if ($model->{$relation['name']})
              <a href="{{ Route::has(str_plural($relation['name']).'.show') ? route(str_plural($relation['name']).'.show', [ $model->{$relation['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
                {{ $model->{$relation['name']}->{$relation['column']} }}
              </a>
              @else
              -
              @endif
            </td>
          </tr>
          @endforeach
          @foreach ($visibles[$model_variable] as $key => $column)
          <tr>
            <th>{{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}</th>
            <td>{{ $model->{$column['name']} }}</td>
          </tr>
          @endforeach
          @foreach ($relations[$model_variable]['hasOne'] as $key => $relation)
          <tr>
            <th>{{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}</th>
            <td>
              @if ($model->{$relation['name']})
              <a href="{{ Route::has(str_plural($relation['name']).'.show') ? route(str_plural($relation['name']).'.show', [ $model->{$relation['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
                {{ $model->{$relation['name']}->{$relation['column']} }}
              </a>
              @else
              -
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @slot('footer')
        <div class="pull-right">
          <a href="{{ route($resource_route.'.edit', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}" class="btn btn-primary">{{ __('Edit') }}</a>
        </div>
        <a href="{{ request()->filled('redirect') ? request()->redirect : route($resource_route.'.index') }}" class="btn btn-default">{{ __('Back') }}</a>
      @endslot
    @endcomponent
  </div>
  @if (!empty(array_merge($relations[$model_variable]['belongsToMany'],
                          $relations[$model_variable]['hasMany'])))
  <div class="col-md-8">
    @component(config('generator.view_component').'components.tabs')
      @slot('nav_tabs')
        <li role="presentation" class="{{ request()->routeIs($resource_route.'.show') ? 'active' : '' }}">
          <a href="{{ route($resource_route.'.show', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" style="cursor:pointer">{{ title_case(__($resource_route.'.singular')) }}</a>
        </li>
        @foreach ([ 'belongsToMany', 'hasMany' ] as $key => $relation_type)
        @foreach ($relations[$model_variable][$relation_type] as $key => $relation)
        @if (Route::has($resource_route.'.'.$relation['name'].'.index'))
        <li role="presentation" class="{{ request()->routeIs($resource_route.'.'.$relation['name'].'.*') ? 'active' : '' }}">
          <a href="{{ route($resource_route.'.'.$relation['name'].'.index', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" style="cursor:pointer">
            {{ !empty($relation['label']) ? $relation['label'] : title_case(str_replace('_', ' ', snake_case($relation['name']))) }}
          </a>
        </li>
        @endif
        @endforeach
        @endforeach
      @endslot

      <div class="tab-pane active">
        <div class="panel-body">
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
          @show
        </div>
      </div>
    @endcomponent
  </div>
  @endif
</div>
@endsection
