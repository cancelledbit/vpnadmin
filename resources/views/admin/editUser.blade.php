@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-md-12">
            <form role="form" action="/admin/users/edit/{{$id}}" method="post">
                @csrf
                <div class="form-group">
                    <label for="email">
                        Email address
                    </label>
                    <input name="email" type="email" class="form-control" id="email" value="{{$email ?? ''}}"/>
                </div>
                <div class="form-group">
                    <label for="name">
                        Email address
                    </label>
                    <input name="name" type="text" class="form-control" id="name" value="{{$name ?? ''}}"/>
                </div>
                <div class="form-group">
                    <label for="role">
                        Role
                    </label>
                    <select name="role" id="role">
                        @foreach($roles as $role)
                            <option @if($currentRole == $role) selected @endif value="{{$role}}">{{$role}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="pass">
                        Password
                    </label>
                    <input name="password" type="password" class="form-control" id="pass" value=""/>
                </div>
                <button type="submit" class="btn btn-primary">
                    Сохранить
                </button>
            </form>
        </div>
    </div>
@endsection