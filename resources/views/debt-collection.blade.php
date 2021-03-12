@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Debt Collection')
@section('content')

<div id="maincontent">
<div class="container-fluid">



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

        <a id="csvURL" href="javascript:void(0)" data-url="{{route('debt-collection-csv')}}" onclick="downloadCSV()" class="input-group-addon">
        <span class="dripicons-download"></span>
        </a>
        </div>
        </div>

        
          </div>
      </div>

        
        
        <div class="card-content">

        



<table id="collectionList" class="table no-borders table-hover">
    <thead class="thead">
        <tr>
            <th>NAME</th>
            <th>EMAIL</th>
            <th>PHONE</th>
            <th>ID NUMBER</th>
            <th>GENDER</th>
            <th>AGE</th>
            <th>AMOUNT</th>
            <th>DUE</th>
            <th>ISSUED AT</th>
            <th>DUE AT</th>
            <th>DAYS LATE</th>
            <th>STATUS</th>
        </tr>
    </thead>

    <tbody>
        <tr v-for="loan in loans">
            <td v-cloak>@{{loan.name}}</td>
            <td v-cloak>@{{loan.email}}</td>
            <td v-cloak>@{{loan.phone}}</td>
            <td v-cloak>@{{loan.identification}}</td>
            <td v-cloak>@{{loan.gender}}</td>
            <td v-cloak>@{{loan.age}}</td>
            <td v-cloak>@{{loan.disbursed}}</td>
            <td v-cloak>@{{loan.due}}</td>
            <td v-cloak>@{{loan.created_at}}</td>
            <td v-cloak>@{{loan.due_at}}</td>
            <td v-cloak>@{{loan.days_late}}</td>
            <td v-cloak>@{{loan.status}}</td>
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
var day = "";
var start_date = "";
var end_date = "";

var loans = new Vue({
    el: '#collectionList',
    data: {
        //critical: [],
        loans: []
    },
    mounted(){
        this.getLoansByDate("");
    },
    methods: {
        getLoansByDate: function(start_date, end_date){
            var self = this;

            //query the backend for repayments
            axios.get("{{route('debt-collection-list')}}", {
              params: { start_date: start_date, end_date: end_date }
            })
            .then(function (response) {
              self.loans = response.data;
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
    // $('#date_search').datepicker({
    //     format: "dd-mm-yyyy",
    //     endDate: '+0d',
    //     autoclose:true
    // }).on('changeDate', function(e){
    //     day = $(this).val();
    //     loans.getLoansByDate(day);
    // });


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

