


$(document).ready(function () {
    $("#frm1").validate({
        errorPlacement: function (error, element) {
            element.parent().after(error);
        }
    });

    
});


function showLoading(properties) {
    icon = properties.icon || 'info';
    title = properties.title || 'Loading...';
    html = properties.html || '';
    
    Swal.fire({
        icon: icon,
        title: title,
        html: html,
        showConfirmButton: false,
        allowEscapeKey: false,
        allowOutsideClick: false

    });
}



