@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <td class="card">
                <table class="table">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        @if ($user['role'] === 'admin')
                            <tr class="table-success">
                        @else
                            <tr class="table-danger">
                                @endif
                                <td>{{$user['id']}}</td>
                                <td>{{$user['name']}}</td>
                                <td>{{$user['email']}}</td>
                                <td>{{$user['role']}}</td>
                                <td><a href="/admin/users/edit/{{$user['id']}}">Изменить</a></td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
        </div>
    </div>
@endsection