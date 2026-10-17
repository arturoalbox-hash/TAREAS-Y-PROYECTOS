<?php
/**
 * Envío de correos sin librerías externas.
 * config('mail')['method']:
 *   'log'  -> no envía nada: guarda el correo en data/correos.log (para probar en local)
 *   'mail' -> usa mail() de PHP (funciona en la mayoría de hostings compartidos)
 *   'smtp' -> se conecta a un servidor SMTP (Gmail, el correo de tu hosting, Brevo…)
 */
function send_mail(string $to, string $subject, string $text): bool
{
    $m = config('mail', ['method' => 'log']);
    $from = $m['from'] ?? config('contact_email');
    $fromName = $m['from_name'] ?? config('app_name');

    $encSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encFrom = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>';
    $headers = [
        'From: ' . $encFrom,
        'Reply-To: ' . config('contact_email'),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Date: ' . date('r'),
    ];

    switch ($m['method'] ?? 'log') {
        case 'mail':
            return @mail($to, $encSubject, $text, implode("\r\n", $headers), '-f' . $from);

        case 'smtp':
            try {
                return smtp_send($m, $from, $to, array_merge(['To: ' . $to, 'Subject: ' . $encSubject], $headers), $text);
            } catch (Throwable $e) {
                error_log('CartaFácil SMTP: ' . $e->getMessage());
                return false;
            }

        default: // log
            $dir = __DIR__ . '/../data';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $entry = "==================== " . date('Y-m-d H:i:s') . " ====================\n"
                   . "Para: $to\nAsunto: $subject\n\n$text\n\n";
            return file_put_contents($dir . '/correos.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}

/** Cliente SMTP mínimo con SSL (465) o STARTTLS (587) y AUTH LOGIN */
function smtp_send(array $m, string $from, string $to, array $headers, string $body): bool
{
    $host = $m['host'];
    $port = (int)($m['port'] ?? 587);
    $secure = $m['secure'] ?? ($port === 465 ? 'ssl' : 'tls');
    $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;

    $fp = @stream_socket_client($remote, $errno, $errstr, 15);
    if (!$fp) throw new RuntimeException("No se pudo conectar a $host:$port ($errstr)");
    stream_set_timeout($fp, 15);

    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function (string $c, array $ok) use ($fp, $read): string {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $res = $read();
        if (!in_array((int)substr($res, 0, 3), $ok, true)) {
            throw new RuntimeException('SMTP: respuesta inesperada a "' . (str_starts_with($c, 'AUTH') || strlen($c) > 60 ? '…' : $c) . '": ' . trim($res));
        }
        return $res;
    };

    $ehloHost = parse_url(config('base_url'), PHP_URL_HOST) ?: 'localhost';
    $cmd('', [220]);
    $cmd('EHLO ' . $ehloHost, [250]);
    if ($secure === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('No se pudo activar TLS');
        }
        $cmd('EHLO ' . $ehloHost, [250]);
    }
    if (!empty($m['user'])) {
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($m['user']), [334]);
        $cmd(base64_encode($m['pass']), [235]);
    }
    $cmd('MAIL FROM:<' . $from . '>', [250]);
    $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    $cmd('DATA', [354]);
    // Normaliza saltos de línea y escapa líneas que empiezan por punto
    $body = preg_replace("/\r?\n/", "\r\n", $body);
    $body = preg_replace('/^\./m', '..', $body);
    $cmd(implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.", [250]);
    $cmd('QUIT', [221]);
    fclose($fp);
    return true;
}
