@extends('layouts.app')
@section('title', 'Repayments')
@section('page-name', 'Repayments')
@section('content')

<div id="maincontent">
<div class="container-fluid">



<div class="section">
  <div class="row" id="repayments_txs">
    <div class="col-sm-12">
        <h3>Total Repayments: </h3>
        <h4>Count: @{{count}} Amount: @{{total}}</h4>
    </div>
    <div class="col col-sm-12">
      <div class="card">

      <div class="card-header clearfix">
          <div class="row">
              
          <div class="col-sm-5 pull-left">

                <div class="input-group">

                    <!-- <input type="text" class="form-control" placeholder="Search"> -->
                    <input type="text" name="s" class="form-control" placeholder="Search by code or source" v-model="query" v-on:keyup="getTransactions()">

                    <span class="input-group-btn">

                        <button class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>

                    </span>

                </div>

            </div>

        <div class="col-sm-3 pull-right">
        <div class="input-group date">
        <input type="text" class="form-control" id="date_search" placeholder="Select date" onkeypress="return false;">
        <div class="input-group-addon">
        <span class="dripicons-calendar"></span>
        </div>
        <a id="csvURL" href="javascript:void(0)" data-url="{{route('repayments-csv')}}" onclick="downloadCSV()" class="input-group-addon">
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
            <th>Code</th>
            <th>Source</th>
            <th>Account</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        <tr v-for="transaction in transactions">
            <td v-cloak>@{{transaction.mobile_wallet_transaction_id}}</td>
            <td v-cloak>@{{transaction.transaction_source}}</td>
            <td v-cloak><a v-bind:href="transaction.link">@{{transaction.transaction_account}}</a></td>
            <td v-cloak>@{{transaction.amount_paid}}</td>
            <td v-cloak>@{{transaction.transaction_date}}</td>
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
var the_date = "";
var transactions = new Vue({
    el: '#repayments_txs',
    data: {
        query: '',
        transactions: [],
        count: 0,
        total: 0
    },
    mounted(){
        this.getTransactions();
    },
    methods: {
        getTransactions: function(){
            var self = this;

            //query the backend for repayments
            axios.get("{{route('repayments-search')}}", {
              params: { q: self.query }
            })
            .then(function (response) {
              self.transactions = response.data;
            })
            .catch(function (error) {
              console.log(error);
            });
            /////////////////////////////////////////////
        },
        getTransactionsByDate: function(dt){
            var self = this;
            //query the backend for repayments
            axios.get("{{route('daily-repayments')}}", {
              params: { q: dt }
            })
            .then(function (response) {
              self.transactions = response.data.transactions;
              self.count = response.data.count;
              self.total = response.data.total;
            })
            .catch(function (error) {
              console.log(error);
            });
            /////////////////////////////////////////////
        }
    }
});

$(function(){
    $('#date_search').datepicker({
        format: "dd-mm-yyyy",
        endDate: '+0d',
        autoclose:true
    }).on('changeDate', function(e){
        the_date = $(this).val();
        transactions.getTransactionsByDate(the_date);
    });
});


function downloadCSV(){
  //console.log(day);
  window.location.href = $('#csvURL').attr('data-url')+"?d="+the_date;
  /////////////////////////////////////////////
}
</script>
@endsection
