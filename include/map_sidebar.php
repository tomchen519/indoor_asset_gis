<?php
// Written by Tom Chen
include('./include/asset_map_modals.php');
include('./include/database.php');

function populateDpMenuContent($array, $menuID) {
  foreach ($array as $i) {
    if ($i[0] == $menuID) {
      if (!$i[1]) {
        echo "<option value='None'>None</option>";
      } else {
        echo "<option value='".strtolower(str_replace(" ", "_", $i[1]))."'>".ucwords($i[1])."</option>";
      }
    }
  }
}


$assetFields = getDistinctAssetValues();?>
<!-- sidebar container -->
<div id="sidebar" class="sidebar sidebar-left collapsed">
  <!-- sidebar tabs -->
  <div class="sidebar-tabs">
    <ul role="tablist" id="sidebar_icons">
      <li id="mainTab" data-toggle="tooltip" title="User Profile"><a href="#profile" role="tab"><span class="fa fa-user-o fa-lg"></span></a></li>
      <li id="queryTab" data-toggle="tooltip" title="Feature Query"><a href="#query" role="tab"><span class="fa fa-search fa-lg"></span></a></li>
      <li id="assetsTab" data-toggle="tooltip" title="Assets"><a href="#assets_list" role="tab"><span class="fa fa-list-ul fa-lg"></span></a></li>
      <li id="editTab" data-toggle="tooltip" title="Feature Editor"><a href="#editor" role="tab"><span class="fa fa-pencil-square-o fa-lg"></span></a></li>
    </ul>
    <!-- admin authenticated components -->
    <?php
    if ($userRole == 'admin') {
      echo "<ul>" . "<li id='dataTab' data-toggle='tooltip' title='Data Management'><a href='#data_manage' role='tab'><span class='fa fa-database fa-lg'></span></a></li>" .
      "<li id='usersTab' data-toggle='tooltip' title='User Management'><a href='#user_manage' role='tab'><span class='fa fa-address-book fa-lg'></span></a></li>" . "</ul>";}
    ?>
  </div>
  <!-- sidebar content -->
  <div class="sidebar-content">
    <!-- PROFILE -->
    <div class="sidebar-pane" id="profile">
      <div>
        <h1 class="sidebar-header">User Profile<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>
        <h5><strong>User Name: <?php echo $userName?></strong></h5>
        <h5><strong>User Role: <?php echo $userRole?></strong></h5>
        <button type="button" id="updateProfileBtn" class="btn btn-info" data-toggle="modal" data-target="#updateProfileModal"><span class="fa fa-user-circle" aria-hidden="true"></span> Update Profile</button>
        <form action='./logout.php' method="post">
          <button type="submit" class="btn btn-danger" name="logout" id="logout"><span class="glyphicon glyphicon-off" aria-hidden="true"></span> Log Out</button>
        </form>
      </div>
    </div>

    <!-- QUERY -->
    <div class="sidebar-pane" id="query">
      <div>
        <h1 class="sidebar-header">Feature Query<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>
        <form id="query_feature_form" autocomplete="off">
        <!-- admin authenticated components -->
          <div class="form-group editing_form">

            <!-- UNIQUE ID -->
            <label for="query_uid">Unique ID</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>"><input type="text" id="query_uid" class="query form-control" name="uid" placeholder="Unique ID"></span>
            <!-- ASSET TYPE -->
            <label for="query_asset_type">Asset Type</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>">
              <select id="query_asset_type" class="query asset_type_menu form-control select2-single">
                <option value="none" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "asset_type");
                ?>
              </select>
            </span>
            <!-- SERIAL NUMBER -->
            <label for="query_serial_number">Serial Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>"><input type="text" id="query_serial_number" class="query form-control" name="serial_number" placeholder="Serial Number"></span>
            <!-- BUILDING NAME -->
            <label for="query_bldg_name">Building Name</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>">
              <select id="query_bldg_name" class="query bldg_name_menu form-control select2-single">
                <option value="none" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "bldg_name");
                ?>
              </select>
            </span>
            <!-- FLOOR NUMBER -->
            <label for="query_floor">Floor Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>"><input type="text" id="query_floor" class="query form-control" name="floor" placeholder="Floor; e.g. 01" disabled></span>
            <!-- ROOM NUMBER -->
            <label for="query_bldg_room">Room Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable';} ?>"><input type="text" id="query_bldg_room" class="query form-control" name="bldg_room" placeholder="Building/Room; e.g. SH0107"></span>

            <!-- DATE ACQUIRED -->
            <label for="query_acquired_date">Date Acquired</label>
            <span class="query_date_form <?php if ($userRole == 'admin') { echo 'editable';} ?>">
              <input type="text" id="query_acquired_date_min" class="query query_date form-control datepicker" name="acquired_date_min" placeholder="From">
              <input type="text" id="query_acquired_date_max" class="query query_date form-control datepicker" name="acquired_date_max" placeholder="To">
            </span>
            <!-- DATE LAST SERVICED -->
            <label for="query_service_date">Date Last Serviced</label>
            <div class="query_date_form">
              <input type="text" class="query query_date form-control datepicker" id="query_service_date_min" name="service_date_min" placeholder="From">
              <input type="text" class="query query_date form-control datepicker" id="query_service_date_max" name="service_date_max" placeholder="To">
            </div>

            <!-- LAST SERVICED BY -->
            <label for="query_serviced_by">Serviced by</label>
            <span class="editable"><input type="text" id="query_serviced_by" class="query form-control" name="serviced_by" placeholder="Maintenance performed by ..."></span>
            <!-- STATUS -->
            <label for="query_status">Status</label>
            <span class="editable">
              <select id="query_status" class="query status_menu form-control select2-single">
                <option value="none" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "status");
                ?>
              </select>
            </span>

