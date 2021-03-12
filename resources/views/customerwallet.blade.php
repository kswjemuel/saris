@extends('layouts.app')
@section('title', 'Wallet')
@section('page-name', 'Customer Wallet')
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
          

          <div class="row customer-stats text-center alert alert-info">
            <div class="col-xs-4">
              <span class="customer-stats-big">{{isset($totalIn) ? number_format($totalIn) : 0}}</span>
              <span class="customer-stats-small">In</span>
            </div>

            <div class="col-xs-4">
              <span class="customer-stats-big">{{isset($totalOut) ? number_format($totalOut) : 0}}</span>
              <span class="customer-stats-small">Out</span>
            </div>
            <div class="col-xs-4">
              <span class="customer-stats-big">{{ isset($customer->wallet->available_balance) ? number_format($customer->wallet->available_balance) : 0}}</span>
              <span class="customer-stats-small">Balance</span>
            </div>
          </div>

          <div class="section">
            <div class="col-sm-12">
              <p><strong>Total Transaction Fees: {{isset($transactionFees) ? number_format($transactionFees) : 0}}</strong></p>
            </div>
          </div>
          

          

          
          
          
          
        </div>
      </div>



      <div class="col-sm-8">
        

        <div class="customer-tabs text-right">
          <a class="btn btn-sm btn-default" href="{{route('customer', $customer->id)}}">Loans</a>
          <a class="btn btn-sm btn-primary" href="{{route('customer.wallet', $customer->id)}}">Wallet</a>
        </div>
        @if(count($customer->transactions))
        <div class="overpayments">
          <h4>Wallet Transactions</h4>
          <table class="table">
            <thead>
            <tr>
            <th>Date</th>
            <th>Transaction</th>
            <th>Amount</th>
            <th>Direction</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customer->transactions as $key => $transaction)
            <tr>
            <td>{{$transaction->created_at}}</td>
            <td>{{$transaction->narration}}</td>
            <td>{{$transaction->amount}}</td>
            <td>{{$transaction->direction}}</td>
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
  
</div>
      
</div>



@endsection

@section('page-scripts')
@endsection
