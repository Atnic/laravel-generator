@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <form class="form" action="{{ route($resource_route.'.update', [ $model->getKey(), 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" method="POST">
      {{ csrf_field() }}
      @component(config('generator.view_component').'components.panel')
        @slot('title')
          {{  __('Edit') }} {{ $panel_title ? : title_case(__($resource_route.'.singular')) }}
        @endslot

        @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
        @endif
        @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], [
            'field' => $field,
            'model' => $model
        ])
        @endcomponent
        @endforeach

        @slot('footer')
          <div class="pull-right">
            <button type="submit" name="_method" value="PUT" class="btn btn-primary">
              {{ __('Update') }}
            </button>
          </div>
          <a href="{{ request()->filled('redirect') ? request()->redirect : route($resource_route.'.index') }}" class="btn btn-default">{{{ __('Back') }}}</a>
          <button type="submit" name="_method" value="DELETE" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete?') }}');">
            {{ __('Delete') }}
          </button>
        @endslot
      @endcomponent
    </form>
  </div>
</div>
@endsection
