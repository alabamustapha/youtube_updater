@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">

                        <form action="{{ route("update") }}" method="POST">
                                {{ csrf_field() }}

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <label>Enter Channel ID</label>
                                    <input type="text" class="form-control" placeholder="channel_id" name="channel_id">
                                </div>

                                <div class="form-group">
                                    <label>Subscribes ?</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <input type="checkbox" name="subscribe">
                                        </span>
                                        <input type="number" class="form-control" name="quantity" placeholder="how many">
                                    </div><!-- /input-group -->
                                </div>
                            </div>
                                

                            <div class="col-md-6">
                            
                                    <div class="form-group">
                                            <label>Enter Video ID</label>
                                            <input type="text" class="form-control" placeholder="video_id" name="video_id">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Likes ?</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <input type="checkbox" name="like">
                                                        </span>
                                                        <input type="number" class="form-control" name="likes_quantity" placeholder="how many">
                                                    </div><!-- /input-group -->
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Unlikes ?</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <input type="checkbox" name="unlike">
                                                        </span>
                                                        <input type="number" class="form-control" name="unlikes_quantity" placeholder="how many">
                                                    </div><!-- /input-group -->
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Comments ?</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <input type="checkbox" name="comment">
                                                        </span>
                                                        <input type="number" class="form-control" name="comment_quantity" placeholder="how many">
                                                    </div><!-- /input-group -->
                                                </div>
                                            </div> 
                                        </div>  
                            
                            </div>

                            </div>
                                
                                <button class="btn btn-primary btn-lg">
                                        Continue
                                </button>
                            </form>   
                
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection
