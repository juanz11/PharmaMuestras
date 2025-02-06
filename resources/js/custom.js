// Configurar el título de las pestañas
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el título actual de la página
    let currentTitle = document.title;
    
    // Reemplazar 'Laravel' con 'SNCPharma' si está presente
    if (currentTitle.includes('Laravel')) {
        document.title = currentTitle.replace('Laravel', 'SNCPharma');
    }
    
    // También establecer el título cuando la pestaña se hace visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            document.title = currentTitle;
        } else {
            document.title = currentTitle.replace('Laravel', 'SNCPharma');
        }
    });
});
