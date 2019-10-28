@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <form class="form-horizontal" action="/edit" method="POST">
                        <fieldset>

                            <!-- Form Name -->
                            <legend>VPN</legend>
                            @csrf
                            <input hidden name="id" value="{{$vpn->id ?? ''}}">
                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="login">Login</label>
                                <div class="col-md-4">
                                    <input id="login" name="login" type="text"  class="form-control input-md" required="" value="{{$vpn->username ?? ''}}">
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="pwd">Password</label>
                                <div class="col-md-4">
                                    <input id="pwd" name="pwd" type="text" placeholder="" class="form-control input-md" required="" value="{{$vpn->password ?? ''}}">
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="ip">IP</label>
                                <div class="col-md-4">
                                    <input id="ip" name="ip" type="text" placeholder="192.168.1.1" class="form-control input-md" required="" value="{{$vpn->ip ?? ''}}">
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="fname">Full name</label>
                                <div class="col-md-4">
                                    <input id="fname" name="fname" type="text" placeholder="" class="form-control input-md" value="{{$vpn->fullname ?? ''}}">
                                </div>
                            </div>

                            <!-- Textarea -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="comment">Comment</label>
                                <div class="col-md-4">
                                    <textarea class="form-control" id="comment" name="comment" value="{{$vpn->comment ?? ''}}"></textarea>
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="btn"></label>
                                <div class="col-md-4">
                                    <button id="btn" name="btn" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                            @if($vpn->id)
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="btn2"></label>
                                <div class="col-md-4">
                                    <a id="btn2"  class="btn btn-danger"
                                            href = '{{url("remove/$vpn->id")}}'>Remove</a>
                                </div>
                            </div>
                            @endif
                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
