@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul>
                    @foreach($menus as $entry)
                    <li class="list-item">
                        <a href="{{$entry['href']}}">{{$entry['name']}}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection