$(document).ready(function() {
  $('[data-dtc-grid-jq-grid]').each(
    function(idx, val) {
      var options = $(val).data('dtc-grid-jq-grid');
      $(val).jqGrid(options);
    }
  )
});
