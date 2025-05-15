<a id="back-to-top" href="#" class="btn btn-light btn-lg back-to-top" role="button"><i class="fas fa-chevron-up"></i></a>

<script src="<?=base_url()?>/template/js/app.js" type="text/javascript"></script>
<script src="<?=base_url()?>/template/js/script.js" type="text/javascript"></script>
<script src="<?=base_url()?>/template/toastr/dist/simple-notify.min.js"></script>
<script src="<?=base_url()?>/template/toastr/js/prism.js"></script>
<script src="<?=base_url()?>/template/toastr/js/OverlayScrollbars.min.js"></script>

                    <!-- Jquery, Popper, Bootstrap -->
                    <!-- <script src="https://app.mediabox.bi/paeejse/assets/js/vendor/jquery-1.12.4.min.js"></script> -->
                    <!-- <script src="https://app.mediabox.bi/paeejse/assets/js/popper.min.js"></script>
                    <script src="https://app.mediabox.bi/paeejse/assets/js/bootstrap.min.js"></script> -->

<script type="text/javascript">
        window.alert_notify=function(status,title,message,typ)
        {

          var notif=new Notify ({
            status:status,
            title: title,
            text: message,
            effect:'slide',
            speed: 700,
            customClass: null,
            customIcon: null,
            showIcon: true,
            showCloseButton: true,
            autoclose: true,
            autotimeout: 5000,
            gap: 20,
            distance: 20,
            type:typ,
            position: 'right top'
          })
          
        }
        
      </script>

<script>
        $(function() {
            // Select2
            $(".select2").each(function() {
                $(this)
                    .wrap("<div class=\"position-relative\"></div>")
                    .select2({
                        placeholder: "-- <?=lang('messages_lang.selectionner_transmission_du_bordereau') ?> --",
                        dropdownParent: $(this).parent()
                    });
            })
            // Daterangepicker
            $("input[name=\"daterange\"]").daterangepicker({
                opens: "left"
            });
            $("input[name=\"datetimes\"]").daterangepicker({
                timePicker: true,
                opens: "left",
                startDate: moment().startOf("hour"),
                endDate: moment().startOf("hour").add(32, "hour"),
                locale: {
                    format: "M/DD hh:mm A"
                }
            });
            $("input[name=\"datesingle\"]").daterangepicker({
                singleDatePicker: true,
                showDropdowns: true
            });
            var start = moment().subtract(29, "days");
            var end = moment();

            function cb(start, end) {
                $("#reportrange span").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
            }
            $("#reportrange").daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    "Today": [moment(), moment()],
                    "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "Last 7 Days": [moment().subtract(6, "days"), moment()],
                    "Last 30 Days": [moment().subtract(29, "days"), moment()],
                    "This Month": [moment().startOf("month"), moment().endOf("month")],
                    "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
                }
            }, cb);
            cb(start, end);
            // Datetimepicker
            $('#datetimepicker-minimum').datetimepicker();
            $('#datetimepicker-view-mode').datetimepicker({
                viewMode: 'years'
            });
            $('#datetimepicker-time').datetimepicker({
                format: 'LT'
            });
            $('#datetimepicker-date').datetimepicker({
                format: 'L'
            });
        });
    </script>

    <script>
        $(function() {
            $('#datatables-dashboard-products').DataTable({
                pageLength: 6,
                lengthChange: false,
                bFilter: false,
                autoWidth: false
            });
        });
    </script>


<script>
$(document).ready(function(){
    $(window).scroll(function () {
            if ($(this).scrollTop() > 50) {
                $('#back-to-top').fadeIn();
            } else {
                $('#back-to-top').fadeOut();
            }
        });
        // scroll body to 0px on click
        $('#back-to-top').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 400);
            return false;
        });
});
</script>
<!-- <script type="text/javascript">
$('.sidebar-item .active').parents('ul').addClass('show').each(function() {
    // This should iterate through all parent <li>s and the current one too
});</script>
<script type="text/javascript">
    $('.sidebar-toggle').on("click", function(){
  $('#sidebar').toggleClass('toggled');
});
</script> -->