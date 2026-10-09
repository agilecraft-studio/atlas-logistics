@canany(['Bookings list','Bookings add','BookingQuotations list'])
<li class="side-nav-title">@lang('menu.bookings')</li>
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarBookings" aria-expanded="{{ Request::is('admin/bookings*') || Request::is('admin/booking-quotation*') || Request::is('admin/bookings_calendar') ? 'true' : 'false' }}" aria-controls="sidebarBookings" class="side-nav-link {{ Request::is('admin/bookings*') || Request::is('admin/booking-quotation*') || Request::is('admin/bookings_calendar') ? 'active' : '' }}">
        <i class="ri-book-open-line"></i><span> @lang('menu.bookings') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/bookings*') || Request::is('admin/booking-quotation*') || Request::is('admin/bookings_calendar') ? 'show' : '' }}" id="sidebarBookings">
        <ul class="side-nav-second-level">
            @can('Bookings add')
            <li><a href="{{ route('bookings.create') }}" class="@if(Request::is('admin/bookings/create')) active @endif">@lang('menu.newbooking')</a></li>
            @endcan
            @can('Bookings list')
            <li><a href="{{ route('bookings.index') }}" class="@if(Request::is('admin/bookings*') && !Request::is('admin/bookings/create') && !Request::is('admin/bookings_calendar')) active @endif">@lang('menu.manage_bookings')</a></li>
            <li><a href="{{ url('admin/bookings_calendar') }}" class="@if(Request::is('admin/bookings_calendar')) active @endif">@lang('menu.calendar')</a></li>
            @endcan
            @can('BookingQuotations list')
            <li><a href="{{ route('booking-quotation.index') }}" class="@if(Request::is('admin/booking-quotation*')) active @endif">@lang('menu.booking_quotes')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
