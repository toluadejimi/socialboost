
@extends('user.layouts.app')
@section('title')
    @lang('Resolve')
@endsection
@section('content')


<div class="container p-5">



    <div class="text-center">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif
    </div>



    <div
        class="text-blue-500 text-center  mt-5 text-sm justify-center items-stretch bg-blue-500 bg-opacity-20 self-stretch mt-2 px-6 py-3 rounded-xl">
        Your payment has been submitted<br>Your funds will be added in less than 10 minutes
    </div>



    <div class="text-center">

        <img src="{{url('')}}/assets/success.gif" alt="image">

    </div>



    <div class="text-center">

        <a href="/user/dashboard" class="text-center text-white btn btn-block btn-dark my-4">
            Go to Dashboard
        </a>


    </div>





</div>

@endsection
