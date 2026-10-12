@extends('layouts.attex')
@section('page_title', trans('menu.calendar'))
@section('styles')

<style type="text/css">
  .modal-body {
    height: 400px;
    overflow-y: auto;
  }
</style>
@endsection
@section('content')
<x-fleet.panel :title="trans('menu.calendar')" icon="bi-calendar-event">
    <div id="calendar"></div>
</x-fleet.panel>

<div class="modal fade" id="booking_detail" tabindex="-1" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">@lang('fleet.event_details')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="my_event" tabindex="-1" aria-labelledby="exampleModalLabel1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">@lang('fleet.my_events')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
@if(AtlasLogistics::get('language')!="English-en")
@php($lg = explode('-',AtlasLogistics::get('language')))
@if($lg[1]!='al')
<script src="{{asset('assets/js/cdn/calendar/'.$lg[1].'.js')}}"></script>
@endif
@endif
<script>
  $(document).ready(function() {
    $('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay,listWeek'
      },

      defaultDate: '{{date("Y-m-d")}}',
      navLinks: true, // can click day/week names to navigate views
      editable: false,
      events: "{{ url('admin/calendar')}}",
      eventLimit: true,
      eventClick: function(calEvent, jsEvent, view) {
         $.ajax({

            url: '{{url("/admin/calendar/event/")}}/'+calEvent.type+'/'+calEvent.id,

          })
          .then(function(content){
            $("#booking_detail .modal-body").empty();
            $("#booking_detail .modal-body").html(content);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('booking_detail')).show();

          },
          function(xhr, status, error) {

            api.set('content.text', status + ': ' + error);
          });
        }
    });
  });
</script>
@endsection