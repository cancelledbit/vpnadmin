@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <td class="card">
                <table class="table">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Login</th>
                        <th scope="col">Type</th>
                        <th scope="col">Password</th>
                        <th scope="col">ip</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Comment</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($vpn as $entry)
                        @if ($entry->fullname=='Свободно')
                           <tr class="table-success">
                        @else
                            <tr class="table-danger">
                        @endif
                                <td>{{$entry->id}}</td>
                                <td>{{$entry->username}}</td>
                                <td>{{$entry->server}}</td>
                                <td>{{$entry->password}}</td>
                                <td>{{$entry->ip}}</td>
                                <td>{{$entry->fullname}}</td>
                                <td>{{$entry->comment}}</td>
                                <td><a href="edit/{{$entry->id}}">Изменить</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="/edit"> Новый ВПН  </a>
            </div>
        </div>
    </div>
</div>
@endsection
