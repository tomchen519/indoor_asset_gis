// Written by Tom Chen
function validate_login(loginArray) {
  console.log(loginArray);
  var errors={};
  for (var key in loginArray) {
    console.log(loginArray[key]);
    value = loginArray[key].trim();
    if (value == null || value == "" || (/[^\w\s]/gi.test(value))) {
      errors[key] = 1;
      console.log(error);
    }
  }

  if (Object.keys(errors).length == 0) {
    $.ajax({
      url: "./include/login_form.php",
      method: "post",
      data: loginArray,
      dataType: "json",
      success: function(data) {
        if (data["userRole"]) {
          console.log(data["userRole"])
          location.href = "asset_map.php";
        } else if (data["error"]) {
          console.log(data["error"]);
          $("#password_error").text("Username and/or password are incorrect");
        }
      }
    });
  } else {
    console.log( "Invalid input, please check your username and password.");
    for (var error in errors) {
      console.log(error);
      $("#"+error+"_error").text("Invalid input, please check again");
    }
  }
}
$(document).ready( function () {
  $('#submit_btn').click(function(){
    $('.error').text('');
    var login_array = {};
    var username = $('#username').val();
    var password = $('#password').val();
    login_array['username']=username;
    login_array['password']=password;
    console.log(login_array);
    validate_login(login_array);
  });
  $('#username, #password').keyup(function(e){
    if(e.keyCode==13){
      $('#submit_btn').click();
    }
  });
});
