@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-name', 'Customers')
@section('content')

<div id="maincontent">
<div class="container-fluid">

<div class="section">
  <div class="row text-center">
  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->new) ? number_format($critical->new) : 0}}</span>
      <div class="wgt-text">
      <span>Today</span>
      </div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->green) ? number_format($critical->green) : 0}}</span>
      <div class="wgt-text"><span>Green</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->bronze) ? number_format($critical->bronze) : 0}}</span>
      <div class="wgt-text"><span>Bronze</span></div>
    </div>
  </div>


  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->silver) ? number_format($critical->silver) : 0}}</span>
      <div class="wgt-text"><span>Silver</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain">
      <span class="wgt-figure">{{isset($critical->gold) ? number_format($critical->gold) : 0}}</span>
      <div class="wgt-text"><span>Gold</span></div>
    </div>
  </div>

  <div class="col col-md-2 col-xs-6">
    <div class="wgt-plain text-primary">
      <span class="wgt-figure">{{isset($critical->rejection_rate) ? number_format($critical->rejection_rate) : 0}}%</span>
      <div class="wgt-text"><span>Rejection Rate</span></div>
    </div>
  </div>

  <div class="col-xs-12">
    <div class="declined-nobee text-right">
      <p><strong>{{isset($critical->declined) ? number_format($critical->declined) : 0}}</strong> Declined,
      <strong>{{isset($critical->nobee) ? number_format($critical->nobee) : 0}}</strong> have no BEE records</p>
    </div>
    
  </div>

</div>
</div>

<div class="section">
  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-header clearfix">
          <div class="row">
            <div class="col-xs-5">
              <form class="pull-left" onsubmit="return false;">

                <div class="input-group">

                    <!-- <input type="text" class="form-control" placeholder="Search"> -->
                    <input type="text" name="s" class="form-control" placeholder="Search by name or phone" onkeyup="getCustomerData(this.value)">

                    <span class="input-group-btn">

                        <button class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>

                    </span>

                </div>

            </form>
            </div>
            <div class="col-xs-7 text-right">
              <a href="javascript:void(0)" onclick="getCustomerData('blocked')" class="btn btn-sm btn-danger">Blocked</a>
            </div>
          </div>
        </div>
        <div class="card-content">

        



<table id="customers" class="table no-borders table-hover">
    <thead class="thead">
        <tr>
            <th></th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>ID</th>
            <th>Date</th>
            <th>Limit</th>
            <th>Status</th>
            <th>Details</th>
        </tr>
    </thead>

    <tbody>
        <tr v-for="item in items">
            <td class="text-center">
            <span v-bind:class="[item.status, item.blocked]" class="customer-status"></span>
            </td>
            <td v-cloak>@{{item.name}}</td>
            <td v-cloak v-bind:class="item.verified">@{{item.email_address}}</td>
            <td v-cloak>@{{item.phone_number}}</td>
            <td v-cloak>@{{item.identification}}</td>
            <td v-cloak>@{{item.join_date}}</td>
            <td v-cloak>@{{item.bee_limit}}</td>
            <td v-cloak>@{{item.bee_status}}</td>
            <td v-cloak><a v-bind:href="item.link">Details</a></td>
            

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
  var mycustomers = new Vue({
  el: 'table#customers',
  data: {
    items: []
  },
  mounted(){
    var self = this;
    axios.get("{{route('customers.search')}}")
      .then(function (response) {
        console.log(response.data);
        self.items = response.data;
      })
      .catch(function (error) {
        console.log(error);
      });
  },
  methods:{
    quickPreview: function(event){
      

    }
  }
});

function getCustomerData(query){
  if(query.length > 2 || query.length == 0){
    axios.get("{{route('customers.search')}}", {
      params: { q: query }
    })
    .then(function (response) {
      mycustomers.items = response.data;
    })
    .catch(function (error) {
      console.log(error);
    });
  }
}
</script>
@endsection
