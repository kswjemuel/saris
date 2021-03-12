@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Loans')
@section('content')

<div id="maincontent">
<div class="container-fluid" id="loansWrapper">



<div class="section">
  <div class="row">
    <div class="col col-sm-12">
      <div class="card">

        <div class="card-header clearfix">
          <h3 class="">Late Loans: {{isset($loans) ? number_format($loans->sum('principle_disbursed')) : 0}}</h3>
      </div>
        
        <div class="card-content">

        



<table id="customers" class="table no-borders table-hover">
    <thead class="thead">
        <tr>
            <th>SID</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Given On</th>
            <th>Due Date</th>
            <th>Days Late</th>
            <th>Penalized</th>
            <th>Collection</th>
            <th>Last Updated</th>
        </tr>
    </thead>

    <tbody>
        @if(isset($loans))
        @foreach($loans as $loan)
        <tr>
            <td>{{$loan->customer->id}}</td>
            <td>{{$loan->customer->user->name}}</td>
            <td>{{$loan->customer->phone_number}}</td>
            <td>{{$loan->total_amount_due}}</td>
            <td>{{$loan->date_issued}}</td>
            <td>{{$loan->due_date}}</td>
            <td>{{$loan->days_late}}</td>
            <td>{{$loan->date_penalized}}</td>
            <td>{{$loan->date_marked_for_collection}}</td>
            <td>{{$loan->updated_at}}</td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
          
        </div>
      </div>
    </div>

  </div>
</div>
  
</div>
      
</div>



@endsection

