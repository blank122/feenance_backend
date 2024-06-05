<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Personal Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ action('App\Http\Controllers\UserController@update') }}">
            {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usr_email">E-Mail *</label>
                        <input class="form-control" type="email" name="usr_email" id="usr_email" placeholder="E-mail" value="{{ session('usr_email') }}" required/>
                    </div>
                    <div class="form-group">
                        <label for="usr_last_name">Last Name *</label>
                        <input class="form-control" type="text" name="usr_last_name" id="usr_last_name" placeholder="Last Name" value="{{ session('usr_last_name') }}" required/>
                    </div>
                    <div class="form-group">
                        <label for="usr_first_name">First Name *</label>
                        <input class="form-control" type="text" name="usr_first_name" id="usr_first_name" placeholder="First Name" value="{{ session('usr_first_name') }}" required/>
                    </div>
                    <div class="form-group">
                        <label for="usr_middle_name">Middle Name</label>
                        <input class="form-control" type="text" name="usr_middle_name" id="usr_middle_name" placeholder="Middle Name" value="{{ session('usr_middle_name') }}" />
                    </div>
                    <div class="form-group">
                        <label for="usr_birth_date">Birth Date *</label>
                        <input class="form-control" type="date" name="usr_birth_date" id="usr_birth_date" value="{{ session('usr_birth_date') }}" required/>
                    </div>
                    <div class="col-12 mb-3">
                        {{-- {!! NoCaptcha::renderJs() !!} --}}
                        {{-- {!! NoCaptcha::display() !!} --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="usr_uuid" value="{{ session('usr_uuid') }}" />
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userPasswordModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ action('App\Http\Controllers\UserController@updatePassword2') }}">
            {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input class="form-control" type="password" name="current_password" id="current_password" placeholder="Current Password" required/>
                    </div>
                    <hr/>
                    <div class="form-group">
                        <label for="new_password1">New Password</label>
                        <input class="form-control" type="password" name="new_password1" id="new_password1" placeholder="New Password" required/>
                    </div>
                    <div class="form-group">
                        <label for="new_password2">Retype Password</label>
                        <input class="form-control" type="password" name="new_password2" id="new_password2" placeholder="Retytpe Password" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update password</button>
                </div>
            </form>
        </div>
    </div>
</div>
