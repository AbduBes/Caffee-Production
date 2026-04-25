let RestClient = {
  get: function (url, callback, error_callback) {
    $.ajax({
      url: Constants.PROJECT_BASE_URL + url,
      type: "GET",
      beforeSend: function (xhr) {
        if (!url.startsWith("auth/login") && !url.startsWith("auth/register")) {
          const token = localStorage.getItem("user_token");
          if (token) {
            xhr.setRequestHeader("Authorization", "Bearer " + token);
          }
        }
      },
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        if (error_callback) error_callback(jqXHR);
        toastr.error("Request failed: " + textStatus);
      },
    });
  },
  request: function (url, method, data, callback, error_callback) {
    $.ajax({
      url: Constants.PROJECT_BASE_URL + url,
      type: method,
      data: data ? JSON.stringify(data) : null,
      contentType: "application/json",
      dataType: "json",
      beforeSend: function (xhr) {
        //  DO NOT attach token to login/register
        if (!url.startsWith("auth/login") && !url.startsWith("auth/register")) {
          const token = localStorage.getItem("user_token");
          if (token) {
            xhr.setRequestHeader("Authorization", "Bearer " + token);
          }
        }
      },
    })
      .done(function (response) {
        if (callback) callback(response);
      })
      .fail(function (jqXHR) {
        if (error_callback) error_callback(jqXHR);
        let msg = "Request failed";
        if (jqXHR.responseJSON?.message) msg = jqXHR.responseJSON.message;
        else if (jqXHR.responseText) msg = jqXHR.responseText;
        toastr.error(msg);
      });
  },
  post: function (url, data, callback, error_callback) {
    RestClient.request(url, "POST", data, callback, error_callback);
  },
  delete: function (url, data, callback, error_callback) {
    RestClient.request(url, "DELETE", data, callback, error_callback);
  },
  patch: function (url, data, callback, error_callback) {
    RestClient.request(url, "PATCH", data, callback, error_callback);
  },
  put: function (url, data, callback, error_callback) {
    RestClient.request(url, "PUT", data, callback, error_callback);
  },
};
