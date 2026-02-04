<?php
// config/EmailHelper.php

require_once __DIR__ . '/class.phpmailer.php';
require_once __DIR__ . '/class.smtp.php';

class EmailHelper {
    
    private $mail;
    private $emailAdmin = 'sebastianobando@sanmarino.com.co';
    
    public function __construct() {
        $this->mail = new PHPMailer();
        $this->configurarSMTP();
    }
    
    private function configurarSMTP() {
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = "ssl"; // SSL en puerto 465
        $this->mail->Host = "smtp.gmail.com";
        $this->mail->Port = 465;
        
        $this->mail->Username = 'dotacionsa@gmail.com';
        
        // ⚠️ IMPORTANTE: Reemplaza 'TU_CONTRASEÑA_DE_APLICACION_16_CARACTERES' con la contraseña
        // de aplicación que generes en Gmail (sigue la guía GUIA_CONFIGURAR_GMAIL.md)
        // Ejemplo: $this->mail->Password = 'abcdefghijklmnop';
        $this->mail->Password = 'rsrbseqfdtujmuxl';
        
        $this->mail->SetFrom('dotacionsa@gmail.com', 'Sistema de Requisiciones Sanmarino');
        $this->mail->CharSet = 'UTF-8';
        
        $this->mail->SMTPDebug = 2; // 0 = off, 1 = client messages, 2 = client and server messages
        $this->mail->Debugoutput = function($str, $level) {
            error_log("[PHPMailer DEBUG] $str");
        };
    }
    
