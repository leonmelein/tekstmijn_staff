/**
 * Created by leon on 14-06-17.
 */
$(function () {
    $('#datetimepicker2').datetimepicker({
        format: 'YYYY-MM-DD HH:mm'
    });
    $('#datetimepicker3').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        useCurrent: false
    });
    $("#datetimepicker2").on("dp.change", function (e) {
        $('#datetimepicker3').data("DateTimePicker").minDate(e.date);
    });
    $("#datetimepicker3").on("dp.change", function (e) {
        $('#datetimepicker2').data("DateTimePicker").maxDate(e.date);
    });
});