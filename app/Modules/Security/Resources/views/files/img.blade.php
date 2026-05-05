<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
        <style>
            #img-book {
                display: none; /* Ocultar la imagen por defecto */
            }
            #img-book {
                pointer-events: none; /* Deshabilita los eventos del mouse en la imagen */
            }
        </style>
    </head>
    <body>
        {!!$IMAGE!!}
        <script>
            document.oncontextmenu = function () {
                return false; // Deshabilitar el clic derecho
            };
            // Verificar si la página está dentro de un iframe
            if (window.self !== window.top) {
                // Si está en un iframe, mostrar la imagen
                document.getElementById('img-book').style.display = 'block';
            } else {
                document.getElementsByTagName('body')[0].innerHTML = "";
            }
        </script>

    </body>
</html>