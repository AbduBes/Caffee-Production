let AdminService = {
  init: function () {
    const token = localStorage.getItem("user_token");
    if (!token) {
      window.location.hash = "#login";
      return;
    }

    const user = Utils.parseJwt(token).user;
    if (user.role !== Constants.ADMIN_ROLE) {
      toastr.error("Access denied");
      window.location.hash = "#login";
      UserService.generateMenuItems();
      return;
    }

    // Load data
    AdminService.loadProducts();
    AdminService.loadShifts();
    AdminService.loadLatestShift();
    AdminService.loadEmoployees();

    // Bind forms
    $("#product-form").validate({
      submitHandler: function (form) {
        const entity = Object.fromEntries(new FormData(form).entries());
        AdminService.createProduct(entity);
      },
    });

    $("#shift-form").validate({
      submitHandler: function (form) {
        const entity = Object.fromEntries(new FormData(form).entries());
        AdminService.createShift(entity);
      },
    });

    $("#shift-sale-form").validate({
      submitHandler: function (form) {
        const entity = Object.fromEntries(new FormData(form).entries());
        AdminService.addSaleToLatestShift(entity);
      },
    });
  },

  /* ================= PRODUCTS ================= */
  loadProducts: function () {
    RestClient.get("products", function (response) {
      const products = response;

      const table = document.getElementById("products-table");
      const select = document.getElementById("shift-product-select");

      table.innerHTML = "";
      select.innerHTML = `<option value="">Select product</option>`;

      products.forEach((p) => {
        table.insertAdjacentHTML(
          "beforeend",
          `
        <tr data-id="${p.id}" id="product-row-${p.id}">
          <td>${p.name}</td>
          <td>${p.entry_price}</td>
          <td>${p.price}</td>
          <td>
            <button class="btn btn-sm btn-danger"
              onclick="AdminService.deleteProduct(${p.id})">
              Delete
            </button>
          </td>
        </tr>
      `,
        );

        select.insertAdjacentHTML(
          "beforeend",
          `
        <option value="${p.id}">
          ${p.name} (€${p.price})
        </option>
      `,
        );
      });
    });
  },

  loadEmoployees: function () {
    RestClient.get("users?role=employee", function (response) {
      const select = document.getElementById("employee-select");
      select.innerHTML = `<option value="">Select Employee</option>`;
      response.forEach((e) => {
        select.insertAdjacentHTML(
          "beforeend",
          `
        <option value="${e.id}">
          ${e.name}
        </option>
      `,
        );
      });
    });
  },

  createProduct: function (entity) {
    RestClient.post("products", entity, function () {
      toastr.success("Product created");
      $("#product-form")[0].reset();
      AdminService.loadProducts();
    });
  },

  deleteProduct: function (id) {
    if (!confirm("Delete this product?")) return;

    RestClient.delete(`products/${id}`, function () {
      toastr.success("Product deleted");
    });
    document.getElementById(`product-row-${id}`).remove();
  },

  /* ================= SHIFTS ================= */
  loadShifts: function () {
    RestClient.get("shifts", function (response) {
      console.log("Shifts response:", response);

      const table = $("#shifts-table");
      table.empty();

      const shifts =
        response.data ||
        response.shifts ||
        (Array.isArray(response) ? response : []);
      shifts.forEach((shift) => {
        table.append(`
          <tr>
            <td>${shift.user_id}</td>
            <td>${shift.date}</td>
            <td>${shift.smena}</td>
          </tr>
        `);
      });
    });
  },

  createShift: function (entity) {
    RestClient.post("shifts", entity, function () {
      toastr.success("Shift created");
      $("#shift-form")[0].reset();

      // 🔄 Always reload from backend
      AdminService.loadShifts();
      //AdminService.loadLatestShift();
      $("#latest-shift-info").empty();
      AdminService.loadShiftSales();
    });
  },

  /* ================= SALES ================= */
  loadLatestShift: function () {
    RestClient.get(
      "shifts/latest",
      function (shift) {
        if (!shift || !shift.id) {
          $("#latest-shift-info").text("No shifts found");
          return;
        }

        $("#latest-shift-info").text(
          `Employee #${shift.user_id} | ${shift.date} (${shift.start_time} - ${shift.end_time})`,
        );

        AdminService.loadShiftSales(shift.id);
      },
      function () {
        $("#latest-shift-info").text("No shifts found");
      },
    );
  },

  loadShiftSales: function (shiftId) {
    RestClient.get(`shift-products/${shiftId}`, function (response) {
      console.log("Shift sales response:", response);

      const table = $("#shift-sales-table");
      table.empty();

      let total = 0;

      const sales = response;

      sales.forEach((s) => {
        const productName = s.product_name;
        const price = s.price;
        const entry_price = s.entry_price;
        const quantity = s.quantity_sold;
        const rowTotal = parseFloat(s.total_sale) || 0;

        total += rowTotal - entry_price * quantity;

        console.log("Sale:", { productName, quantity, price, rowTotal }); // Debug log

        table.append(`
        <tr>
          <td>${productName}</td>
          <td>${quantity}</td>
          <td>${entry_price}</td>
          <td>${price}</td>
          <td>${rowTotal}</td>
        </tr>
      `);
      });
      ``;
      $("#shift-total").text(total.toFixed(2) + " RSD");
    });
  },

  addSaleToLatestShift: function (entity) {
    RestClient.get("shifts/latest", function (shift) {
      if (!shift || !shift.id) {
        toastr.error("No shifts found");
        return;
      }

      RestClient.post(
        "shift-products",
        {
          shift_id: shift.id,
          product_id: entity.product_id,
          products_sold: entity.products_sold,
        },
        function () {
          toastr.success("Sale added");
          $("#shift-sale-form")[0].reset();
          AdminService.loadShiftSales(shift.id);
        },
      );
    });
  },
};
