// Written by Tom Chen

// ****Declare Map parameters and global variables
var centerlatlng = L.latLng(34.2386101923809, -118.53070557117464);
var main_southWest = L.latLng(34.24066114560192, -118.53679955005647),
    main_northEast = L.latLng(34.23649714231573, -118.52336168289186),
    main_bounds = L.latLngBounds(main_southWest, main_northEast);
var layerControl, zoom, map, selected_room, authenticated=false, currentFloorView, room_in_focus, new_edits = {}, dropdown_selection = {}, validated_array, queried_room_featureGroup;
if ($(window).width() < 800) {
  zoom = 18;
} else {
  zoom = 19;
}

// ****Map layer styles
var floorDefaultStyle = {
  color: '#4F4F4F', weight: 1, fillColor: '#7F7F7F', fillOpacity: 0.2
};

var floorHighlightStyle = {
  color: '#3399ff', weight: 2, fillColor: '#3399ff', fillOpacity: 0.5
};

var floorSelectedStyle = {
  color: '#8e0e0e', weight: 2, fillColor: '#d31313', fillOpacity: 0.5
};

// ****Functions to manipulate DOMs for editing sessions
function enableUpdateFeature() {
  $(".editable.editor").find("input[type=text], textarea").prop({"readonly": false});
  $(".editable.editor > select*").prop({"disabled": false});
  $(".save_edits_btn").prop({"disabled": false});
}

function disableUpdateFeature() {
  $(".editable.editor").find("input[type=text], textarea").prop({"readonly": true});
  $(".editable.editor > select*").prop({"disabled": true});
}

