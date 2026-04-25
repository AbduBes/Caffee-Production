var app = $.spapp({
  defaultView: "login",
  templateDir: "frontend/views/",
});

app.route({
  view: "reports",
  onCreate: function () {
    ReportsService.init();
  },
});

app.run();

// Handle showing/hiding sections
$(window).on("hashchange", function () {
  const current = location.hash.slice(1) || "login";
  $("main#spapp > section").hide();
  $("#" + current).show();
});

// Run it once when the page loads
$(function () {
  const current = location.hash.slice(1) || "login";
  $("main#spapp > section").hide();
  $("#" + current).show();
});
