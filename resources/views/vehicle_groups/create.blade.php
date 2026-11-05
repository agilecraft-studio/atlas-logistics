@extends(request()->boolean('drawer') ? 'layouts.master-data-drawer' : 'layouts.master-data')
@section('master-content')

<div class="row">
    <div class="col-md-12">
        <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0">@lang('fleet.createGroup')</h5>
      </div>
      <div>
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {!! Form::open(['route' => 'vehicle_group.store','method'=>'post','class' => 'form-reset']) !!}
                {!! Form::hidden('user_id',Auth::user()->id)!!}

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            {!! Form::label('name',__('fleet.groupName'), ['class' => 'form-label']) !!}
                            {!! Form::text('name',null,['class'=>'form-control','required']) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('description',__('fleet.description'), ['class' => 'form-label']) !!}
                            {!! Form::text('description',null,['class'=>'form-control']) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('note',__('fleet.note'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('note',null,['class'=>'form-control','size'=>'30x2']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::submit(__('fleet.createGroup'), ['class' => 'fleet-btn']) !!}
                    </div>
                </div>
            </div>
        </x-fleet.panel>
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
