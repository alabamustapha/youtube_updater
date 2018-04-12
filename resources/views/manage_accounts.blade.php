@extends('layouts.app')

@section('content')
<div class="container">
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
