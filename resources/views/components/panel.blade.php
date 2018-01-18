<div class="panel panel-default">
  @isset($header)
  <div class="panel-heading">
    {{ $header }}
  </div>
  @endisset
  <div class="panel-body">
    {{ !empty($body) ? $body : $slot }}
  </div>
  @isset($footer)
  <div class="panel-footer">
    {{ $footer }}
  </div>
  @endisset
</div>
