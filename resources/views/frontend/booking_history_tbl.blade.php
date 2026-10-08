@if ($bookings->count() > 0)

    @foreach ($bookings as $booking)

        <div class="col-12" data-id="{{ $booking->id }}" style="margin-bottom: 20px;">

            <div class="booking-history-bg ">

              
            @if($booking && $booking->ride_status)

    

            <div class="booking-history-bg-blue-img">{{$booking->ride_status}}</div>

            @endif

            
             
            <div class="set-icon-image">
                @if($booking && $booking->return_flag == 1)
                        @php
                            $p = \App\Model\Bookings::select("bookings.*")->where('id',$booking->parent_booking_id)->first();
                        @endphp

                        @if(isset($p))

                            <img src="{{ url('/assets/customer_dashboard/assets/img/return_way.svg') }}" width="30" height="30" class="return-way" title="Return Way">

                        @else
                            <img src="{{ url('/assets/customer_dashboard/assets/img/one_way.svg') }}" width="30" height="30" class="one-way" title="One Way">

                        @endif
                
                
                @else
                    <img src="{{ url('/assets/customer_dashboard/assets/img/one_way.svg') }}" width="30" height="30" class="one-way" title="One Way">
                @endif
            </div>
              

                

                <div class="from-to-address">

                      <div class="row">

                        <div class="col-8">

                            <div class="address-detail">

                                <div class="row">

                                    <div class="col-12 col-md-6 col-lg-6">

                                        <div class="from-add">

                                            <h5 class="from">@lang('frontend.from')</h5>

                                            <p class="mb-3 mb-sm-3 mb-md-3 mb-lg-0 mb-xl-0 mb-xxl-0">

                                               {{ $booking->pickup_addr }}

                                            </p>

                                        </div>

                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">

                                        <div class="from-add">

                                            <h5 class="from ">@lang('frontend.to')</h5>

                                            <p class="mb-3 mb-sm-3 mb-md-3 mb-lg-0 mb-xl-0 mb-xxl-0">

                                                {{ $booking->dest_addr }}

                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>



                        <div class="col-4">



                            <div class="rent-hour">

                                <div class="row g-0">

                                    @if ($booking->tax_total)

                                        <div class="col-12 col-md-12 col-lg-6">

                                            <div class="rent">

                                                <h5>{{ Hyvikk::get('currency') }}{{ $booking->tax_total }}

                                                </h5>

                                            </div>

                                        </div>

                                    @endif



                                    @if ($booking->total_kms)

                                        <div class="col-12 col-md-12 col-lg-6">

                                            <div class="hour">

                                                <p class="km">{{ $booking->total_kms }}

                                                    {{ Hyvikk::get('dis_format') }}</p>

                                               

                                                    @if($booking && $booking->total_time)



                                                    @php

                                                        $date=explode(":",$booking->total_time);

                                                    

                                                    @endphp



                                                    <p class="hour-detail">{{$date[0]}}h

                                                        {{$date[1]}}m</p>

                                                    @else

                                                    <p class="hour-detail">---</p>

                                                    @endif



                                            </div>

                                        </div>

                                    @endif





                                </div>

                            </div>

                        </div>



                    

            



                        <div class="col-6 booking-history-date">

                            <div class="b-h-date">

                                <p>{{ date('d F Y', strtotime($booking->journey_date)) }}</p>

                            </div>

                        

                        </div>

                    

                    

                    

                    

                    </div>



                </div>

            </div>

        </div>

    @endforeach



@else

    <h4 class="text-center">No Record Found.</h4>

@endif

