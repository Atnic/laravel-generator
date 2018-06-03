@section('parent-content')
<form class="form" action="{{ route($resource_route.'.store', [
  $parent->getKey(),
  'redirect' => route($resource_route.'.index', [
    $parent->getKey(),
    'redirect' => request()->filled('redirect') ? request()->redirect : null ]) ]) }}" method="POST">
  {{ csrf_field() }}
  @foreach ($fields[$model_variable] as $key => $field)
  @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
  @endcomponent
  @endforeach
  <div class="pull-right">
    <button type="submit" class="btn btn-primary">
      {{ __('Store') }}
    </button>
  </div>
  <a href="{{ route($resource_route.'.index', [ $parent->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" class="btn btn-default">{{ __('List') }}</a>
</form>
@endsection
