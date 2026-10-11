<article class="fleet-card fleet-panel">
    <div class="fleet-panel-heading">
        <h2><i class="bi bi-exclamation-circle-fill" style="color:#ef5363"></i>Action Required</h2>
        <a href="{{ route('logistics.documents.index') }}" class="fleet-view-all">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="fleet-alert-list">
        <a href="{{ route('logistics.documents.index') }}" class="fleet-alert">
            <span class="fleet-alert-icon alert-red"><i class="bi bi-file-earmark-x-fill"></i></span>
            <span class="fleet-alert-copy"><strong>Vehicle/Driver Documents Expiring Soon</strong><small>Licenses, passports, visas, medicals etc.</small></span>
            <span class="fleet-alert-count">{{ $dashboard['expiring_documents'] }}</span>
        </a>
        <a href="{{ route('logistics.expenses.index') }}" class="fleet-alert">
            <span class="fleet-alert-icon alert-amber"><i class="bi bi-cash-coin"></i></span>
            <span class="fleet-alert-copy"><strong>Expenses Awaiting Approval</strong><small>Pending approval from management</small></span>
            <span class="fleet-alert-count">{{ $dashboard['pending_expense_approvals'] }}</span>
        </a>
        <div class="fleet-alert">
            <span class="fleet-alert-icon alert-amber"><i class="bi bi-tools"></i></span>
            <span class="fleet-alert-copy"><strong>Service Intervals Due</strong><small>Maintenance and inspections</small></span>
            <span class="fleet-alert-count amber">{{ $dashboard['services_due'] }}</span>
        </div>
        <div class="fleet-alert">
            <span class="fleet-alert-icon alert-amber"><i class="bi bi-fuel-pump-fill"></i></span>
            <span class="fleet-alert-copy"><strong>Fuel Variance Alerts</strong><small>Unusual fuel consumption detected</small></span>
            <span class="fleet-alert-count amber">{{ $dashboard['fuel_variance_alerts'] }}</span>
        </div>
        <div class="fleet-alert">
            <span class="fleet-alert-icon alert-amber"><i class="bi bi-file-earmark-arrow-up-fill"></i></span>
            <span class="fleet-alert-copy"><strong>Missing Proof-of-Delivery Documents</strong><small>PODs not uploaded yet</small></span>
            <span class="fleet-alert-count amber">{{ $dashboard['missing_pods'] }}</span>
        </div>
    </div>
</article>
