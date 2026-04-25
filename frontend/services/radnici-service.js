var EmployeesService = {
  init: function () {
    EmployeesService.loadEmployees();
    EmployeesService.bindModal();
  },

  loadEmployees: function () {
    RestClient.get("users", function (employees) {
      $("#employees tbody").empty();

      employees.forEach(function (emp) {
        let row = `
          <tr>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#student-details-modal"
                data-bs-id="${emp.id}"
              >
                Smene
              </button>
            </td>
            <td>${emp.id}</td>
            <td>${emp.name}</td>
          </tr>
        `;

        $("#employees tbody").append(row);
      });
    });
  },

  bindModal: function () {
    $("#student-details-modal").on("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const user_id = $(button).data("bs-id");

      EmployeesService.loadShifts(user_id);
      EmployeesService.emptySalesModal();
    });
  },

  loadShifts: function (user_id) {
    //employeesService.emptySalesModal();
    RestClient.get("users/" + user_id + "/shifts", function (shifts) {
      $("#student-attendance-body").empty();

      shifts.forEach(function (shift, index) {
        let row = `
<tr>
  <td>${index + 1}</td>
  <td>
    <button 
      type="button" 
      class="btn btn-primary show-sales-btn"
      data-id = "${shift.shift_id}">
      Prikazi prodaju
    </button>
  </td>
  <td>${shift.date}</td>
  <td>${shift.smena}</td>
  <td>${shift.revenue} €</td>
  <td>${shift.true_total}</td>
</tr>
`;

        $("#student-attendance-body").append(row);
      });
    });
  },

  loadSales: function (shift_id) {
    RestClient.get("shift-products/" + shift_id, function (sales) {
      $("#sales-table tbody").empty();
      let true_total = 0;
      sales.forEach(function (sale, index) {
        let row = `
          <tr>
            <td>${index + 1}</td>
            <td>${sale.product_name}</td>
            <td>${sale.quantity_sold}</td>
            <td>${sale.true_total}€</td>
          </tr>
        `;
        $("#sales-table tbody").append(row);
        true_total += parseFloat(sale.true_total);
      });
      $("#total-revenue").text(`${true_total} €`);
    });
  },

  emptySalesModal: function () {
    $("#sales-table tbody").empty();
    $("#total-revenue").text("0 €");
  },
};
