@canany(['Transactions list','Reports list','Income list','Expense list'])
<li class="side-nav-title">@lang('menu.reports')</li>
@can('Transactions list')
<li class="side-nav-item">
    <a href="{{ url('admin/transactions') }}" class="side-nav-link @if(Request::is('admin/transactions*')) active @endif">
        <i class="ri-exchange-line"></i><span> @lang('menu.transactions') </span>
    </a>
</li>
@endcan
@canany(['Income list','Expense list'])
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarFinance" aria-expanded="{{ Request::is('admin/income*') || Request::is('admin/expense*') ? 'true' : 'false' }}" aria-controls="sidebarFinance" class="side-nav-link {{ Request::is('admin/income*') || Request::is('admin/expense*') ? 'active' : '' }}">
        <i class="ri-money-dollar-circle-line"></i><span> @lang('menu.addIncome') / @lang('menu.addExpense') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/income*') || Request::is('admin/expense*') ? 'show' : '' }}" id="sidebarFinance">
        <ul class="side-nav-second-level">
            @can('Income list')
            <li><a href="{{ route('income.index') }}" class="@if(Request::is('admin/income*')) active @endif">@lang('menu.addIncome')</a></li>
            @endcan
            @can('Expense list')
            <li><a href="{{ route('expense.index') }}" class="@if(Request::is('admin/expense*')) active @endif">@lang('menu.addExpense')</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
@can('Reports list')
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="{{ Request::is('admin/reports*') ? 'true' : 'false' }}" aria-controls="sidebarReports" class="side-nav-link {{ Request::is('admin/reports*') ? 'active' : '' }}">
        <i class="ri-bar-chart-line"></i><span> @lang('menu.reports') </span><span class="menu-arrow"></span>
    </a>
    <div class="collapse {{ Request::is('admin/reports*') ? 'show' : '' }}" id="sidebarReports">
        <ul class="side-nav-second-level">
            <li><a href="{{ url('admin/reports/income') }}" class="@if(Request::is('admin/reports/income')) active @endif">@lang('fleet.income') @lang('fleet.report')</a></li>
            <li><a href="{{ url('admin/reports/expense') }}" class="@if(Request::is('admin/reports/expense')) active @endif">@lang('fleet.expense') @lang('fleet.report')</a></li>
            <li><a href="{{ route('reports.monthly') }}" class="@if(Request::is('admin/reports/monthly')) active @endif">@lang('menu.monthlyReport')</a></li>
            <li><a href="{{ route('reports.booking') }}" class="@if(Request::is('admin/reports/booking')) active @endif">@lang('menu.bookingReport')</a></li>
            <li><a href="{{ route('reports.fuel') }}" class="@if(Request::is('admin/reports/fuel')) active @endif">@lang('fleet.fuelReport')</a></li>
            <li><a href="{{ route('reports.drivers') }}" class="@if(Request::is('admin/reports/drivers')) active @endif">@lang('fleet.driverReport')</a></li>
            <li><a href="{{ route('reports.customers') }}" class="@if(Request::is('admin/reports/customers')) active @endif">@lang('fleet.customerReport')</a></li>
            <li><a href="{{ route('reports.vendors') }}" class="@if(Request::is('admin/reports/vendors')) active @endif">@lang('fleet.vendorReport')</a></li>
            <li><a href="{{ route('reports.yearly') }}" class="@if(Request::is('admin/reports/yearly')) active @endif">@lang('fleet.yearlyReport')</a></li>
            <li><a href="{{ route('reports.payments') }}" class="@if(Request::is('admin/reports/payments')) active @endif">@lang('fleet.paymentReport')</a></li>
            <li><a href="{{ route('reports.work_order') }}" class="@if(Request::is('admin/reports/work-order')) active @endif">@lang('fleet.work_order_report')</a></li>
            <li><a href="{{ route('reports.delinquent') }}" class="@if(Request::is('admin/reports/delinquent')) active @endif">@lang('menu.deliquentReport')</a></li>
            <li><a href="{{ route('reports.users') }}" class="@if(Request::is('admin/reports/users')) active @endif">@lang('menu.users') @lang('fleet.report')</a></li>
        </ul>
    </div>
</li>
@endcan
@endcanany
@if(auth()->check() && (auth()->user()->user_type == 'D' || auth()->user()->user_type == 'O'))
@can('MyTransactions list')
<li class="side-nav-item">
    <a href="{{ url('admin/my_bookings') }}" class="side-nav-link @if(Request::is('admin/my_bookings')) active @endif">
        <i class="ri-book-line"></i><span> @lang('menu.my_bookings') </span>
    </a>
</li>
@endcan
@endif
