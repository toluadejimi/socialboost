@extends('user.layouts.app')
@section('title')
    @lang('Resolve')
@endsection
@section('content')

    <div class="container p-5">



        <div class="text-center mt-3">
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


        <div class="row mt-5">




            <div class="text-stone-900 text-center text-xl mb-3 font-bold  whitespace-nowrap mt-10">
                Resolve Deposit
            </div>









            <div class="col-lg-5 col-sm-12">
                <div class="card border-0 shadow-lg p-3 mb-5 bg-body rounded-40">
                    <div class="card-body">
                        <div class="">

                            <form action="/user/resolve-now" method="POST">
                                @csrf
                                <p style="font-size: 12px" class="mb-4"> Resolve pending transactions by using your bank session ID / Refrence No on your
                                    transaction recepit
                                <p>

                                    <strong ><h6 class="mb-3">{{ $ref }}</h6></strong>

                                    <label class="my-2">Enter Session ID</label>
                                    <input class="form-control" name="session_id" required autofocus>
                                    <input hidden value="{{ $ref }}" name="trx_ref">


                                    <button type="submit" class="text-white btn btn-block btn-dark my-4">
                                        Add Funds
                                    </button>
                            </form>
                        </div>



                    </div>

                </div>


            </div>
        </div>





    </div>








    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex flex-wrap justify-content-center px-0">
            <div class="card-body p-5">

    </ul>






    </div>

@endsection
