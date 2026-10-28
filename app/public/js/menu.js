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

/**
 * Realiza una petición fetch con loader automático, manejo seguro de errores de red,
 * respuestas HTTP no-200 (419, 403, 422, 500) y cierre garantizado del loader (UX-24).
 *
 * @param {string} url
 * @param {object} options
 * @param {object} feedbackOptions
 * @returns {Promise<any>}
 */
async function peticionConFeedback(url, options = {}, feedbackOptions = {}) {
    var loadingTitle = feedbackOptions.loadingTitle || "Procesando...";
    var loadingText = feedbackOptions.loadingText || "Por favor espere";
    var showLoader = feedbackOptions.showLoader !== false;

    if (showLoader && typeof Swal !== "undefined") {
        Swal.fire({
            title: loadingTitle,
            text: loadingText,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function () {
                Swal.showLoading();
            },
        });
    }

    try {
        var response = await fetch(url, options);

        if (!response.ok) {
            var errorMsg = "Error en el servidor (" + response.status + ")";
            if (response.status === 419) {
                errorMsg = "Tu sesión ha expirado. Por favor recarga la página.";
            } else if (response.status === 403) {
                errorMsg = "No tienes permisos para realizar esta acción.";
            } else if (response.status === 404) {
                errorMsg = "El recurso solicitado no fue encontrado.";
            }

            try {
                var errJson = await response.json();
                if (errJson && errJson.message) {
                    errorMsg = errJson.message;
                } else if (errJson && errJson.errors) {
                    var msgs = Object.values(errJson.errors).flat();
                    if (msgs.length) errorMsg = msgs.join("\n");
                }
            } catch (_) {}

            throw new Error(errorMsg);
        }

        var data;
        var contentType = response.headers.get("content-type") || "";
        if (contentType.indexOf("application/json") !== -1) {
            data = await response.json();
        } else {
            data = await response.text();
        }

        if (data && typeof data === "object" && data.success === false) {
            throw new Error(data.message || "La operación no se pudo completar.");
        }

        if (showLoader && typeof Swal !== "undefined" && !feedbackOptions.keepLoader) {
            Swal.close();
        }

        return data;
    } catch (error) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.message || "Error de red o conexión interrumpida.",
                confirmButtonColor: "#dc3545",
                confirmButtonText: "Cerrar",
            });
        }
        throw error;
    }
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
            })
            .catch(function () {
                // Sin cámara o permiso denegado: la lista queda vacía y se usa la subida de archivo.
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
