<?php

namespace App\Services;

use InvalidArgumentException;

class TaxService
{
    /**
     * Obtiene la tasa decimal de IGV según el tipo de afectación (Catálogo 07 SUNAT).
     *
     * - gravado   (Código 10): 18% (0.18) por defecto en config('erp.igv_rate')
     * - exonerado (Código 20): 0% (0.00)
     * - inafecto  (Código 30): 0% (0.00)
     */
    public function getRateForTaxType(string $taxType): float
    {
        $normalized = strtolower(trim($taxType));

        if (!in_array($normalized, ['gravado', 'exonerado', 'inafecto'], true)) {
            throw new InvalidArgumentException("Tipo de afectación tributaria inválido: '{$taxType}'. Use 'gravado', 'exonerado' o 'inafecto'.");
        }

        if ($normalized === 'gravado') {
            return (float) config('erp.igv_rate', 0.18);
        }

        return 0.00;
    }

    /**
     * Calcula los importes tributarios de una línea de venta o compra con redondeo
     * financiero consistente (PHP_ROUND_HALF_UP a 2 decimales para importes monetarios
     * y 4 decimales para valor/precio unitario).
     *
     * Garantiza invariante contable por línea:
     *   tax_base + tax_amount === line_total
     *
     * @param float $catalogUnitAmount Precio o valor unitario configurado en catálogo
     * @param int $quantity Cantidad de unidades (entero > 0)
     * @param string $taxType 'gravado' | 'exonerado' | 'inafecto'
     * @param bool|null $pricesIncludeTax Si es null, usa config('erp.prices_include_igv')
     * @param float $discountAmount Descuento aplicado a la línea (en la misma modalidad que catalogUnitAmount)
     * @return array{
     *     tax_type: string,
     *     tax_rate: float,
     *     quantity: int,
     *     unit_value: float,
     *     unit_price: float,
     *     discount_amount: float,
     *     tax_base: float,
     *     tax_amount: float,
     *     subtotal: float,
     *     line_total: float
     * }
     */
    public function calculateLine(
        float $catalogUnitAmount,
        int $quantity,
        string $taxType = 'gravado',
        ?bool $pricesIncludeTax = null,
        float $discountAmount = 0.00
    ): array {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor a cero.');
        }
        if ($catalogUnitAmount < 0) {
            throw new InvalidArgumentException('El importe unitario no puede ser negativo.');
        }
        if ($discountAmount < 0) {
            throw new InvalidArgumentException('El descuento de línea no puede ser negativo.');
        }

        $taxType = strtolower(trim($taxType ?: 'gravado'));
        $rate = $this->getRateForTaxType($taxType);
        $includesTax = $pricesIncludeTax ?? (bool) config('erp.prices_include_igv', true);

        $grossLine = round($catalogUnitAmount * $quantity, 2, PHP_ROUND_HALF_UP);
        $discountAmount = round($discountAmount, 2, PHP_ROUND_HALF_UP);

        if ($discountAmount > $grossLine) {
            throw new InvalidArgumentException('El descuento no puede superar el importe bruto de la línea.');
        }

