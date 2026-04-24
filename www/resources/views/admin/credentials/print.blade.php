<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressão de Credenciais</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 20px;
        }

        .print-actions {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .credential-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .credential-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            page-break-inside: avoid;
        }

        .qr-container {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            background: #fff;
            padding: 5px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .qr-container img {
            width: 100%;
            height: 100%;
        }

        .credential-info {
            flex-grow: 1;
        }

        .device-type {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .device-name {
            font-size: 1.5rem;
            font-weight: 900;
            color: #212529;
            margin-bottom: 5px;
            line-height: 1.1;
        }

        .device-location {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 10px;
        }

        .device-id {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            color: #6c757d;
            word-break: break-all;
        }

        @media print {
            @page {
                margin: 1cm;
                size: A4 portrait;
            }
            body {
                background-color: white;
                padding: 0;
            }
            .print-actions {
                display: none !important;
            }
            .credential-card {
                border-color: #000;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <div>
            <h5 class="mb-0 fw-bold">Modo de Impressão</h5>
            <p class="text-secondary mb-0 small">Ajuste as configurações da impressora para folha A4 e sem margens extras.</p>
        </div>
        <button class="btn btn-primary px-4 py-2" onclick="window.print()">
            <i class="bi bi-printer me-2"></i> Imprimir Agora
        </button>
    </div>

    <div class="credential-grid">
        @foreach($devicesToPrint as $index => $device)
        <div class="credential-card">
            <div class="qr-container" id="qr_{{ $index }}" data-identifier="{{ $device['identifier'] }}"></div>
            <div class="credential-info">
                <div class="device-type">{{ $device['type'] }}</div>
                <div class="device-name">{{ $device['name'] }}</div>
                <div class="device-location"><i class="bi bi-geo-alt-fill me-1 text-secondary"></i>{{ $device['location'] }}</div>
                <div class="device-id">{{ $device['identifier'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const containers = document.querySelectorAll('.qr-container');
            containers.forEach(container => {
                const identifier = container.getAttribute('data-identifier');
                new QRCode(container, {
                    text: identifier,
                    width: 110,
                    height: 110,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            });
            
            // Automatically open print dialog after a short delay to ensure QRs are rendered
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
