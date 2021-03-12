@extends('layouts.app')
@section('page-name', 'Lenders')
@section('content')
<div id="maincontent">
<ol class="breadcrumb">
<li><a href="{{route('home')}}">Home</a></li>
<li class="active">Lenders</li>
</ol>

<div class="row">
     <!-- end col -->

     <div class="col-sm-6">
         <div class="card">
             <!-- <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>System Users</strong></h4>
             </div> -->
             <div class="card-content">
                 <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($lenders)
                    @foreach($lenders as $key => $lender)
                    <tr id="user{{$lender->id}}">
                        <td>{{$lender->user->name}}</td>
                        <td>{{$lender->user->email}}</td>
                        <td>{{number_format($lender->invested_amount)}}</td>
                        <td><a href="{{route('single-lender', $lender->id)}}">View</a></td>
                    </tr>
                    @endforeach
                    @endif
                    
                    </tbody>
                </table>
             </div>
         </div>
     </div>

     <!-- end col -->


     <div class="col-sm-6">
         <div class="card">
             <!-- <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>System Roles</strong></h4>
                 <button class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#newRole"><i class="ion-person-add"></i> Add New</button>
             </div> -->
         </div>
     </div>
</div>
</div>










@endsection

@section('page-scripts')
@endsection
