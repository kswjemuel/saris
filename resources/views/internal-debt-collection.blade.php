@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Internal Debt Collection')
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

        


<div id="collectionList">

<table class="table no-borders table-hover">
    <thead class="thead">
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>PHONE</th>
            <th>GENDER</th>
            <th>AGE</th>
            <th>AMOUNT</th>
            <th>DUE</th>
            <th>ISSUED AT</th>
            <th>DUE AT</th>
            <th>DAYS LATE</th>
            <th>COMMITMENT</th>
            <th>STATUS</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <tr v-for="loan in loans">
            <td v-cloak>@{{loan.customer_id}}</td>
            <td v-cloak>@{{loan.name}}</td>
            <td v-cloak>@{{loan.phone}}</td>
            <td v-cloak>@{{loan.gender}}</td>
            <td v-cloak>@{{loan.age}}</td>
            <td v-cloak>@{{loan.disbursed}}</td>
            <td v-cloak>@{{loan.due}}</td>
            <td v-cloak>@{{loan.created_at}}</td>
            <td v-cloak>@{{loan.due_at}}</td>
            <td v-cloak>@{{loan.days_late}}</td>
            <td v-cloak>@{{loan.commitment_date}}</td>
            <td v-cloak>@{{loan.status}}</td>
            <td><a href="javascript:void(0)" @click="setLoan(loan)" data-toggle="modal" data-target="#commitmentModal">Comment</a></td>
        </tr>
    </tbody>
</table>



    <!-- Modal -->
<div id="commitmentModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">@{{loan.name}}</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
        <label for="">Customer Comment</label>
        <textarea rows="3" class="form-control" v-model="commitment.comment"></textarea>
        </div>

        <div class="form-group">
        <label for="">Commitment Date</label>
        <div class="input-group">
            <input type="text" class="form-control" id="commitment_date" placeholder="Select date" onkeypress="return false;" v-model="commitment.date">
            <div class="input-group-addon">
            <span class="dripicons-calendar"></span>
            </div>
        </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" @click="saveCommitment">@{{saveCTA}}</button>
      </div>
    </div>

  </div>
</div>

</div>

          
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
        loans: [],
        loan: '',
        commitment: {},
        saveCTA: 'Save'
    },
    mounted(){
        this.getLoansByDate("");
    },
    methods: {
        getLoansByDate: function(start_date, end_date){
            var self = this;

            //query the backend for repayments
            axios.get("{{route('internal-debt-collection-json')}}", {
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
        },
        setLoan: function(loan){
            this.loan = loan;
        },
        saveCommitment: function(){
            var self = this;
            self.commitment.loan_id = self.loan.id;
            self.commitment.customer_id = self.loan.customer_id;
            self.saveCTA = "Saving...";

            axios.post("{{route('create-commitment')}}", self.commitment)
            .then(function(response){
                console.log(response.data);
                self.saveCTA = "Save";
                self.commitment = {};
                loans.getLoansByDate(start_date, end_date);
                $('#commitmentModal').modal('hide');
            })
            .catch(function(err){
                console.log(err);
            })
        }
    }
});

$(function(){
    $('#commitment_date').datepicker({
        format: "dd-mm-yyyy",
        startDate: '+0d',
        autoclose:true
    }).on('changeDate', function(e){
        //set the commitment date
        loans.commitment.date = $(this).val();
        //console.log(loans.commitment);
    });

    $('#commitmentModal').on('hidden.bs.modal', function (){
        loans.loan = '';
        console.log(loans.loan);
    });


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

