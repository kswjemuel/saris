@extends('layouts.app')
@section('page-name', 'Lenders')
@section('content')
<div id="maincontent">
<ol class="breadcrumb">
<li><a href="{{route('home')}}">Home</a></li>
<li><a href="{{route('lenders')}}">Lenders</a></li>
<li class="active">{{$lender->user->name}}</li>
</ol>

<div class="row">
     <!-- end col -->

     <div class="col-sm-4">
         <div class="card">
             <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>Portfolio</strong></h4>
             </div>
             <div class="card-content">
                 <table class="table">
                     <tbody>
                         <tr>
                             <td width="70%">Cash In</td>
                             <td>{{isset($critical->cash_in) ? number_format($critical->cash_in) : 0}}</td>
                         </tr>
                                                
                         <tr>
                             <td>Outstanding Principal</td>
                             <td>{{isset($critical->outstanding) ? number_format($critical->outstanding) : 0}}</td>
                         </tr>
                         <tr>
                             <td>Income Received</td>
                             <td>{{isset($critical->earning) ? number_format($critical->earning) : 0}}</td>
                         </tr>
                         <tr>
                             <td>Cash @ Hand</td>
                             <td><b>{{isset($critical->cash_at_hand) ? number_format($critical->cash_at_hand) : 0}}</b></td>
                         </tr>
                         

                     </tbody>
                 </table>
             </div>
         </div>
     </div>


     <div class="col-sm-4">
         <div class="card">
             <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>Revenue/Expenses</strong></h4>
             </div>
             <div class="card-content">
                 <table class="table">
                     <tbody>
                        <tr>
                             <td width="70%">Income Received</td>
                             <td>{{isset($critical->earning) ? number_format($critical->earning) : 0}}</td>
                         </tr>
                         <tr>
                             <td>SMS Fees</td>
                             <td>{{isset($expenses->sms) ? number_format($expenses->sms) : 0}}</td>
                         </tr>
                         <tr>
                             <td>MPesa Fees</td>
                             <td>{{isset($expenses->mpesa) ? number_format($expenses->mpesa) : 0}}</td>
                         </tr>
                         
                         <tr>
                             <td>Payment Gateway</td>
                             <td>{{isset($expenses->gateway) ? number_format($expenses->gateway) : 0}}</td>
                         </tr>

                         <tr>
                             <td>Return on <strong>Cash In</strong></td>
                             <td>{{isset($expenses->roi) ? number_format($expenses->roi, 2) : 0}}%</td>
                         </tr>
                         

                     </tbody>
                 </table>
             </div>
         </div>
     </div>

     <!-- end col -->

     @if(Auth::user()->email=='kimkiuna@gmail.com')
     <div class="col-sm-4">
         <div class="card">
             <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>Settings</strong></h4>
             </div>
             <div class="card-content">
                 <form method="post" action="{{route('update-lender')}}">
                    {{ csrf_field() }}
                    <input type="hidden" name="lender_id" value="{{$lender->id}}">
                     <div class="form-group">
                         <label>Invested Amount</label>
                         <input type="text" name="invested_amount" value="{{$lender->invested_amount}}" class="form-control">
                     </div>
                     <div class="form-group">
                         <button type="submit" class="btn btn-primary">Update</button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
     @endif
</div>
</div>










@endsection

@section('page-scripts')

@endsection
