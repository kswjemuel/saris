@extends('layouts.app')
@section('page-name', 'System Users')
@section('content')
<div id="maincontent">
<ol class="breadcrumb">
<li><a href="{{route('home')}}">Home</a></li>
<li class="active">Users</li>
</ol>

<div class="row">
     <!-- end col -->

     <div class="col-sm-6">
         <div class="card">
             <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>System Users</strong></h4>
                 <button class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#newUser"><i class="ion-person-add"></i> Add New</button>
             </div>
             <div class="card-content">
                 <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($users)
                    @foreach($users as $key => $user)
                    <tr id="user{{$user->id}}">
                        <td>{{isset($user->name) ? $user->name : '' }}</td>
                        <td>{{isset($user->email) ? $user->email : '' }}</td>
                        <td>{{isset($user->roles->first()->name) ? $user->roles->first()->name : '' }}</td>
                        <td><a href="javascript:void(0)" onclick="deleteUser({{$user->id}})"><i class=" dripicons-trash"></i></a></td>
                    </tr>
                    @endforeach
                    @endif
                    
                    </tbody>
                </table>
             </div>
         </div>
     </div>

     <!-- end col -->


     <div class="col-sm-6">
         <div class="card">
             <div class="card-header clearfix">
                 <h4 class="m0 p0 pull-left"><strong>System Roles</strong></h4>
                 <button class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#newRole"><i class="ion-person-add"></i> Add New</button>
             </div>
             <div class="card-content">
                 <table class="table">
                    <tbody>
                    @if(isset($roles))
                    @foreach($roles as $role)
                    <tr>
                        <td>{{$role->display_name}}</td>
                    </tr>
                    @endforeach
                    @endif
                    
                    </tbody>
                </table>
             </div>
         </div>
     </div>
</div>
</div>





<div id="newUser" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<form method="post" action="{{route('user.create')}}">
{{ csrf_field() }}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="myModalLabel">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                
                    
                	<div class="form-group">
                		<label>Name</label>
                		<input type="text" name="name" class="form-control" />
                	</div>
                	
                	<div class="form-group">
                		<label>Email Address</label>
                		<input type="text" name="email" class="form-control" />
                	</div>
                	<div class="form-group">
                		<label>Role</label>
                        <select class="form-control" name="role">
                            @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->display_name}}</option>
                            @endforeach
                        </select>
                	</div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" />
                    </div>

                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-sm btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
    </form>

</div>


<div id="newRole" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
<form method="post" action="{{route('create-role')}}">
{{ csrf_field() }}
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="myModalLabel">Add New Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" />
                    </div>

                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary waves-effect" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-sm btn-primary waves-effect waves-light">Add</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
    </form>

</div>

@endsection

@section('page-scripts')
<script type="text/javascript">

function deleteUser(id){
    alertify.confirm("You are about to delete a user, are you sure that is a good idea?", function(e){
        axios.post('{{route('user.delete')}}', { id: id})
        .then(function (response) {
             alertify.success(response.data);
             $('tr#user'+id).fadeOut();
        })
        .catch(function (error) {
            console.log(error);
        });
    }, function(e){
        //alertify.error('You canceled');
    });
}
</script>
@endsection
