var ReportsService = {
  init: function () {
    // Set default month to current month
    const currentMonth = new Date().toISOString().slice(0, 7);
    $("#monthSelect").val(currentMonth);

    // Generate report button
    $("#generateReport").on("click", function () {
      const month = $("#monthSelect").val();
      if (!month) {
        toastr.error("Molimo izaberite mesec");
        return;
      }
      ReportsService.loadMonthlyReport(month);
    });

    // Manual tab switching
    $("#overview-tab").on("click", function () {
      $(".nav-link").removeClass("active");
      $(this).addClass("active");
      $(".tab-pane").removeClass("show active");
      $("#overview").addClass("show active");
    });

    $("#products-tab").on("click", function () {
      $(".nav-link").removeClass("active");
      $(this).addClass("active");
      $(".tab-pane").removeClass("show active");
      $("#products").addClass("show active");
    });

    $("#employees-tab").on("click", function () {
      $(".nav-link").removeClass("active");
      $(this).addClass("active");
      $(".tab-pane").removeClass("show active");
      $("#employee").addClass("show active");
    });

    $("#venues-tab").on("click", function () {
      $(".nav-link").removeClass("active");
      $(this).addClass("active");
      $(".tab-pane").removeClass("show active");
      $("#venues").addClass("show active");
    });

    // Load current month by default
    ReportsService.loadMonthlyReport(currentMonth);
  },

  loadMonthlyReport: function (month) {
    $("#loadingSpinner").show();

    RestClient.get(
      `reports/monthly?month=${month}`,
      function (response) {
        $("#loadingSpinner").hide();
        ReportsService.renderOverview(response.overview);
        ReportsService.renderProducts(response.productSales);
        ReportsService.renderEmployees(response.employeeSales);
        ReportsService.renderVenues(response.venueCosts);
        toastr.success("Izveštaj uspešno učitan");
      },
      function (error) {
        $("#loadingSpinner").hide();
        toastr.error("Greška pri učitavanju izveštaja");
        console.error(error);
      },
    );
  },

  renderOverview: function (overview) {
    const html = `
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Ukupan Prihod</h5>
                        <h2>${overview.totalRevenue.toLocaleString("sr-RS")} RSD</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Ukupni Troškovi</h5>
                        <h2>${overview.totalCosts.toLocaleString("sr-RS")} RSD</h2>
                        <small>Proizvodi: ${overview.totalProductCosts.toLocaleString("sr-RS")} RSD</small><br>
                        <small>Objekti: ${overview.totalVenueCosts.toLocaleString("sr-RS")} RSD</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Neto Dobit</h5>
                        <h2>${overview.netProfit.toLocaleString("sr-RS")} RSD</h2>
                        <small>Bruto: ${overview.grossProfit.toLocaleString("sr-RS")} RSD</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Ukupno Smena</h5>
                        <h2>${overview.totalShifts}</h2>
                        <small>Prodato: ${overview.totalProductsSold} kom</small>
                    </div>
                </div>
            </div>
        `;
    $("#overviewMetrics").html(html);
  },

  renderProducts: function (products) {
    let html = "";
    let totalQty = 0;
    let totalRevenue = 0;
    let totalCost = 0;
    let totalProfit = 0;

    products.forEach(function (product) {
      const margin = ((product.profit / product.revenue) * 100).toFixed(1);
      const badgeClass = margin > 50 ? "badge-success" : "badge-warning";

      html += `
                <tr>
                    <td>${product.name}</td>
                    <td class="text-right">${product.quantity}</td>
                    <td class="text-right text-success font-weight-bold">${product.revenue.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right text-danger">${product.cost.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right text-primary font-weight-bold">${product.profit.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right"><span class="badge ${badgeClass}">${margin}%</span></td>
                </tr>
            `;

      totalQty += product.quantity;
      totalRevenue += product.revenue;
      totalCost += product.cost;
      totalProfit += product.profit;
    });

    // Add totals row
    html += `
            <tr class="table-dark font-weight-bold">
                <td>UKUPNO</td>
                <td class="text-right">${totalQty}</td>
                <td class="text-right text-success">${totalRevenue.toLocaleString("sr-RS")} RSD</td>
                <td class="text-right text-danger">${totalCost.toLocaleString("sr-RS")} RSD</td>
                <td class="text-right text-primary">${totalProfit.toLocaleString("sr-RS")} RSD</td>
                <td></td>
            </tr>
        `;

    $("#productsTable tbody").html(html);
  },

  renderEmployees: function (employees) {
    let html = "";
    let totalShifts = 0;
    let totalDays = 0;
    let totalProducts = 0;
    let totalRevenue = 0;
    let totalProfit = 0;

    employees.forEach(function (employee) {
      const avgPerShift = (employee.revenue / employee.shifts).toFixed(0);

      html += `
                <tr>
                    <td>${employee.name}</td>
                    <td class="text-right">${employee.shifts}</td>
                    <td class="text-right">${employee.days_worked}</td>
                    <td class="text-right">${employee.products_sold}</td>
                    <td class="text-right text-success font-weight-bold">${employee.revenue.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right text-primary font-weight-bold">${employee.profit.toLocaleString("sr-RS")} RSD</td>
                </tr>
            `;

      totalShifts += employee.shifts;
      totalDays += employee.days_worked;
      totalProducts += employee.products_sold;
      totalRevenue += employee.revenue;
      totalProfit += employee.profit;
    });

    // Add totals row
    html += `
            <tr class="table-dark font-weight-bold">
                <td>UKUPNO</td>
                <td class="text-right">${totalShifts}</td>
                <td class="text-right">${totalDays}</td>
                <td class="text-right">${totalProducts}</td>
                <td class="text-right text-success">${totalRevenue.toLocaleString("sr-RS")} RSD</td>
                <td class="text-right text-primary">${totalProfit.toLocaleString("sr-RS")} RSD</td>
            </tr>
        `;

    $("#employeesTable tbody").html(html);
  },

  renderVenues: function (venues) {
    let html = "";
    let totalDays = 0;
    let totalShifts = 0;
    let totalCost = 0;
    let totalRevenue = 0;
    let totalProfit = 0;

    venues.forEach(function (venue) {
      const roi = ((venue.net_profit / venue.total_cost) * 100).toFixed(1);
      let roiBadgeClass = "badge-danger";
      if (roi > 100) roiBadgeClass = "badge-success";
      else if (roi > 50) roiBadgeClass = "badge-warning";

      html += `
                <tr>
                    <td>${venue.name}</td>
                    <td class="text-right">${venue.daily_cost.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right">${venue.days_active}</td>
                    <td class="text-right">${venue.total_shifts}</td>
                    <td class="text-right text-danger">${venue.total_cost.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right text-success font-weight-bold">${venue.revenue.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right text-primary font-weight-bold">${venue.net_profit.toLocaleString("sr-RS")} RSD</td>
                    <td class="text-right"><span class="badge ${roiBadgeClass}">${roi}%</span></td>
                </tr>
            `;

      totalDays += venue.days_active;
      totalShifts += venue.total_shifts;
      totalCost += venue.total_cost;
      totalRevenue += venue.revenue;
      totalProfit += venue.net_profit;
    });

    // Add totals row
    html += `
            <tr class="table-dark font-weight-bold">
                <td>UKUPNO</td>
                <td></td>
                <td class="text-right">${totalDays}</td>
                <td class="text-right">${totalShifts}</td>
                <td class="text-right text-danger">${totalCost.toLocaleString("sr-RS")} RSD</td>
                <td class="text-right text-success">${totalRevenue.toLocaleString("sr-RS")} RSD</td>
                <td class="text-right text-primary">${totalProfit.toLocaleString("sr-RS")} RSD</td>
                <td></td>
            </tr>
        `;

    $("#venuesTable tbody").html(html);
  },
};
