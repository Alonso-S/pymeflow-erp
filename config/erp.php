<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración Monetaria y Tributaria — PymeFlow ERP (Perú)
    |--------------------------------------------------------------------------
    |
    | Define los parámetros fiscales de referencia basados en la normativa de
    | SUNAT (Perú) para operaciones comerciales internas de PYMEs.
    |
    | Nota: La tasa general del 18% comprende el Impuesto General a las Ventas
    | (IGV) más el Impuesto de Promoción Municipal (IPM).
    | Catálogo N.° 07 SUNAT (Códigos de Tipo de Afectación del IGV):
    | - 'gravado'   (Código 10: Gravado - Operación Onerosa, aplica 18%)
    | - 'exonerado' (Código 20: Exonerado - Operación Onerosa, tasa 0%)
    | - 'inafecto'  (Código 30: Inafecto - Operación Onerosa, tasa 0%)
    |
    */

    'currency_code' => env('ERP_CURRENCY_CODE', 'PEN'),
    'currency_symbol' => env('ERP_CURRENCY_SYMBOL', 'S/'),

    // Tasa general de referencia del IGV en Perú (18% = 0.18)
    'igv_rate' => (float) env('ERP_IGV_RATE', 0.18),

    // En comercio minorista y PYMEs peruanas, los precios de catálogo suelen
    // incluir el IGV por defecto. Puede alternarse a false si el catálogo maneja
    // valores unitarios antes de impuestos (B2B mayorista).
    'prices_include_igv' => (bool) env('ERP_PRICES_INCLUDE_IGV', true),

    'tax_types' => [
        'gravado' => [
            'code' => '10',
            'label' => 'Gravado (18% IGV)',
            'rate' => 0.18,
        ],
        'exonerado' => [
            'code' => '20',
            'label' => 'Exonerado (0% - Apéndice I/II)',
            'rate' => 0.00,
        ],
        'inafecto' => [
            'code' => '30',
            'label' => 'Inafecto (0% - Art. 2 Ley IGV)',
            'rate' => 0.00,
        ],
    ],

    'document_types' => [
        'DNI' => 'DNI (8 dígitos)',
        'RUC' => 'RUC (11 dígitos)',
        'CE' => 'Carné de Extranjería',
        'PASAPORTE' => 'Pasaporte',
    ],

    'payment_methods' => [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta Débito / Crédito',
        'transferencia' => 'Transferencia Bancaria',
        'yape' => 'Yape (Billetera Digital)',
        'plin' => 'Plin (Billetera Digital)',
    ],
];
