//ajax delete yell call
  $(document).on('click', '.deleteYell', function (e) {
      var id = $(this).attr("data-id");
      // prevent the page from submitting like normal
      e.preventDefault();

      $.ajax({
          url: 'http://localhost/TwitterClone/public/delete/'+id,
          type: 'POST',

          data: $(this).serialize(),
          success: function (data) {
            $("#posts").load(" #posts");
            $("#countersAndButtons").load(" #countersAndButtons");
            //flash delete
            $('#flashPost').fadeOut();
            $('#flashDelete').fadeIn();
    				setTimeout(function() {
    					$('#flashDelete').fadeOut("slow");
    				}, 2000 );
          },
          error: function () {
              console.log('it failed!');
          }
      });
  });

//ajax post call
$('form.postYell').submit(function (e) {
    // prevent the page from submitting like normal
    e.preventDefault();
    $.ajax({
        url: 'http://localhost/TwitterClone/public/yell',
        type: 'POST',
        data: $(this).serialize(),
        success: function (data) {
          $("#posts").load(" #posts");
          $("#countersAndButtons").load(" #countersAndButtons");
          $('input[type=text], textarea').val('');
          //flash success
          $('#flashPost').fadeIn();
  				setTimeout(function() {
  					$('#flashPost').fadeOut("slow");
  				}, 2000 );
        },
        error: function () {
            console.log('it failed!');
        }
    });
});
//http://localhost/TwitterClone/public/profile/Mario%20Camarena/follow
//http://localhost/TwitterClone/public/profile/Mario%20Camarena/followers/unfollow
//ajax unfollow call
$(document).on('submit', 'form.followPerson', function (e) {
// prevent the page from submitting like normal
  e.preventDefault();
  var url;
  //go back one level if we are too deep
  //this could probably be done in a more eloquent way
  if (window.location.href.indexOf("followings") > -1 || window.location.href.indexOf("followers") > -1 || window.location.href.indexOf("yells") > -1) {
    url =  window.location.href+'/../follow';
  }else {
    url =  window.location.href+'/follow';
  };
  $.ajax({
    //current url + follow

      url: url,
      type: 'POST',
      success: function () {
        $("#posts").load(" #posts");
        $("#countersAndButtons").load(" #countersAndButtons");
        $("#follows").load(" #follows");
      },
      error: function () {
          console.log('it failed!');
      }
    });
  });
//ajax follow call
$(document).on('submit', 'form.unFollowPerson', function (e) {
// prevent the page from submitting like normal
e.preventDefault();
var url;
//go back one level if we are too deep
if (window.location.href.indexOf("followings") > -1 || window.location.href.indexOf("followers") > -1 || window.location.href.indexOf("yells") > -1) {
  url =  window.location.href+'/../unfollow';
}else {
  url =  window.location.href+'/unfollow';
};
$.ajax({
    url: url,
    type: 'POST',
    success: function () {
      $("#posts").load(" #posts");
      $("#countersAndButtons").load(" #countersAndButtons");
      $("#follows").load(" #follows");
    },
    error: function () {
        console.log('it failed!');
    }
  });
});
//lable for input file counter thingy
$('input').change(function() {
  //regex magic to get rid of fakepaths
	$(this).next('label').text($(this).val().split(/(\\|\/)/g).pop());
})
