@extends('layouts.app')
@section('title', 'Financial Reports')
@section('page-name', 'Financial Reports')
@section('content')

<div id="maincontent">



<div class="section">
    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header clearfix">
                    <h4 class="pull-left">Income</h4>


                    <div class="col-sm-6 pull-right">
<div class="input-group">
  <span class="input-group-addon dripicons-calendar" id="basic-addon1">
      
  </span>
  <input type="text" class="form-control" name="daterange" value="" aria-describedby="basic-addon1">
</div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- <canvas id="myChart" height="400"></canvas> -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>SOURCE</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Accrued</td>
                                <td>204100</td>
                            </tr>
                            <tr>
                                <td>Actual</td>
                                <td>35600</td>
                            </tr>
                            <tr>
                                <td>Penalties</td>
                                <td>15000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                
            </div>
        </div>
    </div>
</div>
      
</div>



@endsection

@section('page-scripts')
<script type="text/javascript">

$(document).ready(function(){
    //getGraphData(0,0);

    $('input[name="daterange"]').daterangepicker({
        opens: 'left'
      },function(start, end, label){
        console.log(start.format('DD-MM-YYYY'));
        console.log(end.format('DD-MM-YYYY'));
        //getGraphData(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));
    });
});



</script>
@endsection