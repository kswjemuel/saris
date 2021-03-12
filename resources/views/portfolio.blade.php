@extends('layouts.app')
@section('title', 'Portfolio Monitor')
@section('page-name', 'Portfolio Monitor')
@section('content')

<div id="maincontent">



<div class="section">
    <div class="row">
        <div class="col-sm-7">
            <div class="card">
                <div class="card-header clearfix">
                    <h4 class="pull-left">Portfolio</h4>
                    <h4 class="pull-right"><strong>Report Date:</strong> {{$report->date_captured}}</h4>


                    <!-- <div class="col-md-4 col-sm-6 pull-right">
<div class="input-group">
  <span class="input-group-addon dripicons-calendar" id="basic-addon1">
      
  </span>
  <input type="text" class="form-control" name="daterange" value="" aria-describedby="basic-addon1">
</div>
                    </div> -->
                </div>
                <div class="card-body card-padding">
                    <table class="table">
                        <thead>
                            <th>KPI</th>
                            <th>Actual</th>
                            <th>Target</th>
                            <th>Difference</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Customers</td>
                                <td>{{isset($report->customers) ? number_format($report->customers) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Acceptance Rate</td>
                                <td>{{isset($report->acceptance_rate) ? $report->acceptance_rate : 0}}%</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>Disbursed</td>
                                <td>{{isset($report->disbursed) ? number_format($report->disbursed) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @if(Auth::user()->email == "kimkiuna@gmail.com")
                            <tr>
                                <td>Paid Back (for this month's loans) plus fees and penalities</td>
                                <td>{{isset($report->paid_back) ? number_format($report->paid_back) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>Difference</td>
                                <td>{{isset($report->income) ? number_format($report->income) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            @endif
                            <tr>
                                <td>All Loans</td>
                                <td>{{isset($report->loans_count) ? number_format($report->loans_count) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>New Customers</td>
                                <td>{{isset($report->newloans) ? number_format($report->newloans) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Repeat Customers</td>
                                <td>{{isset($report->repeatloans) ? number_format($report->repeatloans) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Repeat Customers %</td>
                                <td>{{isset($report->repeat_percentage) ? number_format($report->repeat_percentage, 2) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>Avg. Loan Amount</td>
                                <td>{{isset($report->avg_amount) ? number_format($report->avg_amount) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 1-30</td>
                                <td>{{isset($report->dpd_0_30) ? number_format($report->dpd_0_30) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 1-30 % (Loans)</td>
                                <td>{{isset($report->dpd_0_30_percentage) ? number_format($report->dpd_0_30_percentage) : 0}} %</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 1-30 % (Amount)</td>
                                <td>{{isset($report->dpd1_percent) ? number_format($report->dpd1_percent) : 0}} %</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 30+ (Loans)</td>
                                <td>{{isset($report->dpd_30_plus) ? number_format($report->dpd_30_plus) : 0}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 30+ % (Loans)</td>
                                <td>{{isset($report->dpd_30plus_percentage) ? number_format($report->dpd_30plus_percentage) : 0}} %</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>DPD 30+ %(Amount)</td>
                                <td>{{isset($report->dpd2_percent) ? number_format($report->dpd2_percent) : 0}} %</td>
                                <td></td>
                                <td></td>
                            </tr>
                            

                            <tr>
                                <td>Collection Rate (%) <br> (dpd 1-30 - dpd 30+)/dpd 1-30<br>
                                    Late Unpaid: {{isset($report->late_unpaid) ? number_format($report->late_unpaid) : 0}} | Late Paid: {{isset($report->late_paid) ? number_format($report->late_paid) : 0}}</td>
                                <td>{{isset($report->collection_rate) ? number_format($report->collection_rate) : 0}} %</td>
                                <td></td>
                                <td></td>
                            </tr>

                        </tbody>
                    </table>
                    <!-- <canvas id="myChart" height="400"></canvas> -->
                    <!-- <div id="chart" style="height: 400px;"></div> -->
                </div>

                <!-- <div class="clearfix">
                        <h4 class="col-sm-3">Loans: <span id="totalLoans"></span></h4>
                        <h4 class="col-sm-3">Repayments: <span id="totalRepayments"></span></h4>
                        <h4 class="col-sm-3">Penalties: <span id="totalPenalties"></span></h4>
                        <h4 class="col-sm-3">Customers: <span id="totalCustomers"></span></h4>
                </div> -->
            </div>
        </div>

        <div class="col-sm-5">
            <div class="card">
                <div class="card-header">
                    <h4>Months</h4>
                </div>
                <div class="card-body card-padding">
                    <ul class="">
                        <li class=""><a href="{{route('portfolio', ['m' => '1'])}}">January</a></li>
                        <li><a href="{{route('portfolio', ['m' => '2'])}}">February</a></li>
                        <li><a href="{{route('portfolio', ['m' => '3'])}}">March</a></li>
                        <li><a href="{{route('portfolio', ['m' => '4'])}}">April</a></li>
                        <li><a href="{{route('portfolio', ['m' => '5'])}}">May</a></li>
                        <li><a href="{{route('portfolio', ['m' => '6'])}}">June</a></li>
                        <li><a href="{{route('portfolio', ['m' => '7'])}}">July</a></li>
                        <li><a href="{{route('portfolio', ['m' => '8'])}}">August</a></li>
                        <li><a href="{{route('portfolio', ['m' => '9'])}}">September</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
      
</div>



@endsection

@section('page-scripts')
<script type="text/javascript">
    $(function(){
        //daterange selector
        // $('input[name="daterange"]').daterangepicker({
        //     opens: 'left'
        //   },function(start, end, label){
        //     console.log(start.format('DD-MM-YYYY'));
        //     console.log(end.format('DD-MM-YYYY'));
        //     //getGraphData(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));
        // });
        ///////////////////////////////////////////////////////////////////////
    });
</script>
@endsection