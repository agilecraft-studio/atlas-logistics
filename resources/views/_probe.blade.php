@extends('layouts.attex')
@section('extra_css')
<style>.card{border:1px solid #dee2e6}</style>
@endsection
@section('content')
<div class="row"><div class="col-md-12">
  <div class="card card-info">
    <div class="card-header"><h3 class="card-title">Probe</h3></div>
    <div class="card-body table-responsive">
      <table class="table table-bordered" id="ajax_data_table">
        <thead><tr class="thead-inverse"><th>#</th><th>Name</th></tr></thead>
        <tbody><tr><td>1</td><td>Probe Vehicle</td></tr></tbody>
      </table>
    </div>
  </div>
  <button id="open-modal" class="btn btn-success" data-toggle="modal" data-target="#demo-modal">Open Modal</button>
</div></div>
@endsection
@section('script')
<script>
window.__PROBE = {
  jquery: typeof jQuery,
  pluginDataTable: typeof jQuery.fn.dataTable,
  pluginSelect2: typeof jQuery.fn.select2,
  modalPlugin: typeof jQuery.fn.modal,
  bootstrap: typeof window.bootstrap,
  domContentLoadedFired: false
};
</script>
<script>
$(function(){ window.__PROBE.domContentLoadedFired = true; });
$('#open-modal').on('click', function(){ $('#demo-modal').modal('show'); });
</script>
@endsection
@section('script-bottom')
<script>$(function(){ if(window.bootstrap){ window.__PROBE.boostrapAfter = true; } });</script>
@endsection
