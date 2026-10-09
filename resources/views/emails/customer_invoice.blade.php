@component('mail::message')
# Invoice Generate.

Dear {{$booking->customer->name}},

	Your Ride Invoice Generated. Below are the details for your Invoice.

@component('mail::table')
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

<table>
	<tr><td>Vehicle: </td><td>{{$booking->vehicle->make_name}} {{$booking->vehicle->model_name}}</td></tr>
	<tr><td>Driver: </td><td>{{$booking->driver->name}}</td></tr>
	<tr><td>Mileage: </td><td>{{$booking->mileage}} {{ AtlasLogistics::get('dis_format') }}</td></tr>
	<tr><td>Waiting time (in minutes): </td><td>{{$booking->waiting_time}}</td></tr>
	<tr><td>Amount: </td><td>{{ AtlasLogistics::get('currency') }} {{$booking->total}}</td></tr>
	<tr><td>Total Tax (%): </td><td>{{($booking->total_tax_percent) ? $booking->total_tax_percent : 0}} %</td></tr>
	<tr><td>Total Tax Charges: </td><td>{{ AtlasLogistics::get('currency') }} {{ ($booking->total_tax_charge_rs) ? $booking->total_tax_charge_rs : 0 }}</td></tr>
    <tr><td>Total : </td><td>{{ AtlasLogistics::get('currency') }} {{ $booking->tax_total }}</td></tr>
</table>
@endcomponent

We Wish you a happy journey.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
