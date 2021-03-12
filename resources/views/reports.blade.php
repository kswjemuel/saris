@extends('layouts.app')
@section('title', 'Financial Reports')
@section('page-name', 'Financial Reports')
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
                        <h4 class="col-sm-3">Loans: <span id="totalLoans"></span></h4>
                        <h4 class="col-sm-3">Repayments: <span id="totalRepayments"></span></h4>
                        <h4 class="col-sm-3">Penalties: <span id="totalPenalties"></span></h4>
                        <h4 class="col-sm-3">Customers: <span id="totalCustomers"></span></h4>
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
    axios.get("{{route('date-range-data')}}", {
    params: { start_date: start_date, end_date: end_date }
  })
  .then(function (response) {
    //show the totals
    $('#totalLoans').text(response.data.total.loans);
    $('#totalRepayments').text(response.data.total.repayments);
    $('#totalPenalties').text(response.data.total.penalties);
    $('#totalCustomers').text(response.data.total.customers);

    var loans = response.data.loans;
    var repayments = response.data.repayments;
    var penalties = response.data.penalties;
    var customers = response.data.customers;
    //console.log(thedates);
    //counts.unshift('Customers')
    loans.unshift('Loans');
    repayments.unshift('Repayments');
    penalties.unshift('Penalties');
    customers.unshift('Customers');
    ////////////Draw the graph///////////////////////////////////
    var chart = c3.generate({
        bindto: '#chart',
        data: {
          columns: [loans, repayments, customers, penalties],
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