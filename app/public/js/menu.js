function showOverlay(tag) {
    $("#" + tag).prepend(
        '<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>'
    );
}

function showLoading(properties) {
    icon = properties.icon || "info";
    title = properties.title || "Loading...";
    html = properties.html || "";

    Swal.fire({
        icon: icon,
        title: title,
        html: html,
        showConfirmButton: false,
        allowEscapeKey: false,
        allowOutsideClick: false,
    });
}

function closeCard(card) {
    $(card).parents(".card").remove();
}

function showNotify(type, message) {
    $.notify(
        {
            icon: "icon-bell",
            title: APP_NAME,
            message: message,
        },
        {
            type: type,
            placement: {
                from: "top",
                align: "center",
            },
            time: 5000,
        }
    );
}

var LIST_CAM = [];
var OPTIONS_CAM = "";
var CONT_CAM = 1;
function getCamerasPC() {
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        navigator.mediaDevices
            .getUserMedia({ video: true })
            .then(() => {
                // Enumerar los dispositivos de medios
                return navigator.mediaDevices.enumerateDevices();
            })
            .then((devices) => {
                var videoDevices = devices.filter(function (device) {
                    return device.kind === "videoinput";
                });
                videoDevices.forEach(function (device, index) {
                    if (array_search(device.deviceId, LIST_CAM) === false) {
                        LIST_CAM.push(device.deviceId);
                        OPTIONS_CAM +=
                            '<option value="' +
                            device.deviceId +
                            '">Camera ' +
                            CONT_CAM +
                            "</option>";
                        CONT_CAM++;
                    }
                });
            });
    }
}

function array_search(needle, haystack) {
    for (var i in haystack) {
        if (haystack[i] == needle) return i;
    }
    return false;
}

function setMediaDevice(tagVid, cam) {
    var videoElement = document.getElementById(tagVid);
    navigator.mediaDevices
        .getUserMedia({
            video: { deviceId: cam },
        })
        .then(function (stream) {
            videoElement.srcObject = stream;
            videoElement.play();
            $(".overlay").remove();
        })
        .catch(function (error) {
            console.log("Error accessing camera: ", error);
        });
}

function setPhoneTagInput(lang, tag, country) {
    //fetch("{{asset('template/kaiadmin/assets/js/plugin/intl-tel/lang/es.json')}}")
    url =
        APP_ROUTE +
        "template/kaiadmin/assets/js/plugin/intl-tel/lang/" +
        lang +
        ".json";
    country = country;

    $.ajax({
        url: url,
        type: "GET",
        async: false,
        dataType: "json",
        success: function (response) {
            input = document.getElementById(tag);
            return window.intlTelInput(input, {
                initialCountry: country,
                strictMode: true,
                separateDialCode: true,
                i18n: response,
                utilsScript:
                    APP_ROUTE +
                    "template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input-utils.js",
            });
        },
        error: function () {},
    });

    // return 123;
    /*
     
     fetch(url)
     .then(response => {
     if (!response.ok) {
     throw new Error('Error on Load  JSON');
     }
     return response.json();
     })
     .then(jsonData => {
     
     
     input = document.getElementById(tag);
     return  window.intlTelInput(input, {
     initialCountry: country,
     strictMode: true,
     separateDialCode: true,
     i18n: jsonData,
     utilsScript: APP_ROUTE + "template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input-utils.js",
     });
     
     })
     .catch(error => {
     console.error(error);
     });
     * 
     */
}
