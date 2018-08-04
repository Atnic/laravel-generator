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
      <th>{{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}</th>
      <td>
        @if ($model->{$column['name']})
        <a href="{{ Route::has(str_plural($column['name']).'.show') ? route(str_plural($column['name']).'.show', [ $model->{$column['name']}->getKey(), 'redirect' => request()->fullUrl() ]) : '#' }}">
          {{ $model->{$column['name']}->{$column['column']} }}
        </a>
        @else
        -
        @endif
      </td>
    </tr>
    @else
    <tr>
      <th>{{ !empty($column['label']) ? $column['label'] : title_case(str_replace('_', ' ', snake_case($column['name']))) }}</th>
      <td>{{ $model->{$column['name']} }}</td>
    </tr>
    @endif
    @endforeach
  </tbody>
</table>
<div class="pull-right">
  @if (Route::has($resource_route.'.edit'))
  <a href="{{ route($resource_route.'.edit', [ $parent->getKey(), $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-primary">{{ __('Edit') }}</a>
  @endif
  @if (Route::has(array_last(explode('.', $resource_route)).'.show'))
  <a href="{{ route(array_last(explode('.', $resource_route)).'.show', [ $model->getKey(), 'redirect' => request()->fullUrl() ]) }}" class="btn btn-default">{{ __('Detail') }}</a>
  @endif
</div>
@if (Route::has($resource_route.'.index'))
<a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-default">{{ __('List') }}</a>
@endif
@endsection
