function get(url, data)
{
    $.ajax({
        type: "GET",
        url: url,
        dataType: 'json',
        data: data,

        success: function (response) {
          alert(response.result);
        },

        error: function (xhr) {
          var response = xhr.responseJSON;
          alert(response.error);
        }
      });
}

function post(
  url,
  data,
  successFunction = function (response) {
    alert(response.result);
  },
  errorFunction = function (response) {
    alert(response.error);
  })
{
    $.ajax({
      type: "POST",
      url: url,
      dataType: 'json',
      data: data,

      success: function (response) {
        successFunction(response);
      },

      error: function (xhr) {
        var response = xhr.responseJSON;
        errorFunction(response);
      }
    });
}