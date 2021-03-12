@extends('layouts.app')
@section('title', 'Unclaimed Transactions')
@section('page-name', 'Unclaimed Transactions')
@section('content')

<div id="maincontent">
<div class="container-fluid">



<div class="section">
  <div class="row">
    <div class="col col-sm-12">
      <div class="card" id="unclaimed_txs">

        <div class="card-header clearfix">
          <div class="pull-left">

                <div class="input-group">

                    <!-- <input type="text" class="form-control" placeholder="Search"> -->
                    <input type="text" name="s" class="form-control" placeholder="Search by code or source" v-model="query" v-on:keyup="getTransactions()">

                    <span class="input-group-btn">

                        <button class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>

                    </span>

                </div>

            </div>

            <div class="pull-right"><span style="font-size: 20px;">KSHs {{isset($unclaimed) ? number_format($unclaimed->sum('amount_paid')) : 0 }}</span></div>

        </div>
        
        <div class="card-content">

        



<table class="table no-borders table-hover">
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
            <td v-cloak>@{{transaction.transaction_account}}</td>
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
    var transactions = new Vue({
        el: '#unclaimed_txs',
        data: {
            query: '',
            transactions: []
        },
        mounted(){
            this.getTransactions();
        },
        methods: {
            getTransactions: function(){
                var self = this;

                //query the backend for transactions
                axios.get("{{route('unclaimed-search')}}", {
                  params: { q: self.query }
                })
                .then(function (response) {
                  self.transactions = response.data;
                })
                .catch(function (error) {
                  console.log(error);
                });
                /////////////////////////////////////////////
            }
        }
    });
</script>
@endsection