    /**
     * Notificar nueva requisición al administrador (sebastianobando@sanmarino.com.co)
     * Se ejecuta cuando alguien llena el formulario de requisición
     */
    public function notificarNuevaRequisicion($requisicion) {
        try {
            $this->mail->ClearAddresses();
            $this->mail->AddAddress($this->emailAdmin);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "🆕 Nueva Requisición de Personal - " . $requisicion['codigo'];
            
            $mensaje = $this->plantillaNuevaRequisicion($requisicion);
            $this->mail->Body = $mensaje;
            
            if (!$this->mail->Send()) {
                $error = "Error al enviar correo de nueva requisición: " . $this->mail->ErrorInfo;
                error_log($error);
                return false;
            }
            
            error_log("✅ Correo de nueva requisición enviado a: " . $this->emailAdmin);
            return true;
            
        } catch(Exception $e) {
            error_log("❌ Excepción al enviar correo de nueva requisición: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notificar cambio de estado al solicitante
     * Se ejecuta cuando se cambia el estado de una requisición en el panel
     */
    public function notificarCambioEstado($requisicion, $nuevoEstado) {
        try {
            $this->mail->ClearAddresses();
            
            if (!empty($requisicion['email_solicita'])) {
                $this->mail->AddAddress($requisicion['email_solicita']);
            } else {
                error_log("No hay email de solicitante para la requisición: " . $requisicion['codigo']);
                return false;
            }
            
            $this->mail->isHTML(true);
            
            // Asuntos personalizados según el estado
            $asuntos = [
                'Pendiente' => '⏳ Requisición Registrada',
                'Aprobada' => '✅ Requisición APROBADA',
                'En Proceso' => '👥 Candidato(s) ENCONTRADO(S)',
                'Cerrada' => '✔️ Requisición CERRADA'
            ];
            
            $this->mail->Subject = ($asuntos[$nuevoEstado] ?? 'Actualización de Requisición') . " - " . $requisicion['codigo'];
            
            $mensaje = $this->plantillaCambioEstado($requisicion, $nuevoEstado);
            $this->mail->Body = $mensaje;
            
            if (!$this->mail->Send()) {
                $error = "Error al enviar correo de cambio de estado: " . $this->mail->ErrorInfo;
                error_log($error);
                return false;
            }
            
            error_log("✅ Correo de cambio de estado enviado a: " . $requisicion['email_solicita']);
            return true;
            
        } catch(Exception $e) {
            error_log("❌ Excepción al enviar correo de cambio de estado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Plantilla HTML para notificación de nueva requisición (al administrador)
     */
    private function plantillaNuevaRequisicion($req) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">
            <div style="max-width:600px; margin:30px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                
                <!-- Encabezado -->
                <div style="background: linear-gradient(135deg, #F2B749, #F2620F); color:#ffffff; padding:25px; text-align:center;">
                    <h1 style="margin:0; font-size:26px; font-weight:bold;">🆕 Nueva Requisición de Personal</h1>
                    <p style="margin:10px 0 0 0; font-size:14px; opacity:0.9;">Sistema de Requisiciones SANMARINO</p>
                </div>
                
                <!-- Cuerpo -->
                <div style="padding:30px; color:#333333;">
                    <p style="font-size:16px; margin:0 0 25px 0; line-height:1.6;">
                        Se ha registrado una nueva solicitud de requisición de personal que requiere su atención:
                    </p>
                    
                    <!-- Código destacado -->
                    <div style="background: linear-gradient(135deg, #FFF3E0, #FFE0B2); padding:20px; border-radius:10px; text-align:center; margin-bottom:25px; border-left:5px solid #F2620F;">
                        <p style="margin:0; font-size:14px; font-weight:600; color:#666;">Código de Requisición</p>
                        <p style="margin:8px 0 0 0; font-size:28px; font-weight:bold; color:#F2620F; letter-spacing:1px;">' . htmlspecialchars($req['codigo']) . '</p>
                    </div>
                    
                    <!-- Detalles principales -->
                    <div style="background:#FFF9F0; padding:20px; border-radius:8px; border-left:4px solid #F2620F; margin-bottom:20px;">
                        <p style="margin:0 0 15px 0; font-size:16px; font-weight:bold; color:#F2620F;">📋 Detalles de la Solicitud</p>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <tr>
                                <td style="padding:8px 4px; width:40%; vertical-align:top;"><strong>Fecha Solicitud:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['fecha_solicitud']) . '</td>
                            </tr>
                            <tr style="background:#fff;">
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Cargo:</strong></td>
                                <td style="padding:8px 4px; font-weight:600; color:#F2620F;">' . htmlspecialchars($req['nombre_cargo']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Área:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['area']) . '</td>
                            </tr>
                            <tr style="background:#fff;">
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Ciudad:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['ciudad_ubicacion']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 4px; vertical-align:top;"><strong>N° Vacantes:</strong></td>
                                <td style="padding:8px 4px;"><strong style="color:#F2620F; font-size:16px;">' . htmlspecialchars($req['numero_vacantes']) . '</strong></td>
                            </tr>
                            <tr style="background:#fff;">
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Tipo Contrato:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['tipo_contrato']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Solicitado por:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['email_solicita']) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    ' . (!empty($req['observaciones_perfil']) ? '
                    <!-- Observaciones -->
                    <div style="margin:20px 0; padding:15px; background:#E3F2FD; border-left:4px solid #2196F3; border-radius:4px;">
                        <p style="margin:0 0 8px 0; font-weight:bold; color:#1976D2; font-size:14px;">💬 Observaciones del Perfil:</p>
                        <p style="margin:0; font-size:14px; line-height:1.6; color:#333;">' . nl2br(htmlspecialchars($req['observaciones_perfil'])) . '</p>
                    </div>
                    ' : '') . '
                    
                    ' . (!empty($req['otras_condiciones']) ? '
                    <!-- Otras condiciones -->
                    <div style="margin:20px 0; padding:15px; background:#FFF3E0; border-left:4px solid #FF9800; border-radius:4px;">
                        <p style="margin:0 0 8px 0; font-weight:bold; color:#F57C00; font-size:14px;">⚙️ Otras Condiciones:</p>
                        <p style="margin:0; font-size:14px; line-height:1.6; color:#333;">' . nl2br(htmlspecialchars($req['otras_condiciones'])) . '</p>
                    </div>
                    ' : '') . '
                  
                </div>
                
                <!-- Pie -->
                <div style="background:#f0f0f0; color:#777; text-align:center; padding:20px; font-size:12px; border-top:1px solid #ddd;">
                    <p style="margin:0 0 5px 0;">Este mensaje fue generado automáticamente por el Sistema de Requisiciones</p>
                    <p style="margin:0; font-weight:bold; color:#F2620F;">SANMARINO - Genética Avícola</p>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    /**
     * Plantilla HTML para notificación de cambio de estado (al solicitante)
     */
    private function plantillaCambioEstado($req, $nuevoEstado) {
        // Colores según estado
        $colores = [
            'Pendiente' => ['bg' => '#FFF3CD', 'border' => '#FFC107', 'gradient' => 'linear-gradient(135deg, #FFC107, #FF9800)', 'text' => '#856404'],
            'Aprobada' => ['bg' => '#D1E7DD', 'border' => '#198754', 'gradient' => 'linear-gradient(135deg, #28A745, #20C997)', 'text' => '#0A3622'],
            'En Proceso' => ['bg' => '#CFE2FF', 'border' => '#0D6EFD', 'gradient' => 'linear-gradient(135deg, #0D6EFD, #6610F2)', 'text' => '#084298'],
            'Cerrada' => ['bg' => '#E2E3E5', 'border' => '#6C757D', 'gradient' => 'linear-gradient(135deg, #6C757D, #495057)', 'text' => '#41464B']
        ];
        
        $color = $colores[$nuevoEstado] ?? $colores['Pendiente'];
        
        // Mensajes personalizados
        $mensajes = [
            'Pendiente' => 'Su requisición ha sido registrada correctamente en el sistema y está pendiente de revisión por parte del equipo de Recursos Humanos.',
            'Aprobada' => '¡Excelente noticia! Su requisición ha sido aprobada y está lista para iniciar el proceso de búsqueda y selección de candidatos.',
            'En Proceso' => '¡Tenemos buenas noticias! Se han encontrado candidato(s) potenciales para su vacante y están actualmente en proceso de evaluación y selección.',
            'Cerrada' => 'Su requisición ha sido cerrada exitosamente. El proceso de selección ha finalizado y se han tomado las acciones correspondientes.'
        ];
        
        $mensaje = $mensajes[$nuevoEstado] ?? 'El estado de su requisición ha sido actualizado en el sistema.';
        
        // Iconos según estado
        $iconos = [
            'Pendiente' => '⏳',
            'Aprobada' => '✅',
            'En Proceso' => '👥',
            'Cerrada' => '✔️'
        ];
        
        $icono = $iconos[$nuevoEstado] ?? '📋';
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">
            <div style="max-width:600px; margin:30px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                
                <!-- Encabezado -->
                <div style="background: ' . $color['gradient'] . '; color:#ffffff; padding:25px; text-align:center;">
                    <h1 style="margin:0; font-size:26px; font-weight:bold;">' . $icono . ' Actualización de Requisición</h1>
                    <p style="margin:10px 0 0 0; font-size:14px; opacity:0.9;">Sistema de Requisiciones SANMARINO</p>
                </div>
                
                <!-- Cuerpo -->
                <div style="padding:30px; color:#333333;">
                    <p style="font-size:16px; margin:0 0 10px 0; font-weight:600;">
                        Estimado(a) colaborador(a),
                    </p>
                    
                    <p style="font-size:15px; margin:0 0 25px 0; line-height:1.7;">
                        ' . $mensaje . '
                    </p>
                    
                    <!-- Estado Actual -->
                    <div style="background:' . $color['bg'] . '; 
                                padding:20px; 
                                border-radius:10px; 
                                border-left:5px solid ' . $color['border'] . '; 
                                margin:25px 0;
                                text-align:center;">
                        <p style="margin:0 0 10px 0; font-size:14px; font-weight:bold; color:' . $color['text'] . ';">
                            ESTADO ACTUAL
                        </p>
                        <p style="margin:0; font-size:28px; font-weight:bold; color:' . $color['border'] . '; letter-spacing:1px;">
                            ' . strtoupper($nuevoEstado) . '
                        </p>
                    </div>
                    
                    <!-- Detalles de la Requisición -->
                    <div style="background:#FFF9F0; padding:20px; border-radius:8px; border-left:4px solid #F2620F; margin-top:25px;">
                        <p style="margin:0 0 15px 0; font-size:15px; font-weight:bold; color:#F2620F;">
                            📋 DETALLES DE LA REQUISICIÓN
                        </p>
                        <table style="width:100%; border-collapse:collapse; font-size:14px;">
                            <tr>
                                <td style="padding:8px 4px; width:35%; vertical-align:top;"><strong>Código:</strong></td>
                                <td style="padding:8px 4px; font-weight:600; color:#F2620F; font-size:15px;">' . htmlspecialchars($req['codigo']) . '</td>
                            </tr>
                            <tr style="background:#fff;">
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Cargo:</strong></td>
                                <td style="padding:8px 4px; font-weight:600;">' . htmlspecialchars($req['nombre_cargo']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Área:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['area']) . '</td>
                            </tr>
                            <tr style="background:#fff;">
                                <td style="padding:8px 4px; vertical-align:top;"><strong>N° Vacantes:</strong></td>
                                <td style="padding:8px 4px;"><strong style="color:#F2620F;">' . htmlspecialchars($req['numero_vacantes']) . '</strong></td>
                            </tr>
                            <tr>
                                <td style="padding:8px 4px; vertical-align:top;"><strong>Fecha Solicitud:</strong></td>
                                <td style="padding:8px 4px;">' . htmlspecialchars($req['fecha_solicitud']) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    ' . ($nuevoEstado == 'En Proceso' ? '
                    <!-- Información adicional para "En Proceso" -->
                    <div style="margin-top:20px; padding:18px; background:#E8F5E9; border-left:4px solid #4CAF50; border-radius:4px;">
                        <p style="margin:0 0 10px 0; font-weight:bold; color:#2E7D32; font-size:15px;">
                            👥 Próximos Pasos
                        </p>
                        <p style="margin:0; font-size:14px; line-height:1.7; color:#333;">
                            El equipo de Recursos Humanos está gestionando activamente los candidatos seleccionados. 
                            Se están realizando las evaluaciones correspondientes y le mantendremos informado sobre 
                            el avance del proceso. Si tiene alguna consulta, no dude en contactarnos.
                        </p>
                    </div>
                    ' : '') . '
                    
                    ' . ($nuevoEstado == 'Aprobada' ? '
                    <!-- Información adicional para "Aprobada" -->
                    <div style="margin-top:20px; padding:18px; background:#E3F2FD; border-left:4px solid #2196F3; border-radius:4px;">
                        <p style="margin:0 0 10px 0; font-weight:bold; color:#1565C0; font-size:15px;">
                            🎯 Siguiente Fase
                        </p>
                        <p style="margin:0; font-size:14px; line-height:1.7; color:#333;">
                            El proceso de búsqueda y selección iniciará próximamente. El área de Recursos Humanos 
                            comenzará a publicar la vacante y buscar candidatos que cumplan con el perfil requerido. 
                            Le mantendremos actualizado sobre los avances.
                        </p>
                    </div>
                    ' : '') . '
                    
                    <p style="margin:30px 0 0 0; font-size:13px; color:#666; text-align:center; line-height:1.6;">
                        Si tiene alguna pregunta o necesita más información,<br>
                        no dude en contactar al área de <strong>Recursos Humanos</strong>.
                    </p>
                </div>
                
                <!-- Pie -->
                <div style="background:#f0f0f0; color:#777; text-align:center; padding:20px; font-size:12px; border-top:1px solid #ddd;">
                    <p style="margin:0 0 5px 0;">Este mensaje fue generado automáticamente. Por favor no responda a este correo.</p>
                    <p style="margin:0; font-weight:bold; color:#F2620F;">Sistema de Requisiciones - SANMARINO</p>
                </div>
                
            </div>
        </body>
        </html>';
    }
}
?>
