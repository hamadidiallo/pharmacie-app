<div class="mb-3">
  <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @if ($type ==='textarea')
        <textarea class="form-control" id="{{$name}}" name="{{$name}}" rows="3">{{$value}}</textarea>
    @else
        <input type="{{ $type }}" class="form-control" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}">
    @endif
</div>
@error($name)
    <div class="alert alert-danger">{{$message}}</div> 
@enderror