<!-- MAKE CLEAR QUERY BUTTON -->
          <div class="query_submit"><button type='button' class='query_btn btn btn-primary' data-toggle='tooltip' title='Query for assets'><span class="fa fa-search"></span>&nbsp;&nbsp;&nbsp;Submit Query</button></div>
          <!-- <div class="save_update_error update_error"></div> -->

          <div class="clear_query"><button type='button' class='clear_btn btn btn-primary' data-toggle='tooltip' title='Clear Query'><span class="fa fa-times"></span>&nbsp;&nbsp;&nbsp;Clear Query</button></div>

        </div>
        </form>
      </div>
    </div>

    <!-- ASSETS LIST -->
    <div class="sidebar-pane" id="assets_list">
      <div>
        <h1 class="sidebar-header">Assets<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>
        <div class="selected_room_number"></div>
        <!-- ADD FEATURE BUTTON -->
        <?php
          if ($userRole == 'admin') {
            echo '<button type="button" id = "add_asset_btn" class="btn btn-primary" data-toggle="tooltip" title="Click to add new asset to selected room"><span class="fa fa-plus"></span>  Add New Asset</button>';
            }
        ?>
        <!-- CREATE CATEGORIES DYNAMICALLY -->
        <div class="asset_categories">
        </div>
      </div>
    </div>

    <!-- EDITOR -->
    <div class="sidebar-pane" id="editor">
      <div>
        <h1 class="sidebar-header">Feature Editor<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>
        <form id="edit_asset_form" autocomplete="off">
        <!-- admin authenticated components -->
          <div class="form-group editing_form">
            <!-- FEATURE ID -->
            <label for="fid">Feature ID (Database Use)</label>
            <input type="text" id="fid" class="form-control" name="fid" placeholder="Feature ID" readonly>
            <!-- UNIQUE ID -->
            <label for="uid">Unique ID</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>"><input type="text" id="uid" class="edit_data form-control" name="uid" placeholder="Unique ID" readonly></span>
            <!-- ASSET TYPE -->
            <label for="asset_type">Asset Type</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>">
              <select id="asset_type" class="asset_type_menu edit_data form-control select2-single" disabled>
                <option value="none" disabled="disabled" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "asset_type");
                ?>
              </select>
            </span>
            <!-- SERIAL NUMBER -->
            <label for="serial_number">Serial Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>"><input type="text" id="serial_number" class="edit_data form-control" name="serial_number" placeholder="Serial Number" required="true" readonly></span>
            <!-- BUILDING NAME -->
            <label for="bldg_name">Building Name</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>">
              <select id="bldg_name" class="bldg_name_menu edit_data form-control select2-single" disabled>
                <option value="none" disabled="disabled" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "bldg_name");
                ?>
              </select>
            </span>
            <!-- FLOOR NUMBER -->
            <label for="floor">Floor Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>"><input type="text" id="floor" class="edit_data form-control" name="floor" placeholder="Floor; e.g. 01" required readonly></span>
            <!-- ROOM NUMBER -->
            <label for="bldg_room">Room Number</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>"><input type="text" id="bldg_room" class="edit_data form-control" name="bldg_room" placeholder="Building/Room; e.g. SH0107" required readonly></span>
            <!-- DATE ACQUIRED -->
            <label for="acquired_date">Date Acquired</label>
            <span class="<?php if ($userRole == 'admin') { echo 'editable editor';} ?>"><input type="text" id="acquired_date" class="edit_data form-control datepicker" name="acquired_date" placeholder="Date of equipment acquisition" readonly></span>
            <!-- DATE LAST SERVICED -->
            <label for="service_date">Date Last Serviced</label>
            <input type="text" class="form-control" id="service_date" name="service_date" placeholder="Data of last maintenance" readonly>
            <!-- LAST SERVICED BY -->
            <label for="serviced_by">Serviced by</label>
            <span class="editable editor"><input type="text" id="serviced_by" class="edit_data form-control" name="serviced_by" placeholder="Maintenance performed by ..." readonly></span>
            <!-- STATUS -->
            <label for="status">Status</label>
            <span class="editable editor">
              <select id="status" class="status_menu edit_data form-control select2-single" disabled>
                <option value="none" disabled="disabled" selected="selected">Select...</option>
                <?php
                  $result = populateDpMenuContent($assetFields, "status");
                ?>
              </select>
            </span>
            <!-- RECORD ADDED -->
            <label for="row_added">Record Added</label>
            <input type="text" class="form-control" id="row_added" name="row_added" placeholder="Date the record was added" readonly>
            <!-- RECORD LAST MODIFIED -->
            <label for="row_modified">Record Last Modified</label>
            <input type="text" class="form-control" id="row_modified" name="row_modified" placeholder="Date the record was last modified" readonly>
            <!-- NOTES -->
            <label for="notes">Notes</label>
            <span class="editable editor"><textarea id="notes" class="edit_data form-control" name="notes" rows="2" placeholder="Notes..." readonly></textarea></span>
          </div>
          <div class="checkbox">
            <label><div><input type="checkbox" id="checkbox" value="" disabled></div>Routine Maintenance Performed (No Updates)</label>
          </div>
          <div>
          <div class="save_edits"><button type='button' class='save_edits_btn btn btn-primary edt_btn' data-toggle='tooltip' title='Save edits to asset' disabled><span class="fa fa-floppy-o"></span>&nbsp;&nbsp;&nbsp;Save Edits</button></div><div class="save_update_error update_error"></div>
        </div>
        </form>
      </div>
    </div>

    <?php
    // admin authenticated components
    if ($userRole == 'admin') {
      $preset_categories = ["asset_type", "bldg_name", "status"];
      echo "<!-- DATA MANAGEMENT -->";
      echo "<div class='sidebar-pane' id='data_manage'>
      <div>
        <h1 class='sidebar-header'>Data Management<span class='sidebar-close'><i class='fa fa-caret-left'></i></span></h1>
        <div class='form-group editing_form'>";
      foreach ($preset_categories as $category_title) {
        echo "<label for='add_" . $category_title . "'>" . ucwords(str_replace("_", " ", $category_title)) . "</label>
          <select id='edit_" . $category_title . "_menu' class='" . $category_title . "_menu edit_data form-control select2-single'>
            <option value='none' disabled='disabled' selected='selected'>Select...</option>";
              $result = populateDpMenuContent($assetFields, $category_title);
          echo "</select>" . "<button type='button' class='new_category_btn btn btn-xs btn-primary' data-toggle='tooltip' title='Add new category' id='add_" . $category_title . "'><span class='fa fa-plus'></span></button>" . "

          <button type='button' class='edit_category_btn btn btn-xs btn-danger' data-toggle='tooltip' title='Edit selected value' id='edit_" . $category_title . "' disabled><span class='fa fa-pencil-square-o'></span></button>" . "

          <div class='add_" . $category_title . " edit_" . $category_title ."'></div>";
      }

      echo "</div>

      </div>
      </div>";
      echo "<!-- USER MANAGEMENT -->";
      echo "<div class='sidebar-pane' id='user_manage'>
      <div>
        <h1 class='sidebar-header'>User Management<span class='sidebar-close'><i class='fa fa-caret-left'></i></span></h1>
        <div><button type='button' class='list_user_btn btn btn-primary' data-toggle='tooltip' title='List all users'><span class='fa fa-address-card'></span></button> List all users</div>
        <div><button type='button' class='new_user_btn btn btn-primary' data-toggle='tooltip' title='Add new user'><span class='fa fa-user-plus'></span></button> Add new user</div>
        <div class='user_list'>
        </div>
      </div>
      </div>"
    ;}
    ?>

  </div>
</div>
<!-- map container -->
<div id="map" class="sidebar-map"></div>
