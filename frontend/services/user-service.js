var UserService = {
  init: function () {
    const token = localStorage.getItem("user_token");

    if (token) {
      window.location.hash = "#admin";
    }

    $("#loginForm").validate({
      submitHandler: function (form) {
        const entity = Object.fromEntries(new FormData(form).entries());
        UserService.login(entity);
      },
    });

    $("#signupForm").validate({
      submitHandler: function (form) {
        const entity = Object.fromEntries(new FormData(form).entries());
        UserService.register(entity);
      },
    });
  },

  login: function (entity) {
    RestClient.post("auth/login", entity, function (response) {
      localStorage.setItem("user_token", response.data.token);
      toastr.success("Welcome back!");
      UserService.generateMenuItems();
      window.location.hash = "#admin";
      AdminService.init();
    });
  },

  register: function (entity) {
    RestClient.post("auth/register", entity, function () {
      toastr.success("Registration successful. Please login.");
      $("#signupForm").addClass("d-none");
      $("#loginForm").removeClass("d-none");
    });
  },

  logout: function () {
    localStorage.clear();
    toastr.info("Logged out");
    UserService.generateMenuItems();
    window.location.hash = "#login";
  },
  generateMenuItems: function () {
    const token = localStorage.getItem("user_token");
    const authLink = document.getElementById("auth-link");

    // Not logged in/Logout → show Login
    if (!token) {
      if (document.getElementById("logout-btn")) {
        const logoutBtn = document.getElementById("logout-btn");
        const employeesLink = document.getElementById("employees-link");
        const reportsLink = document.getElementById("reports-link");
        const navbar = document.getElementById("navbar");
        reportsLink.remove();
        employeesLink.remove();
        logoutBtn.remove();

        navbar.insertAdjacentHTML(
          "afterbegin",
          `
      <a href="#login" class="nav-item nav-link active" id="auth-link">
        Log in
      </a>
    `,
        );
        if (document.getElementById("admin-link")) {
          document.getElementById("admin-link").remove();
        }

        window.location.hash = "#login";
        return;
      }
      authLink.outerHTML = `
      <a href="#login" class="nav-item nav-link active" id="auth-link">
        Log in
      </a>
    `;
      return;
    }

    const user = Utils.parseJwt(token).user;

    if (user.role === "admin") {
      const adminLink = `<a href="#admin" class="nav-item nav-link active" id="admin-link">
     Admin
</a>`;
      const employeesLink = `<a href="#employees" class="nav-item nav-link active" id="employees-link">
     Employees
</a>`;

      const reportsLink = `<a href="#reports" class="nav-item nav-link active" id="reports-link">Izveštaji</a>`;

      // Use 'afterbegin' to insert the HTML string just inside the element, before its first child.
      navbar.insertAdjacentHTML("afterbegin", adminLink);
      navbar.insertAdjacentHTML("afterbegin", employeesLink);
      navbar.insertAdjacentHTML("afterbegin", reportsLink);
    }

    // Logged in → show Profile + Logout
    authLink.outerHTML = `
    <button id = "logout-btn" class="btn btn-danger ml-3" onclick="UserService.logout()">
      Logout
    </button>
  `;
  },
};