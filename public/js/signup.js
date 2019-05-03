/**
 * Just submit programmatically.
 * @param index
 */
function imageClick(index) {
    $('.hover-effect').prop("onclick", null).off("click");
    $('#choice').val(index);
    $('#form').submit();
}
