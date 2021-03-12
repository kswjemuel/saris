<div id="sidebar-left">
  <div class="product-logo-wrapper">
    <span class="mylogo">SARIS</span>
  </div>

  <div class="user-panel">
    
  </div>



  <div class="user-menu">
    <ul>
      <li><a href="{{route('home')}}"><i class="micon dripicons-view-apps"></i> Dashboard</a></li>
      <li><a href="{{route('customers')}}"><i class="micon dripicons-user-group"></i> Customers</a></li>
      <li><a href="{{ route('loans') }}"><i class="micon dripicons-wallet"></i> Loans</a></li>
      <li><a href="{{ route('repayments') }}"><i class="micon dripicons-time-reverse"></i> Repayments</a></li>
      @if(!Auth::user()->hasRole('lender'))
      <li><a href="{{ route('unclaimed') }}"><i class="micon dripicons-to-do"></i> Unclaimed</a></li>
      <li><a href="{{ route('debt-collection') }}"><i class="micon dripicons-to-do"></i> Debt Collection</a></li>
      @endif

      <li><a href="{{ route('internal-debt-collection') }}"><i class="micon dripicons-to-do"></i> AC Collection</a></li>

      <li><a href="{{ route('collection-calls') }}"><i class="micon dripicons-phone"></i> AC Calls</a></li>

      <li><a href="{{ route('commitments') }}"><i class="micon dripicons-to-do"></i> Commitments</a></li>

      @if(Auth::user()->hasRole('lender'))
      <li><a href="{{ route('debt-collection') }}"><i class="micon dripicons-to-do"></i> Debt Collection</a></li>
      <li><a href="{{ route('portfolio') }}"><i class="micon  dripicons-graph-line"></i> Portfolio</a></li>
      <li><a href="{{ route('single-lender' , 1) }}"><i class="micon  dripicons-graph-line"></i> Financials</a></li>
      @endif
      
      <li><a href="{{ route('reports') }}"><i class="micon  dripicons-graph-line"></i> Reports</a></li>
      

      <!-- ADMIN MENUS -->
      @if(Auth::user()->hasRole('admin'))
      <li><a href="{{ route('portfolio') }}"><i class="micon  dripicons-graph-line"></i> Portfolio</a></li>
      <li><a href="{{ route('users') }}"><i class="micon  dripicons-user-group"></i> Users</a></li>
      <li><a href="{{ route('lenders') }}"><i class="micon  dripicons-user"></i> Lenders</a></li>
      @endif

      <li><a href="{{ route('wallet') }}"><i class="micon dripicons-to-do"></i> Wallet <span class="badge pull-right beta">BETA</span></a></li>
    </ul>
  </div>
</div>