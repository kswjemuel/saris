@extends('layouts.lenders')
@section('title', 'Dashboard')
@section('page-name', 'Dashboard')
@section('content')

<div id="maincontent">
<div class="container-fluid">
<div class="section">
  <div class="row">
  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->customers) ? number_format($critical->customers) : 0}}</span>
      <div class="wgt-text"><span>Customers</span></div>
      <span class="label label-success">Active: {{isset($critical->active_customers) ? number_format($critical->active_customers) : 0}}</span>
    </div>
  </div>

  <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->loans) ? number_format($critical->loans) : 0}}</span>
      <div class="wgt-text"><span>Outstanding Loans</span></div>
      <span class="label label-success">Active: {{isset($critical->active) ? number_format($critical->active) : 0}}</span> 
      <span class="label label-warning">Late: {{isset($critical->late) ? number_format($critical->late) : 0}}</span>
    </div>
  </div>


  <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->disbursed) ? number_format($critical->disbursed) : 0}}</span>
      <div class="wgt-text"><span>Outstanding Amount (KSHs)</span></div>
      <span class="label label-success">Active: {{isset($loans->active) ? number_format($loans->active) : 0}}</span> 
      <span class="label label-warning">Late: {{isset($loans->late) ? number_format($loans->late) : 0}}</span>
    </div>
  </div>

  <!-- <div class="col col-md-3 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($loans->active) ? number_format($loans->active) : 0}}</span>
      <div class="wgt-text"><span>Active (KSHs)</span></div>
    </div>
  </div> -->


  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->repaid) ? number_format($critical->repaid) : 0}}</span>
      <div class="wgt-text"><span>Paid Back (KSHs)</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain text-primary">
      <span class="wgt-figure">{{isset($default_rate) ? number_format($default_rate) : 0}}%</span>
      <div class="wgt-text"><span>At Risk</span></div>
    </div>
  </div>

</div>
</div>




<div class="section">
  <div class="row">
    <div class="col col-sm-7">
      <div class="card">
        <div class="card-header">
          <h4 class="m0 p0">Monthly Loans &amp; Repayments</h4>
        </div>
        <div class="card-content">
          <div id="monthlyData"></div>
        </div>
      </div>
    </div>

    <div class="col col-sm-5">
      <div class="card">
        <div class="card-header">
          <h4 class="m0 p0">Loan Book</h4>
        </div>
        <div class="card-content">
          <div id="dthreedonut"></div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="section">
  <div class="row">
    <div class="col col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4 class="m0 p0">Total Demand</h4>
        </div>
        <div class="card-content">
          <table class="table">
            <thead>
              <th>RANKING</th>
              <th>CUSTOMERS</th>
              <th>DEMAND</th>
            </thead>

            <tbody>
              <tr>
                <td>GREEN</td>
                <td>{{isset($a_customers->green) ? number_format($a_customers->green) : 0}}</td>
                <td>{{isset($total_demand->green) ? number_format($total_demand->green) : 0}}</td>
              </tr>
              <tr>
                <td>BRONZE</td>
                <td>{{isset($a_customers->bronze) ? number_format($a_customers->bronze) : 0}}</td>
                <td>{{isset($total_demand->bronze) ? number_format($total_demand->bronze) : 0}}</td>
              </tr>
              <tr>
                <td>SILVER</td>
                <td>{{isset($a_customers->silver) ? number_format($a_customers->silver) : 0}}</td>
                <td>{{isset($total_demand->silver) ? number_format($total_demand->silver) : 0}}</td>
              </tr>
              <tr>
                <td>GOLD</td>
                <td>{{isset($a_customers->gold) ? number_format($a_customers->gold) : 0}}</td>
                <td>{{isset($total_demand->gold) ? number_format($total_demand->gold) : 0}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>



    <div class="col col-sm-6">
      <div class="card">
        <div class="card-header">
          <h4 class="m0 p0">Unmet Demand</h4>
        </div>
        <div class="card-content">
          <table class="table">
            <thead>
              <th>RANKING</th>
              <th>CUSTOMERS</th>
              <th>DEMAND</th>
            </thead>

            <tbody>
              <tr>
                <td>GREEN</td>
                <td>{{isset($customer_stats->green) ? number_format($customer_stats->green) : 0}}</td>
                <td>{{isset($demand->green) ? number_format($demand->green) : 0}}</td>
              </tr>
              <tr>
                <td>BRONZE</td>
                <td>{{isset($customer_stats->bronze) ? number_format($customer_stats->bronze) : 0}}</td>
                <td>{{isset($demand->bronze) ? number_format($demand->bronze) : 0}}</td>
              </tr>
              <tr>
                <td>SILVER</td>
                <td>{{isset($customer_stats->silver) ? number_format($customer_stats->silver) : 0}}</td>
                <td>{{isset($demand->silver) ? number_format($demand->silver) : 0}}</td>
              </tr>
              <tr>
                <td>GOLD</td>
                <td>{{isset($customer_stats->gold) ? number_format($customer_stats->gold) : 0}}</td>
                <td>{{isset($demand->gold) ? number_format($demand->gold) : 0}}</td>
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

<script>
$(function(){
  //generate the monthly data graph
  getMonthlyGraphData(0, 0);
})
var chart = c3.generate({
    bindto: '#dthreedonut',
    data: {
        columns: [
            ['Active', {{$loans->active}}],
            ['Completed', {{$loans->completed}}],
            ['Late', {{$loans->defaulted}}]
        ],
        type : 'donut',
        colors: {
            Active: '#95519f',
            Completed: '#e2519f',
            Late: 'rgba(192, 57, 43, 1)'
        },
        onclick: function (d, i) { console.log("onclick", d, i); },
        onmouseover: function (d, i) { console.log("onmouseover", d, i); },
        onmouseout: function (d, i) { console.log("onmouseout", d, i); }
    },
    donut: {
        title: "Loans Statistics"
    }
});

function getMonthlyGraphData(start_date, end_date){
    axios.get("{{route('monthly-data')}}", {
    params: { start_date: start_date, end_date: end_date }
  })
  .then(function (response) {
    var loans = response.data.loans;
    var repayments = response.data.repayments;
    //Add the labels to the data
    loans.unshift('Loans');
    repayments.unshift('Repayments');
    ////////////Draw the graph///////////////////////////////////
    var chart = c3.generate({
        bindto: '#monthlyData',
        data: {
          columns: [loans, repayments],
          colors: { Loans: "#95519f", Repayments: "#e2519f"},
          type: 'bar'
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
                categories: response.data.months,
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
