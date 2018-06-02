@section('content-title', title_case(__($resource_route.'.plural')))

@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <form class="form" action="{{ route($resource_route.'.store', [ 'redirect' => request()->filled('redirect') ? request()->redirect : null ]) }}" method="POST">
      {{ csrf_field() }}
      @component(config('generator.view_component').'components.panel')
        @slot('title')
          {{ __('Create') }} {{ title_case(__($resource_route.'.singular')) }}
        @endslot

        @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
        @endif
        @foreach ($fields[$model_variable] as $key => $field)
        @component(config('generator.view_component').'components.fields.'.$field['field'], compact('field'))
        @endcomponent
        @endforeach

        @slot('footer')
          <div class="pull-right">
            <button type="submit" class="btn btn-primary">
              {{ __('Store') }}
            </button>
          </div>
          <a href="{{ request()->filled('redirect') ? request()->redirect : route($resource_route.'.index') }}" class="btn btn-default">{{ __('Back') }}</a>
        @endslot
      @endcomponent
    </form>
  </div>
</div>
@endsection
