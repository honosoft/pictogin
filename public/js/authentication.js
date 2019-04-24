
function submitForm(index) {
    $('.hover-effect').prop("onclick", null).off("click");
    $('#choice').val(index);
    console.log("submit");
    $('#form').submit();
}

function forgotPassword() {
    $('#forgotpwd')
        .modal({
            allowMultiple: false,
            onApprove : function() {
                $.post('/user/magic-link');
                window.location = '/';
            }
        })
        .modal('show');
}