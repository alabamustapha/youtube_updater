@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Upload records</div>

                <div class="panel-body">
                    <form action="{{ route('add_accounts') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                        <div class="form-group">
                                <label for="accounts" class="control-label">Google account file</label>
                                <input type="file" class="form-control" id="accounts" name="accounts">
                        </div>

                        <button class="btn btn-block btn-primary" type="submit">
                                Submit
                        </button>
                    </form>
                </div>
            </div>    
        </div>            
    </div>                
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->authorized ? 'Authorized' : 'Unauthorized' }}</td>
                            <td>
                            
                            @if(!$user->authorized)
                            <form action="{{ route('get_access_token', ['user' => $user->id]) }}" method="POST">

                            {{ csrf_field() }}


                                <button class="btn btn-primary">
                                    Authorize <span class="badge badge-info"></span>
                                </button></td>
                            </form>
                            @endif
                            
                        </tr>
                        @endforeach
                    </table>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
