<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a TiendaComponentes</title>
    
    <link rel="stylesheet" href="css/estilos.css">
    
    <link rel="stylesheet" href="css/inicio.css">
</head>
<body>

    <video autoplay muted loop id="video-background">
        <source src="images/fondo_video.mp4" type="video/mp4">
        Tu navegador no soporta video HTML5.
    </video>

    <audio id="musica-fondo" loop>
        <source src="images/musica_fondo.mp3" type="audio/mpeg">
    </audio>

    <div class="content">
        <img src="images/logo_tienda.png" alt="TiendaComponentes Logo" class="logo-img">
        
        <h1>El Futuro del Hardware</h1>
        <p class="descripcion">
            Bienvenido a <strong>TiendaComponentes</strong>, tu destino definitivo para la tecnología de vanguardia. 
            Descubre procesadores de última generación, gráficas extremas y todo lo que necesitas para construir la máquina de tus sueños.
        </p>

        <a href="login.php" class="btn-entrar" onclick="activarSonido()">Explorar Tienda </a>
    </div>

    <button id="audio-control" onclick="toggleSonido()">🔇</button>

    <script>
        var audio = document.getElementById("musica-fondo");
        var btnAudio = document.getElementById("audio-control");
        var isPlaying = false;

        window.addEventListener('click', function() {
            if (!isPlaying) {
                audio.play().then(() => {
                    isPlaying = true;
                    btnAudio.innerHTML = "🔊";
                }).catch(error => {
                    console.log("Autoplay bloqueado por el navegador");
                });
            }
        }, { once: true });

        function toggleSonido() {
            if (isPlaying) {
                audio.pause();
                btnAudio.innerHTML = "🔇";
                isPlaying = false;
            } else {
                audio.play();
                btnAudio.innerHTML = "🔊";
                isPlaying = true;
            }
        }
        
        function activarSonido() {
            audio.play();
        }
    </script>

</body>
</html>