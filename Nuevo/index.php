<?php
// Iniciar la sesión
session_start();

// Redirigir directamente a la página de login sin verificar la sesión
header("Location: login/login.html");
exit();