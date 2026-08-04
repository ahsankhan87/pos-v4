<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ZatcaInvoiceServiceTest extends CIUnitTestCase
{
    public function testNormalizeCrnValueTruncatesToTenDigits(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('normalizeCrnValue');
        $method->setAccessible(true);

        $this->assertSame('1234567890', $method->invoke($service, '123456789012345'));
        $this->assertSame('0001234567', $method->invoke($service, '1234567'));
        $this->assertSame('1001001000', $method->invoke($service, ''));
    }

    public function testEvaluateSubmissionResponseTreatsWarningsAsSuccess(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('evaluateSubmissionResponse');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'validationResults' => [
                'status' => 'WARNING',
                'warningMessages' => [['code' => 'BR-KSA-F-08', 'message' => 'warning']],
                'errorMessages' => [],
            ],
            'clearanceStatus' => 'CLEARED',
        ], 'standard');

        $this->assertTrue($result['success']);
        $this->assertSame('cleared', $result['status']);
        $this->assertTrue($result['has_warnings']);
        $this->assertSame('warning', $result['flash_type']);
    }

    public function testBuildBasicAuthorizationHeaderTrimsCredentials(): void
    {
        $client = new App\Services\ZatcaApiClient();
        $reflector = new ReflectionClass($client);
        $method = $reflector->getMethod('buildBasicAuthorizationHeader');
        $method->setAccessible(true);

        $header = $method->invoke($client, ' token ', ' secret ');

        $this->assertSame('Basic ' . base64_encode('token:secret'), $header);
    }

    public function testNormalizeSaleIssuedAtUsesUtcTimestamp(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('normalizeSaleIssuedAt');
        $method->setAccessible(true);

        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Riyadh');

        try {
            $result = $method->invoke($service, '2026-08-03 12:34:56');
        } finally {
            date_default_timezone_set($previousTimezone);
        }

        $this->assertSame('2026-08-03', $result['date']);
        $this->assertSame('09:34:56', $result['time']);
        $this->assertSame('2026-08-03T09:34:56', $result['datetime']);
    }

    public function testBuildSaleXmlIncludesBuyerNameAndAddressFields(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('buildSaleXml');
        $method->setAccessible(true);

        $xml = $method->invoke($service, [
            'uuid' => 'test-uuid',
            'invoice_no' => 'INV-1',
            'issue_date' => '2026-08-02',
            'issue_time' => '12:34:56',
            'type_code' => '388',
            'sub_type' => '0100000',
            'seller_name' => 'Test Seller',
            'seller_vat' => '123456789012345',
            'seller_address' => 'Riyadh',
            'seller_building_number' => '1234',
            'seller_city_subdivision_name' => 'Al-Murabba',
            'seller_city_name' => 'Riyadh',
            'seller_postal_code' => '12345',
            'seller_country' => 'SA',
            'buyer_name' => '',
            'buyer_vat' => '',
            'buyer_address' => '',
            'invoice_flavor' => 'standard',
            'icv' => 1,
            'pih' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'rows' => [[
                'name' => 'Item',
                'qty' => 1,
                'line_subtotal' => 100,
                'line_tax' => 15,
                'line_total' => 115,
                'unit_price' => 100,
            ]],
            'taxable_total' => 100,
            'tax_total' => 15,
            'payable_total' => 115,
        ]);

        $this->assertStringContainsString('<cbc:Name>Customer</cbc:Name>', $xml);
        $this->assertStringContainsString('<cbc:StreetName>Street</cbc:StreetName>', $xml);
        $this->assertStringContainsString('<cbc:BuildingNumber>1</cbc:BuildingNumber>', $xml);
        $this->assertStringContainsString('<cbc:CityName>Riyadh</cbc:CityName>', $xml);
        $this->assertStringContainsString('<cbc:PostalZone>00000</cbc:PostalZone>', $xml);
    }

    public function testCreditAndDebitNotesIncludeDiscrepancyResponseReason(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('buildSaleXml');
        $method->setAccessible(true);

        $creditXml = $method->invoke($service, [
            'uuid' => 'test-uuid',
            'invoice_no' => 'CRN-1',
            'issue_date' => '2026-08-02',
            'issue_time' => '12:34:56',
            'type_code' => '381',
            'sub_type' => '0100000',
            'seller_name' => 'Test Seller',
            'seller_vat' => '123456789012345',
            'seller_address' => 'Riyadh',
            'seller_building_number' => '1234',
            'seller_city_subdivision_name' => 'Al-Murabba',
            'seller_city_name' => 'Riyadh',
            'seller_postal_code' => '12345',
            'seller_country' => 'SA',
            'buyer_name' => 'Customer',
            'buyer_vat' => '123456789012345',
            'buyer_address' => 'Street',
            'invoice_flavor' => 'standard',
            'icv' => 1,
            'pih' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'rows' => [[
                'name' => 'Item',
                'qty' => 1,
                'line_subtotal' => 100,
                'line_tax' => 15,
                'line_total' => 115,
                'unit_price' => 100,
            ]],
            'taxable_total' => 100,
            'tax_total' => 15,
            'payable_total' => 115,
        ]);

        $debitXml = $method->invoke($service, [
            'uuid' => 'test-uuid-2',
            'invoice_no' => 'DBN-1',
            'issue_date' => '2026-08-02',
            'issue_time' => '12:34:56',
            'type_code' => '383',
            'sub_type' => '0100000',
            'seller_name' => 'Test Seller',
            'seller_vat' => '123456789012345',
            'seller_address' => 'Riyadh',
            'seller_building_number' => '1234',
            'seller_city_subdivision_name' => 'Al-Murabba',
            'seller_city_name' => 'Riyadh',
            'seller_postal_code' => '12345',
            'seller_country' => 'SA',
            'buyer_name' => 'Customer',
            'buyer_vat' => '123456789012345',
            'buyer_address' => 'Street',
            'invoice_flavor' => 'standard',
            'icv' => 1,
            'pih' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'rows' => [[
                'name' => 'Item',
                'qty' => 1,
                'line_subtotal' => 100,
                'line_tax' => 15,
                'line_total' => 115,
                'unit_price' => 100,
            ]],
            'taxable_total' => 100,
            'tax_total' => 15,
            'payable_total' => 115,
        ]);

        $this->assertStringContainsString('<cac:DiscrepancyResponse>', $creditXml);
        $this->assertStringContainsString('<cbc:Description>Correction of previous invoice</cbc:Description>', $creditXml);
        $this->assertStringContainsString('<cac:DiscrepancyResponse>', $debitXml);
        $this->assertStringContainsString('<cbc:Description>Correction of previous invoice</cbc:Description>', $debitXml);
    }

    public function testSimplifiedSaleInvoiceIncludesComplianceNoteBlock(): void
    {
        $service = new App\Services\ZatcaInvoiceService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('buildSaleXml');
        $method->setAccessible(true);

        $xml = $method->invoke($service, [
            'uuid' => 'test-uuid',
            'invoice_no' => 'INV-1',
            'issue_date' => '2026-08-02',
            'issue_time' => '12:34:56',
            'type_code' => '388',
            'sub_type' => '0200000',
            'seller_name' => 'Test Seller',
            'seller_vat' => '123456789012345',
            'seller_address' => 'Riyadh',
            'seller_building_number' => '1234',
            'seller_city_subdivision_name' => 'Al-Murabba',
            'seller_city_name' => 'Riyadh',
            'seller_postal_code' => '12345',
            'seller_country' => 'SA',
            'buyer_name' => '',
            'buyer_vat' => '',
            'buyer_address' => '',
            'invoice_flavor' => 'simplified',
            'icv' => 1,
            'pih' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'rows' => [[
                'name' => 'Item',
                'qty' => 1,
                'line_subtotal' => 100,
                'line_tax' => 15,
                'line_total' => 115,
                'unit_price' => 100,
            ]],
            'taxable_total' => 100,
            'tax_total' => 15,
            'payable_total' => 115,
        ]);

        $this->assertStringContainsString('<cbc:Note languageID="ar">ABC</cbc:Note>', $xml);
    }
}
