@extends('user.layouts.app')
@section('title')
    @lang('Add Fund')
@endsection
@section('content')

    <div class="container">
        <ol class="breadcrumb center-items">
            <li><a href="{{route('user.home')}}">@lang('Home')</a></li>
            <li class="active">@lang('Add Fund')</li>
        </ol>
        <div class="row my-3">

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




                    <div class="col-lg-5 col-sm-12">
                        <div class="card border-0 shadow-lg p-3 mb-5 bg-body rounded-40">
                            <div class="card-body">
                                <div class="">

                                    <form action="{{route('user.fundMoney')}}" method="POST">
                                        @csrf

                                        <label class="my-2">Enter the Amount (NGN)</label>
                                        <input type="text" name="amount" class="form-control" max="999999" min="5" name="amount"
                                               placeholder="Enter the Amount you want Add" required >


                                        <label class="my-2 mt-4">Select Payment mode</label>
                                        <select name="type" class="form-control">
                                            <option value="1">Instant</option>

                                        </select>


                                        <button type="submit" class="text-white btn btn-block btn-dark my-4">
                                            Add Funds
                                        </button>
                                    </form>




                                </div>

                            </div>


                        </div>
                    </div>




                    <div class="col-lg-7 col-sm-12">
                        <div class="card border-0 shadow-lg p-3 mb-5 bg-body rounded-40">

                            <div class="card-body">


                                <div class="">

                                    <div class="p-2 col-lg-6">
                                        <strong>
                                            <h4>Latest Transactions</h4>
                                        </strong>
                                    </div>

                                    <div>


                                        <div class="table-responsive ">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>


                                                </tr>
                                                </thead>
                                                <tbody>


                                                @forelse($transaction as $data)
                                                    <tr>
                                                        <td style="font-size: 12px;">{{ $data->id }}</td>


                                                        <td style="font-size: 12px;">₦{{ number_format($data->amount, 2) }}


                                                        <td>
                                                            @if ($data->status == 1)
                                                                <span style="background: orange; border:0px; font-size: 10px"
                                                                      class="btn text-white btn-warning btn-sm">Pending</span>
                                                                <a href="resolve-page?trx_ref={{ $data->ref_id }}"
                                                                   style="background: rgb(168, 0, 14); border:0px; font-size: 10px"
                                                                   class="btn text-white btn-warning btn-sm">Reslove</span>
                                                                    @elseif ($data->status == 2)
                                                                        <span style="font-size: 10px;"
                                                                              class="text-white btn btn-success btn-sm">Completed</span>
                                                            @else
                                                            @endif

                                                        </td>



                                                    </tr>

                                                @empty

                                                    <h6>No transaction found</h6>
                                                @endforelse

                                                </tbody>

                                                {{ $transaction->links() }}

                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>


                        </div>


                    </div>
                </div>
            </div>




        </div>

    </div>




    <div id="signup-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header modal-colored-header bg-primary">
                    <h4 class="modal-title method-name" id="primary-header-modalLabel"></h4>

                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">×
                    </button>
                </div>


                <div class="modal-body">

                    <div class="payment-form">
                        <p class="text-danger depositLimit"></p>
                        <p class="text-danger depositCharge"></p>
                        <input type="hidden" class="form-control gateway" name="gateway" value="">
                        <div class="form-group">
                            <label>@lang('Amount')</label>
                            <div class="input-group">
                                <input type="text" class="form-control amount" name="amount" value="">
                                <div class="input-group-append">
                                    <span class="input-group-text show-currency"></span>
                                </div>
                            </div>
                            <pre class="text-danger errors"></pre>
                        </div>
                    </div>
                    <div class="payment-info text-center">
                        <img id="loading" src="{{asset('assets/images/loading.gif')}}" alt=""/>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary checkCalc">@lang('Check')</button>
                </div>

            </div>
        </div>
    </div>


@endsection

