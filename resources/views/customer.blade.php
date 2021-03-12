@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Customers')
@section('content')

<div id="maincontent">
<div class="container-fluid">



<div class="section">
  <div class="card">
    <div class="card-content">

    <div class="row">
      <div class="col-sm-4">
        <div class="customer-profile-wrapper">
          <div class="customer-avatar">
            
          </div>
          <h4 class="text-center">{{ $customer->user->name }}</h4>
          <p class="text-center {{$customer->user->email_confirmed == 1 ? '':'text-danger'}}">{{ $customer->email_address }}</p>
          <p class="text-center">{{ $customer->phone_number }}</p>
          <p class="text-center"><b>ID:</b> {{ $customer->identification }}</p>
          <p class="text-center"><b>Gender:</b> {{ $customer->gender }} <b>Age:</b> {{Carbon\Carbon::parse($customer->date_of_birth)->age}}</p>

          <div class="row customer-stats text-center">
            <div class="col-xs-4">
              <span class="customer-stats-big">{{ $customer->loans->count()}}</span>
              <span class="customer-stats-small">Loans</span>
            </div>

            <div class="col-xs-4">
              <span class="customer-stats-big">{{ isset($last_loan) ? number_format($last_loan->loan_balance):0 }}</span>
              <span class="customer-stats-small">Balance</span>
            </div>

            <div class="col-xs-4">
              <span class="customer-stats-big">{{ isset($bee->customer_maximum_actual_limit) ? number_format($bee->customer_maximum_actual_limit) : 0 }}</span>
              <span class="customer-stats-small">Limit</span>
            </div>
          </div>
          @if(!empty($bee->customer_ranking))
          <div class="alert alert-info text-center">
            <p>{{$bee->customer_ranking}}</p>
            <p> {{$bee->created_at}} </p>
          </div>
          @endif

          @if(!empty($bee->customer_rejection_reason))
          <div class="alert alert-danger text-left"><strong>{{$bee->customer_failed_rule}}</strong>: {{$bee->customer_rejection_reason}}</div>
          @endif

          @if($customer->user->user_account_block_status)
          <div class="alert alert-danger text-center">Customer is Blocked</div>
          @endif

          @if(Auth::user()->email === 'kimkiuna@gmail.com' || Auth::user()->hasRole('customer-care' || Auth::user()->email === 'kevin.mutiso@alternativecircle.com'))
          <div class="customer-phone-data">
            <hr>
            <div class="row">
              <div class="col-sm-3 text-center">
                <div class="phone-data">
                  <h5>SMSs</h5>
                <p>{{isset($smss) ? $smss : 0 }}</p>
                </div>
              </div>

              <div class="col-sm-3 text-center">
                <div class="phone-data">
                <h5>Contacts</h5>
                <p>{{isset($contacts) ? $contacts : 0 }}</p>
              </div>
              </div>

              <div class="col-sm-3 text-center">
                <div class="phone-data">
                <h5>Calls</h5>
                <p>{{isset($calls) ? $calls : 0 }}</p>
              </div>
              </div>

              <div class="col-sm-3 text-center">
                <div class="phone-data br-0">
                <h5>Apps</h5>
                <p>{{isset($apps) ? $apps : 0 }}</p>
              </div>
              </div>
          </div>
          </div>

          <div class="block-customer" id="accountBlocker">
            <button class="btn btn-danger btn-block" @click="blockAccount({{$customer->id}})">@{{label}}</button>
          </div>
          @endif
          
          @if(!Auth::user()->hasRole('lender'))
          <div class="section alert alert-success">
            <h4>Location</h4>
            <p>{{$customer->lat}},{{$customer->lon}}</p>
            <p>{{isset($customer->physical_address) ? $customer->physical_address : "Unknown"}}</p>
          </div>
          @endif
          
        </div>
      </div>



      <div class="col-sm-8">
        <div class="customer-tabs text-right">
          <a class="btn btn-sm btn-primary" href="{{route('customer', $customer->id)}}">Loans</a>
          <a class="btn btn-sm btn-default" href="{{route('customer.wallet', $customer->id)}}">Wallet</a>
        </div>
        <div class="customer-statements">
            <h4>Transactions History</h4>
            @if($customer->statements)
            <table class="table">
            <thead>
            <tr>
            <th>Date</th>
            <th>Transaction</th>
            <th>Amount</th>
            <th>Balance</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customer->statements as $key => $statement)
            <tr>
            <td>{{$statement->created_at}}</td>
            <td>{{$statement->transaction_type}}</td>
            <td>{{$statement->amount}}</td>
            <td>{{$statement->running_balance}}</td>
            </tr>
            @endforeach
            
            </tbody>
            </table>
            @endif
        </div>


        @if(count($customer->overpayments))
        <div class="overpayments">
          <h4>Overpayments</h4>
          <table class="table">
            <thead>
            <tr>
            <th>Date</th>
            <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customer->overpayments as $key => $overpayment)
            <tr>
            <td>{{$overpayment->created_at}}</td>
            <td>{{$overpayment->amount_overpaid_by}}</td>
            </tr>
            @endforeach
            
            </tbody>
            </table>
        </div>
        @endif
        
      </div>
    </div>
    
      
    </div>
  </div>
</div>

@if(!Auth::user()->hasRole('lender'))
<div class="section">
  <div class="card">
    <div class="card-content">
      <div class="customer-statements">
            <h4>Customer Loans</h4>
            @if($customer->loans)
            <table class="table">
            <thead>
            <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Amount Due</th>
            <th>Loan Status</th>
            <th>Due On</th>
            <th>Completed On</th>
            <th>Lat,Lon</th>
            <th>Location</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customer->loans as $key => $loans)
            <tr>
            <td>{{$loans->id}}</td>
            <td>{{$loans->created_at}}</td>
            <td>{{$loans->principle_disbursed}}</td>
            <td>{{$loans->total_amount_due}}</td>
            <td>{{$loans->loan_status}}</td>
            <td>{{$loans->loan_due_on}}</td>
            <td>{{$loans->loan_completed_on}}</td>
            <td>{{$loans->lat}},{{$loans->lon}}</td>
            <td>{{$loans->location_address}}</td>
            </tr>
            @endforeach
            
            </tbody>
            </table>
            @endif
        </div>
    </div>
  </div>
</div>
@endif
  
</div>
      
</div>



@endsection

@section('page-scripts')
<script type="text/javascript">
  var accountBlocker = new Vue({
    el: '#accountBlocker',
    data: {
      label: 'Block Account'
    },
    methods: {
      blockAccount(id){
        var self = this;
        //console.log('Account blocked ' + id);
        axios.post('block-customer-account', {id: id}).then(function(response){
          //update ui
          self.label = response.data.message;
        }).catch(function(err){
          console.log(err);
        })
      }
    }
  })
</script>

@endsection
