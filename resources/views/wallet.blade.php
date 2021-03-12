@extends('layouts.app')
@section('title', 'Wallet')
@section('page-name', 'Shika Wallet')
@section('content')

<div id="maincontent">
<div class="container-fluid" id="loansWrapper">

<div class="section">
  <div class="row">
  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($totalIn) ? number_format($totalIn) : 0}}</span>
      <div class="wgt-text"><span>In</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($totalOut) ? number_format($totalOut) : 0}}</span>
      <div class="wgt-text"><span>Out</span></div>
    </div>
  </div>

  <!-- <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">0</span>
      <div class="wgt-text"><span>Repaid</span></div>
    </div>
  </div> -->

  <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($balance) ? number_format($balance) : 0}}</span>
      <div class="wgt-text"><span>Balance</span></div>
    </div>
  </div>

</div>
</div>

<div class="section">
  <div class="row">
    <div class="col col-sm-12">
      <div class="card">

        <div class="card-header clearfix">
          <div class="row">
              
        
          </div>
      </div>
        
        <div class="card-content">

        




          
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
