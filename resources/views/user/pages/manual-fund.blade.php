@extends('user.layouts.app')
@section('title')
    @lang('Manual Fund')
@endsection
@section('content')

    <div class="container p-5">

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



        <div class="row mt-5">


            <div class="col text-center">


                <p class="text-small text-center mb-2">Pay<br>
                    NGN{{ number_format($amount, 2) }}</p>



                <div
                    class="text-blue-500 text-center  mt-1 mb-4 text-sm justify-center items-stretch bg-blue-500 bg-opacity-20 self-stretch mt-2 px-6 py-3 rounded-xl">
                    Add funds to wallet by sending to the<br> account details below.<br>
                    <p class="text-danger mt-3">Manual Funding is only availabe from 10am - 10pm</p>
                </div>






            </div>



            <div class="col-lg-5 col-sm-12">
                <div class="card border-0 shadow-lg text-center p-3 mb-5 bg-body rounded-40">
                    <div class="card-body">
                        <div class="">

                            <strong text-black class="my-2">Bank Name</strong><br>
                            <p class="mb-2 text-small">{{ $account_details->bank_name }}</p>

                            <hr>

                            <strong text-black class="my-2">Account Name</strong><br>
                            <p class="mb-2 text-small">{{ $account_details->account_name }}</p>

                            <hr>

                            <strong text-black class="my-2">Account Number</strong><br>
                            <p text-small>{{ $account_details->bank_account }}</p>


                        </div>

                    </div>


                </div>
            </div>




            <div class="col-lg-7 col-sm-12">
                <div class="card border-0 shadow-lg p-3 my-2 bg-body rounded-40">

                    <div class="card-body">


                        <div class="">

                            <form id="myDiv" action="fund-manual-now" enctype="multipart/form-data" text-center class="grid grid-cols-1"
                                  gap-3 method="POST">
                                @csrf

                                <label class="my-2 text-gray-600 text-center font-bold gap-3 text-sm">Upload Payment prove</label>

                                <div class="flex mt-2">

                                    <input type="file"
                                           class="form-control"
                                           id="amount" type="number" max="999999" min="5" name="receipt"
                                           required>
                                </div>

                                <input name="amount" hidden value="{{ $amount }}">

                                <label class="my-3">Name on Account</label>
                                <input name="name" class="form-control" required value="">


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


@endsection
