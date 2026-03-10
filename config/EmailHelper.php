<?php
// Config/EmailHelper.php

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
        $this->mail->SMTPAuth   = true;
        $this->mail->SMTPSecure = "ssl";
        $this->mail->Host       = "smtp.gmail.com";
        $this->mail->Port       = 465;
        $this->mail->Username   = 'dotacionsa@gmail.com';
        $this->mail->Password   = 'rsrbseqfdtujmuxl';
        $this->mail->SetFrom('dotacionsa@gmail.com', 'DevolutionSync - Sanmarino');
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->SMTPDebug  = 2;
        $this->mail->Debugoutput = function($str, $level) {
            error_log("[PHPMailer DEBUG] $str");
        };
    }

    // =========================================================
    // 1. Notificar al ADMIN cuando se registra una devolución
    // =========================================================
    public function notificarNuevaDevolucion($devolucion) {
        try {
            $this->mail->ClearAddresses();
            $this->mail->AddAddress($this->emailAdmin);
            $this->mail->isHTML(true);
            $this->mail->Subject = "🆕 Nueva Devolución Registrada - #" . $devolucion['id'];
            $this->mail->Body    = $this->plantillaNuevaDevolucion($devolucion);

            if (!$this->mail->Send()) {
                error_log("Error al enviar correo de nueva devolución: " . $this->mail->ErrorInfo);
                return false;
            }

            error_log("✅ Correo de nueva devolución enviado al admin: " . $this->emailAdmin);
            return true;

        } catch (Exception $e) {
            error_log("❌ Excepción al notificar nueva devolución: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // 2. Notificar al SOLICITANTE cuando se aprueba o rechaza
    // =========================================================
    public function notificarEstadoDevolucion($devolucion, $estado, $observacionAdmin) {
        try {
            if (empty($devolucion['correo_solicitante'])) {
                error_log("No hay correo del solicitante para la devolución #" . $devolucion['id']);
                return false;
            }

            $this->mail->ClearAddresses();
            $this->mail->AddAddress($devolucion['correo_solicitante']);
            $this->mail->isHTML(true);

            $asunto = ($estado === 'aprobado')
                ? "✅ Devolución APROBADA - #" . $devolucion['id']
                : "❌ Devolución RECHAZADA - #" . $devolucion['id'];

            $this->mail->Subject = $asunto;
            $this->mail->Body    = $this->plantillaEstadoDevolucion($devolucion, $estado, $observacionAdmin);

            if (!$this->mail->Send()) {
                error_log("Error al enviar correo de estado: " . $this->mail->ErrorInfo);
                return false;
            }

            error_log("✅ Correo de estado enviado a: " . $devolucion['correo_solicitante']);
            return true;

        } catch (Exception $e) {
            error_log("❌ Excepción al notificar estado devolución: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // PLANTILLA: Nueva devolución (para el Admin)
    // =========================================================
    private function plantillaNuevaDevolucion($d) {
        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">
            <div style="max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.15);">

                <!-- Header -->
                <div style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:25px;text-align:center;">
                    <h1 style="margin:0;font-size:24px;">🆕 Nueva Devolución Registrada</h1>
                    <p style="margin:8px 0 0;font-size:13px;opacity:0.9;">DevolutionSync - Sanmarino</p>
                </div>

                <!-- Body -->
                <div style="padding:30px;color:#333;">
                    <p style="font-size:15px;margin:0 0 20px;">Se ha registrado una nueva solicitud de devolución que requiere su revisión:</p>

                    <!-- ID destacado -->
                    <div style="background:#f0f0ff;padding:15px;border-radius:8px;text-align:center;margin-bottom:20px;border-left:5px solid #667eea;">
                        <p style="margin:0;font-size:13px;color:#666;">ID de Devolución</p>
                        <p style="margin:6px 0 0;font-size:28px;font-weight:bold;color:#667eea;">#' . htmlspecialchars($d['id']) . '</p>
                    </div>

                    <!-- Detalles -->
                    <div style="background:#fafafa;padding:20px;border-radius:8px;border-left:4px solid #ff8c00;">
                        <p style="margin:0 0 12px;font-weight:bold;color:#ff8c00;font-size:15px;">📋 Detalles de la Solicitud</p>
                        <table style="width:100%;border-collapse:collapse;font-size:14px;">
                            <tr><td style="padding:7px 4px;width:40%;"><strong>Cliente:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['nombre_cliente']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>NIT:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['nit']) . '</td></tr>
                            <tr><td style="padding:7px 4px;"><strong>Producto:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['descripcion_producto']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Motivo:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['motivo']) . '</td></tr>
                            <tr><td style="padding:7px 4px;"><strong>Cantidad UND:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['cantidad_und']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Cantidad KG:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['cantidad_kg']) . '</td></tr>
                            <tr><td style="padding:7px 4px;"><strong>Registrado por:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['usuario_creador']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Observaciones:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['observacion']) . '</td></tr>
                        </table>
                    </div>

                    <p style="margin:25px 0 0;font-size:13px;color:#666;text-align:center;">
                        Ingrese al sistema para aprobar o rechazar esta solicitud.
                    </p>
                </div>

                <!-- Footer -->
                <div style="background:#f0f0f0;color:#888;text-align:center;padding:18px;font-size:12px;border-top:1px solid #ddd;">
                    <p style="margin:0 0 4px;">Mensaje generado automáticamente por DevolutionSync</p>
                    <p style="margin:0;font-weight:bold;color:#ff8c00;">SANMARINO - Genética Avícola</p>
                </div>
            </div>
        </body>
        </html>';
    }

    // =========================================================
    // PLANTILLA: Estado devolución (para el Solicitante)
    // =========================================================
    private function plantillaEstadoDevolucion($d, $estado, $observacionAdmin) {
        $esAprobado = ($estado === 'aprobado');

        $gradient  = $esAprobado ? 'linear-gradient(135deg,#28a745,#20c997)' : 'linear-gradient(135deg,#dc3545,#c82333)';
        $colorBorde = $esAprobado ? '#28a745' : '#dc3545';
        $bgEstado  = $esAprobado ? '#d4edda' : '#f8d7da';
        $txtEstado = $esAprobado ? '#155724' : '#721c24';
        $icono     = $esAprobado ? '✅' : '❌';
        $estadoTexto = $esAprobado ? 'APROBADA' : 'RECHAZADA';

        $mensajePrincipal = $esAprobado
            ? 'Su solicitud de devolución ha sido <strong>aprobada</strong> por el administrador. Puede proceder con los pasos correspondientes.'
            : 'Su solicitud de devolución ha sido <strong>rechazada</strong> por el administrador. Por favor revise las observaciones a continuación.';

        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">
            <div style="max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.15);">

                <!-- Header -->
                <div style="background:' . $gradient . ';color:#fff;padding:25px;text-align:center;">
                    <h1 style="margin:0;font-size:24px;">' . $icono . ' Actualización de Devolución</h1>
                    <p style="margin:8px 0 0;font-size:13px;opacity:0.9;">DevolutionSync - Sanmarino</p>
                </div>

                <!-- Body -->
                <div style="padding:30px;color:#333;">
                    <p style="font-size:15px;margin:0 0 20px;line-height:1.7;">' . $mensajePrincipal . '</p>

                    <!-- Estado destacado -->
                    <div style="background:' . $bgEstado . ';padding:18px;border-radius:8px;text-align:center;margin-bottom:20px;border-left:5px solid ' . $colorBorde . ';">
                        <p style="margin:0;font-size:13px;font-weight:bold;color:' . $txtEstado . ';">ESTADO DE SU SOLICITUD</p>
                        <p style="margin:8px 0 0;font-size:26px;font-weight:bold;color:' . $colorBorde . ';">' . $icono . ' ' . $estadoTexto . '</p>
                    </div>

                    <!-- Detalles devolución -->
                    <div style="background:#fafafa;padding:20px;border-radius:8px;border-left:4px solid #ff8c00;margin-bottom:20px;">
                        <p style="margin:0 0 12px;font-weight:bold;color:#ff8c00;font-size:15px;">📦 Datos de la Devolución</p>
                        <table style="width:100%;border-collapse:collapse;font-size:14px;">
                            <tr><td style="padding:7px 4px;width:40%;"><strong>ID Devolución:</strong></td><td style="padding:7px 4px;font-weight:bold;color:#667eea;">#' . htmlspecialchars($d['id']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Cliente:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['nombre_cliente']) . '</td></tr>
                            <tr><td style="padding:7px 4px;"><strong>Producto:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['descripcion_producto']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Motivo:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['motivo']) . '</td></tr>
                            <tr><td style="padding:7px 4px;"><strong>Cantidad UND:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['cantidad_und']) . '</td></tr>
                            <tr style="background:#fff;"><td style="padding:7px 4px;"><strong>Cantidad KG:</strong></td><td style="padding:7px 4px;">' . htmlspecialchars($d['cantidad_kg']) . '</td></tr>
                        </table>
                    </div>

                    <!-- Observación del admin -->
                    <div style="background:#e9ecef;padding:18px;border-radius:8px;border-left:4px solid #667eea;">
                        <p style="margin:0 0 8px;font-weight:bold;color:#667eea;font-size:14px;">💬 Observación del Administrador:</p>
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#333;">' . htmlspecialchars($observacionAdmin) . '</p>
                    </div>

                    <p style="margin:25px 0 0;font-size:13px;color:#666;text-align:center;">
                        Si tiene alguna duda, comuníquese con el administrador del sistema.
                    </p>
                </div>

                <!-- Footer -->
                <div style="background:#f0f0f0;color:#888;text-align:center;padding:18px;font-size:12px;border-top:1px solid #ddd;">
                    <p style="margin:0 0 4px;">Mensaje generado automáticamente por DevolutionSync</p>
                    <p style="margin:0;font-weight:bold;color:#ff8c00;">SANMARINO - Genética Avícola</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
?>