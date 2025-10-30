<?php

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

if (!class_exists(BarcodeGeneratorPNG::class)) {
    $barcodeLibPath = APPPATH . 'Libraries/Barcode/';
    $fallbackFiles = [
        'Barcode.php',
        'BarcodeBar.php',
        'BarcodeGenerator.php',
        'BarcodeGeneratorPNG.php',
        'BarcodeGeneratorSVG.php',
        'Renderers/RendererInterface.php',
        'Renderers/PngRenderer.php',
        'Renderers/SvgRenderer.php',
        'Types/TypeInterface.php',
        'Types/TypeCode128.php',
        'Helpers/ColorHelper.php',
    ];

    foreach ($fallbackFiles as $file) {
        $fullPath = $barcodeLibPath . $file;
        if (is_file($fullPath)) {
            require_once $fullPath;
        }
    }
}

if (!function_exists('generate_barcode')) {
    function generate_barcode($code, $type = 'C128', $width = 2, $height = 30)
    {
        $generatorType = resolve_barcode_type($type);

        try {
            $generator = new BarcodeGeneratorPNG();
            $data = $generator->getBarcode($code, $generatorType, $width, $height);

            return [
                'data' => $data,
                'mime' => 'image/png',
            ];
        } catch (\Throwable $e) {
            log_message('warning', 'Falling back to SVG barcode rendering: {exception}', ['exception' => $e]);

            $svgGenerator = new BarcodeGeneratorSVG();
            $svg = $svgGenerator->getBarcode($code, $generatorType, $width, $height);

            return [
                'data' => $svg,
                'mime' => 'image/svg+xml',
            ];
        }
    }
}

if (!function_exists('resolve_barcode_type')) {
    function resolve_barcode_type($type)
    {
        $typeMap = [
            'C39' => BarcodeGeneratorPNG::TYPE_CODE_39,
            'C39+' => BarcodeGeneratorPNG::TYPE_CODE_39_CHECKSUM,
            'C39E' => BarcodeGeneratorPNG::TYPE_CODE_39E,
            'C39E+' => BarcodeGeneratorPNG::TYPE_CODE_39E_CHECKSUM,
            'C93' => BarcodeGeneratorPNG::TYPE_CODE_93,
            'S25' => BarcodeGeneratorPNG::TYPE_STANDARD_2_5,
            'S25+' => BarcodeGeneratorPNG::TYPE_STANDARD_2_5_CHECKSUM,
            'I25' => BarcodeGeneratorPNG::TYPE_INTERLEAVED_2_5,
            'I25+' => BarcodeGeneratorPNG::TYPE_INTERLEAVED_2_5_CHECKSUM,
            'C128A' => BarcodeGeneratorPNG::TYPE_CODE_128_A,
            'C128B' => BarcodeGeneratorPNG::TYPE_CODE_128_B,
            'EAN2' => BarcodeGeneratorPNG::TYPE_EAN_2,
            'EAN5' => BarcodeGeneratorPNG::TYPE_EAN_5,
            'EAN8' => BarcodeGeneratorPNG::TYPE_EAN_8,
            'EAN13' => BarcodeGeneratorPNG::TYPE_EAN_13,
            'UPCA' => BarcodeGeneratorPNG::TYPE_UPC_A,
            'UPCE' => BarcodeGeneratorPNG::TYPE_UPC_E,
        ];

        $lookupKey = strtoupper((string) $type);

        return $typeMap[$lookupKey] ?? BarcodeGeneratorPNG::TYPE_CODE_128;
    }
}

if (!function_exists('barcode_image')) {
    function barcode_image($code, $type = 'C128', $width = 2, $height = 30)
    {
        $payload = generate_barcode($code, $type, $width, $height);

        if (is_array($payload)) {
            $data = $payload['data'];
            $mime = $payload['mime'];
        } else {
            $data = $payload;
            $mime = 'image/png';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
