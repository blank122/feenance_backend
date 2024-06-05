@extends('themes.layouts.main')
@section('content')
    {{-- Content Header) --}}
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    @if($mode == 'active')
                        <h1 class="m-0">Active Users</h1>
                    @else
                        <h1 class="m-0">Inactive Users</h1>
                    @endif
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ action('App\Http\Controllers\MainController@home') }}">Home</a></li>
                        <li class="breadcrumb-item">Users</li>
                        @if($mode == 'active')
                            <li class="breadcrumb-item active">Active Users</li>
                        @else
                            <li class="breadcrumb-item active">Inactive Users</li>
                        @endif
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content  --}}
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-secondary mb-3" data-toggle="modal" data-target="#newUserModal"><span class="fa fa-plus"></span> New User</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                                <th width="70px">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        {{ $user->usr_last_name }}, {{ $user->usr_first_name }} {{ $user->usr_middle_name }}
                                        <br/>
                                        <small>{{ $user->usr_email }}</small>
                                        <br/>
                                        <em><small>Last login: {{ getLastLogin($user->usr_id)  }}</small></em>
                                    </td>
                                    <td>
                                        <a class="btn btn-info btn-sm" href="javascrip:void(0)" data-toggle="modal" data-target="#resetModal{{$user->usr_uuid}}"><span class="fa fa-key"></span> Reset</a>
                                    </td>
                                    <td>
                                        @if($user->usr_active == '1')
                                            <a href="{{ action('App\Http\Controllers\UserController@deactivate',[$user->usr_uuid]) }}"><span class="fa fa-toggle-on"></span></a>
                                        @else
                                            <a href="{{ action('App\Http\Controllers\UserController@activate',[$user->usr_uuid]) }}"><span class="fa fa-toggle-off"></span></a>
                                        @endif
                                    </td>
                                </tr>
                                <div class="modal fade" id="resetModal{{$user->usr_uuid}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Please Confirm</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to reset this user's password?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <a class="btn btn-primary" href="{{ action('App\Http\Controllers\UserController@reset',[$user->usr_uuid]) }}">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    {{-- Modals  --}}
    <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newUserModalLabel">Create New User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ action('App\Http\Controllers\UserController@register') }}">
                {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="usr_last_name">Last Name <span style="color:red;">*</span></label>
                            <input class="form-control" type="text" name="usr_last_name" id="usr_last_name" placeholder="Last Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="usr_first_name">First Name <span style="color:red;">*</span></label>
                            <input class="form-control" type="text" name="usr_first_name" id="usr_first_name" placeholder="First Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="usr_middle_name">Middle Name</label>
                            <input class="form-control" type="text" name="usr_middle_name" id="usr_middle_name" placeholder="Middle Name" />
                        </div>
                        <div class="form-group">
                            <label for="usr_mobile">Mobile No. <span style="color:red;">*</span><small>(Format: 09101234567)</small></label>
                            <input class="form-control" type="number" min="00000000000" max="09999999999" name="usr_mobile" id="usr_mobile" placeholder="Mobile Number" required/>
                        </div>
                        <div class="form-group">
                            <label for="usr_email">E-Mail (username) <span style="color:red;">*</span></label>
                            <input class="form-control" type="email" name="usr_email" id="usr_email" placeholder="E-Mail" required/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><span class="fa fa-save"></span> Save user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
