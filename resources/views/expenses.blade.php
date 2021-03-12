@extends('layouts.app')
@section('title', 'Expenses Reports')
@section('page-name', 'Expenses Reports')
@section('content')

<div id="maincontent">



<div class="section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header clearfix">
                    <h4 class="pull-left">Last 30 Days</h4>


                    <div class="col-md-4 col-sm-6 pull-right">
<div class="input-group">
  <span class="input-group-addon dripicons-calendar" id="basic-addon1">
      
  </span>
  <input type="text" class="form-control" name="daterange" value="" aria-describedby="basic-addon1">
</div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- <canvas id="myChart" height="400"></canvas> -->
                    <div id="chart" style="height: 400px;"></div>
                </div>

                <div class="clearfix">
                        <h4 class="col-sm-3">Disbursement: <span id="totalDisbursement"></span>
                            <br><small>MPesa disbursement fee</small></h4>
                        <h4 class="col-sm-3">Repayments: <span id="totalRepayment"></span>
                            <br><small>AT collection fee</small></h4>
                        <h4 class="col-sm-3">AT: <span id="totalAT"></span>
                            <br><small>AT disbursement fee</small>
                        </h4>
                        <h4 class="col-sm-3">SMS: <span id="totalSMS"></span>
                            <br><small>AT SMSs fee</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>
      
</div>



@endsection

@section('page-scripts')
<script type="text/javascript">

//     var customerData = JSON.parse($('#sevenDaysGraph').attr('data-counts'));
//     //console.log(customerData.dates);
//     customerData.counts.unshift('Customers')


$(document).ready(function(){
    getGraphData(0,0);

    // $('#customersStartDate, #financialsStartDate').datepicker({
    //     format: "dd-mm-yyyy",
    //     endDate: '+0d',
    //     autoclose:true
    // });

    $('input[name="daterange"]').daterangepicker({
        opens: 'left'
      },function(start, end, label){
        console.log(start.format('DD-MM-YYYY'));
        console.log(end.format('DD-MM-YYYY'));
        getGraphData(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));
    });
});

function getGraphData(start_date, end_date){
    axios.get("{{route('expenses-data')}}", {
    params: { start_date: start_date, end_date: end_date }
  })
  .then(function (response) {
    //show the totals
    $('#totalDisbursement').text(response.data.total.disbursement);
    $('#totalRepayment').text(response.data.total.repayment);
    $('#totalAT').text(response.data.total.at);
    $('#totalSMS').text(response.data.total.sms);

    var disbursement = response.data.disbursement;
    var at = response.data.at;
    var repayment = response.data.repayment;
    var sms = response.data.sms;
    //console.log(thedates);
    //counts.unshift('Customers')
    disbursement.unshift('Disbursement');
    at.unshift('AT Fees');
    repayment.unshift('Repayment');
    sms.unshift('SMS');
    ////////////Draw the graph///////////////////////////////////
    var chart = c3.generate({
        bindto: '#chart',
        data: {
          columns: [disbursement, at, repayment, sms],
          //colors: { Customers: '#75bdc4', Loans: "#97519f", Repayments: "#91b2cb"},
          type: 'spline'
        },
        tooltip: {
            format: {
                value: function(value) {
                    return value.toLocaleString();
                }
            }
        },
        axis: {
            x: {
                type: 'category', // this needed to load string x value
                categories: response.data.days,
                //show: false
            },
            y: {
                label: {
                    text: '',
                    position: 'outer-middle'
                }
            }
        }
    });
    //////////////////////////////////////////////////////////////
  })
  .catch(function (error) {
    console.log(error);
  });
}

</script>
@endsection