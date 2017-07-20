<!-- Update Profile Modal -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" role="dialog" aria-labelledby="updateProfileModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Update Profile</h4>
      </div>
      <div class="modal-body">
        <form name="update_profile" autocomplete="off" method="post">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" id="username" placeholder="<?php echo (isset($userName))?$userName:'e.g. Joe Smith' ?>" readonly>
          </div>
          <div class="form-group">
	    <label for="current_password">Current Password: <span id='currentPwdError' style="color:#0066cc"> Please input current password to continue</span></label>
            <input type="password" class="form-control" id="current_password" placeholder="Current Password">
          </div>
          <button type="button" id="check_password" class="btn btn-info">Submit</button>
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" inactive>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <div class="update_error"><h5><strong><span id="update_error"></span></strong></h5></div>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" id="update_profile" class="btn btn-danger">Save changes</button>
      </div>
    </div>
  </div>
</div>