        if ($includesTax) {
            // El precio de catálogo ya incluye IGV (o es 0% si es exonerado/inafecto)
            $unitPrice = round($catalogUnitAmount, 2, PHP_ROUND_HALF_UP);
            $unitValue = $rate > 0
                ? round($catalogUnitAmount / (1 + $rate), 4, PHP_ROUND_HALF_UP)
                : round($catalogUnitAmount, 4, PHP_ROUND_HALF_UP);

            $lineTotal = round($grossLine - $discountAmount, 2, PHP_ROUND_HALF_UP);

            if ($rate > 0) {
                $taxBase = round($lineTotal / (1 + $rate), 2, PHP_ROUND_HALF_UP);
                // Ajustar tax_amount por diferencia para garantizar tax_base + tax_amount == line_total
                $taxAmount = round($lineTotal - $taxBase, 2, PHP_ROUND_HALF_UP);
            } else {
                $taxBase = $lineTotal;
                $taxAmount = 0.00;
            }
        } else {
            // El importe de catálogo es Valor Unitario (sin IGV)
            $unitValue = round($catalogUnitAmount, 4, PHP_ROUND_HALF_UP);
            $unitPrice = round($catalogUnitAmount * (1 + $rate), 2, PHP_ROUND_HALF_UP);

            $taxBase = round($grossLine - $discountAmount, 2, PHP_ROUND_HALF_UP);
            $taxAmount = $rate > 0
                ? round($taxBase * $rate, 2, PHP_ROUND_HALF_UP)
                : 0.00;
            $lineTotal = round($taxBase + $taxAmount, 2, PHP_ROUND_HALF_UP);
        }

        return [
            'tax_type' => $taxType,
            'tax_rate' => round($rate * 100, 2, PHP_ROUND_HALF_UP), // Ej: 18.00 o 0.00
            'quantity' => $quantity,
            'unit_value' => $unitValue,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'tax_base' => $taxBase,
            'tax_amount' => $taxAmount,
            'subtotal' => $lineTotal, // Mantenido igual a line_total por compatibilidad con vistas e integraciones previas
            'line_total' => $lineTotal,
        ];
    }

    /**
     * Consolida múltiples líneas ya calculadas en los totales de cabecera de venta o compra.
     *
     * Garantiza invariante contable de documento:
     *   subtotal (valor venta neto: taxable + exempt + unaffected) + tax === total
     *
     * @param array<int, array> $calculatedLines
     * @return array{
     *     taxable_amount: float,
     *     exempt_amount: float,
     *     unaffected_amount: float,
     *     discount_total: float,
     *     subtotal: float,
     *     tax: float,
     *     total: float
     * }
     */
    public function summarizeTotals(array $calculatedLines): array
    {
        $taxableAmount = 0.00;
        $exemptAmount = 0.00;
        $unaffectedAmount = 0.00;
        $discountTotal = 0.00;
        $taxTotal = 0.00;
        $grandTotal = 0.00;

        foreach ($calculatedLines as $line) {
            $taxType = $line['tax_type'] ?? 'gravado';
            $base = (float) ($line['tax_base'] ?? 0.00);
            $tax = (float) ($line['tax_amount'] ?? 0.00);
            $discount = (float) ($line['discount_amount'] ?? 0.00);
            $lineTotal = (float) ($line['line_total'] ?? 0.00);

            if ($taxType === 'gravado') {
                $taxableAmount += $base;
            } elseif ($taxType === 'exonerado') {
                $exemptAmount += $base;
            } else {
                $unaffectedAmount += $base;
            }

            $discountTotal += $discount;
            $taxTotal += $tax;
            $grandTotal += $lineTotal;
        }

        $taxableAmount = round($taxableAmount, 2, PHP_ROUND_HALF_UP);
        $exemptAmount = round($exemptAmount, 2, PHP_ROUND_HALF_UP);
        $unaffectedAmount = round($unaffectedAmount, 2, PHP_ROUND_HALF_UP);
        $discountTotal = round($discountTotal, 2, PHP_ROUND_HALF_UP);
        $taxTotal = round($taxTotal, 2, PHP_ROUND_HALF_UP);
        $grandTotal = round($grandTotal, 2, PHP_ROUND_HALF_UP);

        $netSaleValue = round($taxableAmount + $exemptAmount + $unaffectedAmount, 2, PHP_ROUND_HALF_UP);

        return [
            'taxable_amount' => $taxableAmount,
            'exempt_amount' => $exemptAmount,
            'unaffected_amount' => $unaffectedAmount,
            'discount_total' => $discountTotal,
            'subtotal' => $netSaleValue,
            'tax' => $taxTotal,
            'total' => $grandTotal,
        ];
    }
}
