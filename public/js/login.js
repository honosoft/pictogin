/**
 * When an image is clicked, change the state.
 * @param index index of the image.
 */
function imageClick(index) {
    $('#image_' + index).toggleClass('circular green-effect');
}

function nextClick() {
    let ids = [];
    $('.circular').each(function() {
        ids.push(this.id.split('_')[1]);
    });
    $('#choice').val(ids.join(','));
    $('#form').submit();
}


/**
 * use semantic-ui dialog window to confirm the forget password.
 */
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