function properCase(str)
{
 return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

// ****Set timeout function to automatically log out user after 40 minutes of inactivity
function setTimeOut() {
  var minutes = 30;
  var idleTimeWarning = 1000 * 60 * minutes;
  var sessionTimeOut = 1000 * 60 * (minutes-20);

  idleTimer = null;
  sessionTimer = null;
  idleTimer = setTimeout(function() {
    var timeOutWarning = $(document.createElement('div'));
    $(timeOutWarning).html("Page has been idle for " + minutes + " minutes and will log you out in " + (minutes - 20) + " minutes");
    $(timeOutWarning).dialog({title: "Idle warning"});
    sessionTimer = setTimeout(function() {
      location.href = "logout.php";
    }, sessionTimeOut);
  }, idleTimeWarning);
}

$(document).ready(function() {
  $('.tooltip').tooltip();

// ****Call timeOut function
  setTimeOut();

// ****Attach event listener to map and sidebar to detect activity
  $("#map, #sidebar").on('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function() {
    clearTimeout(sessionTimer);
    clearTimeout(idleTimer);
    setTimeOut();
  });

// ****Function declartions

  // ****Retrive all layers from GeoServer
  function fetchFeatureLayers () {
    map.spin(true, {lines:9, length:40, width:24,radius:60});
    requests = {};
    requests["request"] = "fetch_data_layers";
    $.ajax({
      url: "./include/geoserver_requests.php",
      type: "post",
      data: requests,
      success: function(data) {
        data = $.parseJSON(data);
        var features = data['featureTypes']['featureType'];
        var layer_array = [];
        features.forEach(function(feature) {
          layer_array.push(feature['name']);
        })
        layer_array.sort();
        layer_array.forEach(function(feature) {
          // ****Separate floor layers from asset layer and added to basemap layers
          if (/floor/.test(feature)) {
            window[feature] = L.geoJSON(false, {onEachFeature: onEachFloorFeature});
            layerControl.addBaseLayer(window[feature], String(feature).upperFirstChar());
            fetchFloorFeatures(feature);
            // ****Floor01 is displayed by default
            if (feature=='floor01') {
              window[feature].addTo(map);
            }
          }
        });
        map.spin(false);
      }
    });
  }

  // ****Retrive floor features from GeoServer
  function fetchFloorFeatures (floorNumber) {
    map.spin(true, {lines:9, length:40, width:24,radius:60});
    requests = {};
    requests["request"] = "fetch_floor_layers";
    requests["floor"] = floorNumber;
    $.ajax({
      url: "./include/geoserver_requests.php",
      type: "post",
      data: requests,
      dataType: 'json',
      success: function(data) {
        window[floorNumber].addData(data).setStyle(floorDefaultStyle);
        map.spin(false);
      }
    });
  }

  // ****Add event listeners, tooltip, and styling to floor features
  function onEachFloorFeature (feature, layer) {
    if (layer.feature.properties.bldg_abbr_ != null) {
      layer.bindTooltip(layer.feature.properties.bldg_abbr_, {sticky:true, opacity:0.7, direction:'bottom', offset: [0, 10]});
    }
    layer.on('mouseover', function(e) {
      layer.setStyle(floorHighlightStyle);
    });
    layer.on('mouseout', function(e) {
      layer.setStyle(floorDefaultStyle);
    });
    layer.on('click', selectRoom);
    layer.on('click', function(e) {
      map.panTo(e.latlng, {duration: 0.10});
    });
  }

  function selectRoom (room=null) {
    cleanMap();
    $(".asset_categories").html("");
    var room = this.feature;
    room_in_focus = this.feature.properties.bldg_abbr_;
    if (this.feature.properties.fac_name!=null) {
      bldg_in_focus = this.feature.properties.fac_name.replace(/\s/g, "_").toLowerCase();
    } else {
      bldg_in_focus = null;
    }
    if (bldg_in_focus != null && room_in_focus != null) {
      selected_room = L.geoJSON(room, {
        style: floorSelectedStyle,
        onEachFeature: function(feature, layer){
          layer.bindTooltip(layer.feature.properties.bldg_abbr_, {sticky:true, opacity:0.7, direction:'bottom', offset: [0, 10]});
        }
      }).addTo(map);
      fetchAssetFeatures(room_in_focus);
    } else {
      var roomError = $(document.createElement('div'));
      $(roomError).html("Room can not be used");
      $(roomError).dialog({title: "Room Selection Error"});
    }
  }

// FUNCTION TO PASS CLICKED AND QUERIED ROOM TO FETCH ASSETS
  function selectQueriedRoom (room, querySTR) {
    $(".asset_categories").html("");
    var room_prop = room.properties;
    room_in_focus = room_prop.bldg_abbr_;
    if (room_prop.fac_name!=null) {
      bldg_in_focus = room_prop.fac_name.replace(/\s/g, "_").toLowerCase();
    } else {
      bldg_in_focus = null;
    }
    fetchAssetFeatures(room_in_focus, querySTR);
  }

  function queryRooms (queryArray) {
    cleanMap();
    $(".asset_categories").html("");
    querySTRArray = [];
    querySTRArray.push("floor='" + currentFloorView + "'");
    for (var query_field in queryArray) {
      console.log(query_field + ": " + queryArray[query_field]);
      if (query_field == "query_acquired_date_max") {
        querySTRArray.push("acquired_date<='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_acquired_date_min") {
        querySTRArray.push("acquired_date>='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_asset_type") {
        querySTRArray.push("asset_type='" + queryArray[query_field].replace(/_/g," ") + "'");
      }
      if (query_field == "query_bldg_name") {
        querySTRArray.push("bldg_name='" + queryArray[query_field].replace(/_/g," ") + "'");
      }
      if (query_field == "query_bldg_room") {
        querySTRArray.push("bldg_room='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_serial_number") {
        querySTRArray.push("serial_number='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_serviced_by") {
        querySTRArray.push("serviced_by='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_status") {
        querySTRArray.push("status='" + queryArray[query_field].replace(/_/g," ") + "'");
      }
      if (query_field == "query_uid") {
        querySTRArray.push("uid='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_service_date_max") {
        querySTRArray.push("service_date<='" + queryArray[query_field] + "'");
      }
      if (query_field == "query_service_date_min") {
        querySTRArray.push("service_date>='" + queryArray[query_field] + "'");
      }
    }
    var querySTR = querySTRArray.join(" AND ");
    queryURL = querySTR;
    queryURL = encodeURI(queryURL);
    requests = {};
    requests["request"] = "query_rooms";
    requests["url"] = queryURL;
    $.ajax({
      url: "./include/geoserver_requests.php",
      type: "post",
      data: requests,
      dataType: 'json',
      success: function(data) {
        queried_feature = data.features;
        queried_room = [];
        for (var i = 0; i < queried_feature.length; i++) {
          if (queried_room.indexOf(queried_feature[i].properties.bldg_room)==-1) {
            queried_room.push(queried_feature[i].properties.bldg_room);
            fetchSelectedRoomFeatures(queried_feature[i].properties.bldg_room, currentFloorView, querySTR);
          }
        }
      }
    });
  }

  function fetchSelectedRoomFeatures (room, floor, querySTR) {
    map.spin(true, {lines:9, length:40, width:24,radius:60});
    queried_room_featureGroup = L.geoJson(null, {style: floorSelectedStyle,
      onEachFeature: function(feature, layer){
        layer.bindTooltip(layer.feature.properties.bldg_abbr_, {sticky:true, opacity:0.7, direction:'bottom', offset: [0, 10]});
        layer.on('mouseover', function(e) {
          layer.setStyle(floorHighlightStyle);
        });
        layer.on('mouseout', function(e) {
          layer.setStyle(floorSelectedStyle);
        });
        layer.on('click', function(e) {
          map.panTo(e.latlng, {duration: 0.10});
          console.log(room);
          console.log(floor);
          var room_feature = e.target.feature;
          selectQueriedRoom(room_feature, querySTR);
        });
      }
    }).addTo(map);

    requests = {};
    requests["request"] = "fetch_queried_rooms";
    requests["floor"] = "floor" + floor;
    requests["room"] = "bldg_abbr_=%27" + room + "%27";
    $.ajax({
      url: "./include/geoserver_requests.php",
      type: "post",
      data: requests,
      dataType: 'json',
      success: function(data) {
        queried_room_featureGroup.addData(data.features[0]);
        map.spin(false);
      }
    });
  }

  // ****Retrive asset features from GeoServer
  function fetchAssetFeatures (room, QueryURL=null) {
    var url = "bldg_room='" + room + "'";
    if (QueryURL!=null) {
      url += " AND ";
      url += QueryURL;
      url = encodeURI(url);
    }
    listAssets(url, room);
  }

  // ****List all assets for selected room
  function listAssets(url, room) {
    $(".selected_room_number").html("Selected Room: " + room);
    requests = {};
    requests["request"] = "get_assets_in_room";
    requests["url"] = url;
    $.ajax({
      url: "./include/geoserver_requests.php",
      type: "post",
      data: requests,
      dataType: 'json',
      success: function(data) {
        var feature_array = data.features;
        sidebar.open();
        $("#assetsTab").addClass("active");
        $("#assets_list").addClass("active");
        if (feature_array.length >= 1) {
          var asset_categories = [];
          assets_inRoom = {};
          for (var i = 0; i < feature_array.length; i++) {
            var asset_type = feature_array[i].properties.asset_type.replace(/\s/g, "_");
            var asset_id = feature_array[i].id;
            var asset_element_id = feature_array[i].id.replace(".","_");
            assets_inRoom[asset_id] = feature_array[i];
            var serial_number = feature_array[i].properties.serial_number;
            var status = feature_array[i].properties.status;
            var asset_properties = feature_array[i].properties;
            if (asset_categories.indexOf(asset_type) == -1) {
              asset_categories.push(asset_type);
              $(".asset_categories").append("<div class='category_header'>" + asset_type.toUpperCase().replace(/_/g, " ") + "</div><div class='" + asset_type + " asset_category'></div>");
            }

            var feature_btn = '<div class="edit_'+asset_element_id+'">' +
            '<button type="button" id = "' + asset_element_id + '_edit" class="asset_btn btn btn-info btn-xs" data-toggle="tooltip" title="Click to edit this asset"><span class="glyphicon glyphicon-edit"></span></button>';

            if (userRole == "admin") {
              feature_btn += '<button type="button" id = "' + asset_element_id + '_delete" class="asset_btn btn btn-danger btn-xs" data-toggle="tooltip" title="Click to delete this asset"><span class="fa fa-trash-o"></span></button>';
            }

            $('.'+asset_type).append(
              "<p><div id='" + asset_element_id + "'><span class='title'>Feature ID: </span><span class='value'>" + asset_id + "</span><br/>" +
              "<span class='title'>Serial Number: </span><span class='value'>" + serial_number + "</span><br/>" +
              "<span class='title'>Status: </span><span class='value'>" + status + "</span><br/>" + feature_btn +
              "</div></p>"
            );

            $("#"+asset_element_id + "_edit").click(function(e){
              var clickedAssetID = $(this).attr('id');
              clickedAssetID = clickedAssetID.replace("_edit","").replace("_",".");
              editAsset(assets_inRoom[clickedAssetID]);
            });

            $("#"+asset_element_id + "_delete").click(function(e){
              var clickedAssetID = $(this).attr('id');
              clickedAssetID = clickedAssetID.replace("_delete","").replace("_",".");
              var asset_to_delete = {};
              asset_to_delete["fid"] = clickedAssetID;
              asset_to_delete["action"] = "delete";
              $(".asset_categories").html("");
              updateAssetData(asset_to_delete)
            });
          }
        } else {
          $(".asset_categories").append("<h4>No assets found in this room</h4>");
        }
      }
    });
  }

  // ****Editing functions of Feature
  function editAsset(assetObj=null) {
    sidebar.open();
    $("#editTab").addClass("active");
    $("#editor").addClass("active");
    data = null;
    asset_data = null;
    data = assetObj;
    asset_data = assetObj;
    enableUpdateFeature();
    var dropMenuFields = ["asset_type", "bldg_name", "status"]
    if(data) {
      $("#checkbox").prop({"disabled": false});
      if (data.id) {
        $("#fid").val(data.id);
      } else {
        $("#fid").prop("placeholder","Feature ID");
      }
      if (data.properties.uid) {
        $("#uid").val(data.properties.uid);
      } else {
        $("#uid").prop("placeholder","Unique ID");
      }
      if (data.properties.serial_number) {
        $("#serial_number").val(data.properties.serial_number);
      } else {
        $("#serial_number").prop("placeholder","Serial Number");
      }
      if (data.properties.floor) {
        $("#floor").val(data.properties.floor);
      } else {
        $("#floor").prop("placeholder","Floor; e.g. 01");
      }
      if (data.properties.bldg_room) {
        $("#bldg_room").val(data.properties.bldg_room);
      } else {
        $("#bldg_room").prop("placeholder","Building/Room; e.g. SH0107");
      }
      if (!data.properties.acquired_date) {
        $("#acquired_date").val("");
        $("#acquired_date").prop("placeholder","Date of equipment acquisition");
      } else {
        var acqDate = data.properties.acquired_date.replace("Z","");
        $("#acquired_date").val(acqDate);
      }
      if (data.properties.service_date) {
        $("#service_date").prop("placeholder",new Date(data.properties.service_date));
      } else {
        $("#service_date").prop("placeholder","Data of last maintenance");
      }
      if (data.properties.serviced_by) {
        $("#serviced_by").val(data.properties.serviced_by);
      } else {
        $("#serviced_by").prop("placeholder","Maintenance performed by ...");
      }
      if (data.properties.row_added) {
        $("#row_added").prop("placeholder",new Date(data.properties.row_added));
      } else {
        $("#row_added").prop("placeholder","Date the record was added");
      }
      if (data.properties.row_modified) {
        $("#row_modified").prop("placeholder", new Date(data.properties.row_modified));
      } else {
        $("#row_modified").prop("placeholder","Date the record was last modified");
      }
      if (!data.properties.notes) {
        $("#notes").val("");
        $("#notes").prop("placeholder","Notes...");
      } else {
        $("#notes").val(decodeURI(data.properties.notes));
      }
      for (var field = 0; field < dropMenuFields.length; field++) {
        if (data.properties[dropMenuFields[field]]==null){
          var placeholder="None";
        } else {
          placeholder=data.properties[dropMenuFields[field]];
        }
        $("#"+dropMenuFields[field]).select2({
          placeholder: placeholder,
          width: "100%"
        });
        $("#"+dropMenuFields[field]).val(placeholder.replace(/\s/g,"_").toLowerCase()).trigger("change");
      }
    }
  }

  // ****Restore map view to default
  function cleanMap () {
    if (selected_room) {
      map.removeLayer(selected_room);
    }
    if (queried_room_featureGroup) {
      map.removeLayer(queried_room_featureGroup);
    }
    $("#checkbox").prop("checked", false);
  }

  // ****UPDATE ASSET DATA
  function updateAssetData(properties_array) {
    disableUpdateFeature();
    map.spin(true, {lines:9, length:40, width:24,radius:60});
    $.ajax({
      url:"./include/update_feature.php",
      type: "post",
      data: properties_array,
      dataType: 'text',
      success: function(response) {
        $(".asset_categories").html("");
        fetchAssetFeatures(room_in_focus);
        map.spin(false);
        var updateResult = $(document.createElement('div'));
        $(updateResult).html(response);
        $(updateResult).dialog({title: "Update Results"});
      }
    });
  }

  function editCategoryValue(category, actionStr, newValue, oldValue=null) {
    $('label[for="add_'+category+'"]').css({color:"black"});
    var category_menu = category + "_menu";
    if (newValue==null) {
      var input = "";
    } else {
      input = newValue.trim();
    }
    var display_input = input;
    var input = input.toLowerCase();
    if (/[^\w\s]/gi.test(input) || input=="" || input==null) {
      $('label[for="add_'+category+'"]').css({color:"red"});
    } else {
      if (oldValue) {
        var update_array = {manageData: category, category_action: actionStr, category_value: input, old_value: oldValue};
      } else {
        update_array = {manageData: category, category_action: actionStr, category_value: input};
      }
      $.ajax({
        url: "./include/database.php",
        type: "post",
        dataType: "json",
        data: update_array,
        success: function(data) {
          console.log(data);
          if (data.category_update==0) {
            $("."+actionStr+"_"+category).html("Error occurred");
          } else {
            $("."+actionStr+"_"+category).html("Category updated").css({color:"blue"});
            $("."+category_menu).append("<option value='" + input + "'>" + properCase(display_input) + "</option>");
            if (actionStr=="edit") {
              $("."+category_menu+ " option[value='" + oldValue.replace(/\s/g, "_") + "']").remove();
              $("."+category_menu).val("none").trigger("change");
            }
          }
        }
      });
    }
  }

  // ****Function for Add Feature Button
  $('#add_asset_btn').click(function() {
    if (!room_in_focus) {
      var error = $(document.createElement('div'));
      $(error).html("No room selected");
      $(error).dialog({title: "Error"});
    } else {
      asset_data={};
      $("#edit_asset_form").find("input[type=text], textarea").val("");
      $(".select2-single").val("none").trigger("change");
      editAsset();
      $("#floor").val(currentFloorView);
      $("#bldg_room").val(room_in_focus);
      $("#floor").prop({"readonly": true});
      $("#bldg_room").prop({"readonly": true});
      $("#checkbox").prop({"disabled": true});
      $("#bldg_name").select2();
      $("#bldg_name").val(bldg_in_focus).trigger("change");
      $("#bldg_name").prop({"disabled": true})
    }
  });

////////////////////////////////////////////////////////////////////////////////

// ****Capitalize the first letter of a string
  String.prototype.upperFirstChar = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
  }

// ****Create basemap layer
  var campus_map = L.tileLayer('https://api.mapbox.com/styles/v1/tomchen519/cindowlro000jakniv5hfe9ds/tiles/256/{z}/{x}/{y}?access_token=pk.eyJ1IjoidG9tY2hlbjUxOSIsImEiOiJjaWtnOWtyamswMDF4dnRrbWtxYjFleml6In0.4Wc6CHr2kaqhGzDnY06WiQ', {
    maxZoom: 20,
    attribution: 'Map: <a href="https://www.mapbox.com/about/maps/">&copy;Mapbox</a> <a href="http://www.openstreetmap.org/copyright">&copy;OpenStreetMap</a>'
  });

// ****Create map element
  map = L.map('map', {
    center: centerlatlng,
    zoom: zoom,
    minZoom: 17,
    maxZoom: 24,
    maxBounds: main_bounds,
    layers: [campus_map],
    zoomSnap: 0.25,
    zoomDelta: 0.25
  });

// ****Function call to retrieve all layers from GeoServer
  fetchFeatureLayers();

// ****Declare map layers and controls
  var baseLayers = {};
  var overlays = {
    "Campus Map": campus_map
  };
  layerControl = L.control.layers(baseLayers, overlays).addTo(map);
  L.control.scale({position: 'bottomright'}).addTo(map);
  sidebar = L.control.sidebar('sidebar', {position: 'left'});
  map.addControl(sidebar);

// ****LISTENER FUNCTION TO DETECT FLOOR NUMBER AND REFRESH MARKERS FOR THAT FLOOR
  map.on('baselayerchange', function(e) {
    cleanMap();
    currentFloorView = e.name.slice(-2);
    $("#query_floor").prop("placeholder", currentFloorView);
  });

// ****Check for user's new password and confirm password are matched
  function validatePassword (newPassword, confirmPassword) {
    if (confirmPassword != newPassword) {
      return 0;
    } else {
      return 1;
    }
  }

// ****JQuery function to show Profile Update Modal
  $('#updateProfileModal').on('show.bs.modal', function(e){
    $('#new_password').prop('disabled', true);
    $('#confirm_password').prop('disabled', true);
    $('#check_password').prop('disabled', false);
    $('#current_password').val('');
    $('#current_password').prop('disabled', false);
  });
  // ****Check user current password to enable profile editing
  $('#check_password').click(function(){
    var current_password = $('#current_password').val();
    var current_username = $('#username').attr('placeholder');
    if (current_password) {
      $.ajax({
        url: "./include/database.php",
        type: "post",
        dataType: 'json',
        data: {username: current_username, user_password: current_password},
        success: function(data) {
          if (data['response']!=1) {
            console.log("you need to input the correct password");
            $('#currentPwdError').css({"color":"red"});
            $('#currentPwdError').html("Password is incorrect");
            authenticated = false;
          } else {
            $('#currentPwdError').html("");
            $('#update_error').html("");
            $('#new_password').prop('disabled', false);
            $('#confirm_password').prop('disabled', false);
            $('#current_password').prop('disabled', true);
            $('#check_password').prop('disabled', true);
            authenticated = true;
          }
        }
      })
    }
  });

  // ****Add Keyup Event for "ENTER" on current user password check
  $('#current_password').keyup(function(e){
    if(e.keyCode==13){
      $('#check_password').click();
    }
  });

  // ****Close sidebar to expose Profile Update Modal
  $('#updateProfileBtn').click(function(){
    sidebar.close();
  });

  // ****Validate if new password and confirm passwords exist and match
  /////// NEED TO ADD UPDATE USER FUNCTION ON PHP ///////////////////
  $('#update_profile').click(function(){
    var newPassword = $('#new_password').val();
    var confirmPassword = $('#confirm_password').val();
    var current_username = $('#username').attr('placeholder');
    if (authenticated) {
      if ((!newPassword) && (!confirmPassword)) {
      }
      if ((!newPassword) || (!confirmPassword)) {
        $('#update_error').html("Passwords do not match");
      }
      if ((newPassword) && (confirmPassword)) {
        $('#update_error').html("");
        if (status = validatePassword(newPassword, confirmPassword)) {
          var passwordChange = 1;
          //  New password is sent to PHP for hashing
          $.ajax({
            url: "./include/database.php",
            type: "post",
            dataType: 'json',
            data: {manageUsers: "updateUser", username: current_username, new_password: newPassword},
            success: function(data) {
              if (data.response==1) {
                $('#update_error').html("Password saved").css({color: "blue"});
                $('#update_profile').prop({"disabled": true});
                $('#confirm_password, #new_password').prop({"readonly": true});
              } else {
                $('#update_error').html("Error encountered");
              }
            }
          });
        } else {
          $('#update_error').html("Passwords do not match");
        }
      }
    } else {
      $('#update_error').html("Please enter the correct current password");
    }
  });

  // ****Check for user role to restrict editing functions
  if (userRole!='admin') {
    $('#delete_marker:visible').attr('display', 'none');
  }

  // ****JQuery UI for date input
  $('.datepicker').datepicker({dateFormat: "yy-mm-dd"});

  function checkEditedValue (validatedArray) {
    new_edits = {};
    var required_fields = ["serial_number", "floor", "bldg_room", "serviced_by", "asset_type", "bldg_name", "status"];
    var input_fields = [], updated_value, edited_field;
    $(".editable.editor > input, .editable.editor > textarea, .editable.editor > select").each(function() {
      var id = this.id;
      input_fields.push(id);
    });
    $('.save_update_error').html("");
    $('.save_edits_btn').removeClass("btn-danger").addClass("btn-primary");

    for (var i = 0; i < input_fields.length; i++) {
      edited_field = input_fields[i];
      $('label[for="' + edited_field + '"]').css({color:"black"});
      edited_value = validatedArray[edited_field];
      if (edited_value=="" || edited_value==null) {
        edited_value = null;
      }
      if (edited_field != "notes" || edited_field != "acquired_date") {
        if (required_fields.indexOf(edited_field) != -1) {
          //   FOR REQUIRED INPUTS
          if (edited_value == null || edited_value == "") {
            $('label[for="' + edited_field + '"]').css({color:"red"});
            $('.save_update_error').html("Missing Required Fields");
            $('.save_edits_btn').removeClass("btn-primary").addClass("btn-danger");
            new_edits[edited_field] = "error";
          } else if (edited_value == "error") {
            $('label[for="' + input_fields[i] + '"]').css({color:"red"});
            $('.save_update_error').html("Invalid characters detected");
            $('.save_edits_btn').removeClass("btn-primary").addClass("btn-danger");
            new_edits[edited_field] = "error";
          } else if (!asset_data || edited_value != asset_data.properties[edited_field]) {
            new_edits[edited_field] = edited_value;
          }
        } else {
          //   FOR ALL OTHER INPUTS
          if (edited_value == "error") {
            $('label[for="' + input_fields[i] + '"]').css({color:"red"});
            $('.save_update_error').html("Invalid characters detected");
            $('.save_edits_btn').removeClass("btn-primary").addClass("btn-danger");
            new_edits[edited_field] = "error";
          } else if (!asset_data || edited_value != asset_data.properties[edited_field]) {
            new_edits[edited_field] = edited_value;
          }
        }
      } else if (!asset_data || edited_value != asset_data.properties[edited_field]) {
        new_edits[edited_field] = edited_value;
      }
    }
    console.log(new_edits);
    var error_count = 0;
    for (var key in new_edits) {
      if (new_edits[key]=="error") {
        error_count += 1;
      } else if (new_edits[key]==null) {
        delete new_edits[key];
      }
    }
    console.log("Errors found: "+error_count);
    if (error_count == 0) {
      if (!asset_data) {
        new_edits['action'] = "insert";
      } else if (Object.keys(new_edits).length > 0){
        new_edits['fid'] = asset_data.id;
        new_edits['action'] = "update";
      }
    return new_edits;
    }
  }

  $('#checkbox').change(function() {
    new_edits = {};
    if (this.checked) {
      editAsset(assets_inRoom[asset_data.id]);
      var date_now = new Date();
      date_now = date_now.toUTCString() + (date_now.getTimezoneOffset())/-60;
      disableUpdateFeature();
      if (asset_data) {
        new_edits["fid"] = asset_data.id;
        new_edits["service_date"] = date_now;
      }
    } else {
      enableUpdateFeature();
    }

  });

  function validateInput(inputArray) {
    inputString = null;
    // console.log("InputString: "+inputString);
    for (var key in inputArray) {
      inputString = inputArray[key];
      inputString = inputString.trim();
      if (key == "acquired_date") {
        if (inputString==null || inputString == "") {
          inputString = null;
          delete inputArray[key];
        } else {
          inputString = inputString + "Z";
        }

      } else if (key == "notes") {
        if (inputString == null || inputString == "") {
          delete inputArray[key];
        } else {
          inputString = encodeURI(inputString);
        }

      } else {
        if (/[^\w\s]/gi.test(inputString)) {
          inputString = "error";
        }
      }
      inputArray[key] = inputString;
    }
    console.log(inputArray);
    return inputArray;
  }

  // ****Select2 dropdown UI interactions
  $(".select2-single").select2({placeholder:"", width: "100%"});

  // ****SAVE BUTTON FUNCTIONS
  $('.save_edits_btn').click(function() {
    var array_to_validate = {};
    var input_fields = [];
    $(".editable.editor > input, textarea").each(function() {
      input_fields.push(this.id);
    });
    $(".editable.editor > select").each(function() {
      var field = this.id;
      var value_to_save = $("#"+field).val();
      if (value_to_save == "" || value_to_save == null) {
        value_to_save = "";
      } else {
        value_to_save = value_to_save.replace(/_/g, " ")
      }
      array_to_validate[field] = value_to_save;
    })
    for (var i = 0; i < input_fields.length; i++) {
      value_to_save = $("#"+input_fields[i]).val();
      array_to_validate[input_fields[i]] = value_to_save;
    }
    validated_array = validateInput(array_to_validate);
    var checked_array = checkEditedValue(validated_array);
    if (checked_array) {
      if (Object.keys(checked_array).length > 0) {
        console.log(checked_array);
        console.log("need to update to GeoServer")
        updateAssetData(checked_array);
      }
    }
  });

  $("#profile, #query, #assets_list, #data_manage, #user_manage").on('click', function() {
    $("#checkbox").prop({"checked": false});
  });

  $(".query_btn").click(function() {
    var query_array = {};
    $(".query").each(function() {
      var query_id = this.id;
      var query_value = $("#" + query_id).val();
      if (query_value!="" && query_value != null && query_value!= "none") {
        query_array[query_id] = query_value;
      }
    });
    console.log(query_array);
    if (Object.keys(query_array).length > 0) {
      queryRooms(query_array);
    }
  });

  $(".clear_btn").click(function() {
    document.getElementById("query_feature_form").reset();
    $(".select2-single").val("none").trigger("change");
    if (queried_room_featureGroup!=undefined) {
      map.removeLayer(queried_room_featureGroup);
    }
    cleanMap();
  });

  $(".new_category_btn").click(function(e) {
    var category = e.currentTarget.id.replace("add_","");
    var category_action = "add";
    var selected_category = category_action + "_" + category;
    var add_cate_btn_id = selected_category+"_btn";

    $("."+selected_category).html("<div class='input-group'><input type='text' class='"+selected_category+"_input form-control' placeholder='Add new " + selected_category.replace('add_','').replace('_',' ') + "'><span class='input-group-btn'><button class='btn btn-default' type='button' id='" + add_cate_btn_id + "'>Save</button></span></div>");

    $("#"+add_cate_btn_id).click(function(e) {
      console.log("Adding new values");
      var new_category_input = $("."+selected_category +"_input").val();
      console.log("."+selected_category +"_input");
      console.log(new_category_input);
      editCategoryValue(category, category_action, new_category_input);
    });
  });

  $(".edit_data.select2-single").on('select2:select', function (e) {
    var edit_value = e.target.value;
    var edit_type = e.target.id.replace("_menu", "");
    var edit_type_btn = edit_type;
    $("#"+edit_type_btn).prop("disabled", false);
  });

  $(".edit_category_btn").click(function(e) {
    var category = e.currentTarget.id.replace("edit_", "");
    var category_action = "edit";
    var selected_category = category_action + "_" +category;
    var edit_cate_btn_id = selected_category+"_btn";
    var value_to_edit = $("#"+selected_category+"_menu").val();
    value_to_edit = value_to_edit.replace(/_/g, " ");
    $("."+selected_category).html("<div class='input-group'><input type='text' class='"+selected_category+"_input form-control' placeholder='Edit \""+value_to_edit+"\"'><span class='input-group-btn'><button class='btn btn-default' type='button' id='" + edit_cate_btn_id + "'>Save</button></span></div>");

    $("#"+edit_cate_btn_id).click(function(e) {
      var edited_input = $("."+selected_category+"_input").val();
      editCategoryValue(category, category_action, edited_input, value_to_edit);
    });
  });

  $(".list_user_btn").click(function() {
    $(".user_list").html("");
    $.ajax({
      url: "./include/database.php",
      type: "post",
      dataType: "json",
      data: {manageUsers: "getUsers"},
      success: function(data) {
        var userlist = data[0];
        for (var i = 0; i < userlist.length; i++) {
          var display_user_id = userlist[i][0];
          var display_user_name = userlist[i][1];
          var display_user_role = userlist[i][2];
          $(".user_list").append(
            "<p><div><span class='title'>User Name: </span><span class='value'>" + display_user_name + "</span><br/>" +
            "<span class='title'>User Role: </span><span class='value'>" + display_user_role + "</span><br/>" + "<div><button type='button' id='" + display_user_id + "' class='delete_user_btn btn btn-xs btn-danger' data-toggle='tooltip' title='Delete existing user'><span class='fa fa-user-times'></span></button></div>" + "</div></p>"
          );

          $("#" + display_user_id).click(function() {
            var delete_user_id = this.id;
            $.ajax({
              url: "./include/database.php",
              type: "post",
              dataType: "json",
              data: {manageUsers: "deleteUser", deleteUser: delete_user_id},
              success: function(data) {
                $(".list_user_btn").trigger("click");
              }
            });
          })
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log("Status: " + textStatus); console.log("Error: " + errorThrown);
      }
    });
  });

  $(".new_user_btn").click(function() {
    console.log("adding new user");
    $(".user_list").html("");
    $(".user_list").append(
      "<form id='add_user_form' autocomplete='off'><div class='form-group editing_form'><label for='add_user_name'>Name of New User</label><input type='text' id='new_user_name' class='add_user form-control' name='new_user_name' placeholder='Joe Smith'><label for='add_user_role'>Role of New User</label>" +
      "<select id='new_user_role' class='add_user form-control select2-single'><option value='none' selected='selected' disabled>Select...</option><option value='admin'>Administrator</option><option value='editor'>Editor</option></select>" +
      "<button type='button' class='save_user_btn btn btn-primary edt_btn' data-toggle='tooltip' title='Save New User'><span class='fa fa-floppy-o' ></span>&nbsp;&nbsp;&nbsp;Save New User</button><div class='save_user_error update_error'></div></div>"
    );
    $("#new_user_role").select2({placeholder:"", width: "100%"});
    $(".save_user_btn").click(function(e) {
      $('.save_user_error').html("");
      $('label[for="add_user_role"]').css({color:"black"});
      $('label[for="add_user_name"]').css({color:"black"});
      $('.save_user_btn').removeClass("btn-danger").addClass("btn-primary");
      var add_user_data = {};
      var new_username = $('#add_user_form > div > input[type=text]')[0].value;
      var new_userrole = $('#add_user_form > div > select')[0].value;
      new_username = new_username.trim();
      if (/[^\w\s]/gi.test(new_username) || new_username=="" || new_username==null) {
        $('label[for="add_user_name"]').css({color:"red"});
        $('.save_user_error').html("Missing Required Fields or Invalid Characters");
        $('.save_user_btn').removeClass("btn-primary").addClass("btn-danger");
      } else {
        add_user_data['new_username'] = new_username;

      }
      if (new_userrole=="none") {
        $('label[for="add_user_role"]').css({color:"red"});
        $('.save_user_error').html("Missing Required Fields");
        $('.save_user_btn').removeClass("btn-primary").addClass("btn-danger");
      } else {
        add_user_data['new_userrole'] = new_userrole;
      }
      if (Object.keys(add_user_data).length == 2) {
        add_user_data['manageUsers']='addUser';
        $.ajax({
          url: "./include/database.php",
          type: "post",
          dataType: "json",
          data: add_user_data,
          success: function(data) {
            document.getElementById("add_user_form").reset();
            $("#new_user_role").val("none").trigger("change");
            if (data.insert==0) {
              $('.save_user_error').html("User already exist");
            } else {
              $('.save_user_error').html("User added").css({color:"blue"});
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log("Status: " + textStatus); console.log("Error: " + errorThrown);
          }
        });
      }
    });
  });
});
