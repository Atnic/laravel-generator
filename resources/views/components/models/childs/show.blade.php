@section('parent-content')
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
<div class="pull-right">
  <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-primary">{{ __('Edit') }}</a>
  @if (Route::has(array_last(explode('.', $resource_route)).'.show'))
  <a href="{{ route(array_last(explode('.', $resource_route)).'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}" class="btn btn-default">{{ __('Detail') }}</a>
  @endif
</div>
<a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-default">{{ __('List') }}</a>
@endsection
