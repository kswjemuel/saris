@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Loans')
@section('content')

<div id="maincontent">
<div class="container-fluid" id="loansWrapper">

<div class="section">
  <div class="row">
  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure" v-cloak>@{{critical.count}}</span>
      <div class="wgt-text"><span>Loans</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure" v-cloak>@{{critical.disbursed}}</span>
      <div class="wgt-text"><span>Disbursed</span></div>
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
      <span class="wgt-figure" v-cloak>@{{critical.late_amount}}</span>
      <div class="wgt-text"><span>Late (@{{critical.late_loans}})</span></div>
    </div>
  </div>


  <!-- <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">0</span>
      <div class="wgt-text"><span>Defaulted</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain text-primary">
      <span class="wgt-figure">0%</span>
      <div class="wgt-text"><span>Default Rate</span></div>
    </div>
  </div> -->

</div>
</div>

<div class="section">
  <div class="row">
    <div class="col col-sm-12">
      <div class="card">

        <div class="card-header clearfix">
          <div class="row">
              
          

        <div class="col-sm-3 pull-right">
        <div class="input-group date">
        <input type="text" class="form-control" id="date_search" placeholder="Select date" onkeypress="return false;">
        <div class="input-group-addon">
        <span class="dripicons-calendar"></span>
        </div>
        <a id="csvURL" href="javascript:void(0)" data-url="{{route('loans-by-date-csv')}}" onclick="downloadCSV()" class="input-group-addon">
        <span class="dripicons-download"></span>
        </a>
        </div>
        </div>

        
          </div>
      </div>
        
        <div class="card-content">

        



<table id="customers" class="table no-borders table-hover">
    <thead class="thead">
        <tr>
            <th>CODE</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>

        <tr v-for="loan in loans">
            <td v-cloak>@{{loan.code}}</td>
            <td v-cloak><a v-bind:href="loan.link">@{{loan.customer_name}}</a></td>
            <td v-cloak>@{{loan.phone}}</td>
            <td v-cloak>@{{loan.amount}}</td>
            <td v-cloak>@{{loan.disbursed_on}}</td>
            <td v-cloak>@{{loan.loan_status}}</td>
        </tr>
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

@section('page-scripts')
<script type="text/javascript">
var start_date = "";
var end_date = "";

var loans = new Vue({
    el: '#loansWrapper',
    data: {
        critical: [],
        loans: []
    },
    mounted(){
        this.getLoansByDate();
    },
    methods: {
        getLoansByDate: function(start_date, end_date){
            var self = this;
            //query the backend for repayments
            axios.get("{{route('loans-by-date')}}", {
              params: { start_date: start_date, end_date: end_date }
            })
            .then(function (response) {
              self.loans = response.data.loans;
              self.critical = response.data.critical;
              //self.critical = response.data.critical;
            })
            .catch(function (error) {
              console.log(error);
            });
            /////////////////////////////////////////////
        }
    }
});

$(function(){
    $('#date_search').daterangepicker({
        opens: 'left'
      },function(start, end, label){
        start_date = start.format('DD-MM-YYYY');
        end_date = end.format('DD-MM-YYYY');
        loans.getLoansByDate(start_date, end_date);
    });
});



function downloadCSV(){
  //console.log(day);
  window.location.href = $('#csvURL').attr('data-url')+"?start_date="+start_date+"&end_date="+end_date;
  /////////////////////////////////////////////
}
</script>
@endsection
