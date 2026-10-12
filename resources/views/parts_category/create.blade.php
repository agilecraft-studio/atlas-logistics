@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">@lang('fleet.add_part_category')</h3>
      </div>

      <div class="card-body">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        {!! Form::open(['route' => 'parts-category.store','method'=>'post','class' => 'form-reset']) !!}
        {!! Form::hidden('user_id',Auth::user()->id)!!}

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('name',__('fleet.category_name'), ['class' => 'form-label']) !!}
              {!! Form::text('name',null,['class'=>'form-control','required']) !!}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            {!! Form::submit(__('fleet.submit'), ['class' => 'btn btn-success']) !!}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script>
  $(document).ready(function() {
      $(".form-reset").on("submit", function(event) {
          $('input[type="submit"]').prop('disabled', true);
      });
    });
  </script>

@endsection
