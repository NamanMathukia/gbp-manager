<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Checks the response contract of GET /api/v1/health.
 *
 * Note: during automated tests CodeIgniter uses the "tests" database
 * group (in-memory SQLite in the default starter), NOT your MariaDB.
 * This test therefore proves the endpoint's JSON shape and that its
 * HTTP status agrees with the database status it reports.
 * Real MariaDB connectivity is verified with the Postman requests.
 *
 * @internal
 */
final class HealthEndpointTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testHealthEndpointReturnsConsistentJsonContract(): void
    {
        $result = $this->call('get', 'api/v1/health');

        $json = $result->getJSON();
        $this->assertNotEmpty($json, 'Response body should contain JSON.');

        $body = json_decode($json, true);
        $this->assertIsArray($body);

        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('data', $body);
        $this->assertSame('gbp-manager-api', $body['data']['service']);
        $this->assertContains($body['data']['database']['status'], ['up', 'down']);

        $databaseUp = $body['data']['database']['status'] === 'up';

        // success flag and HTTP status must agree with the reported DB status
        $this->assertSame($databaseUp, $body['success']);
        $result->assertStatus($databaseUp ? 200 : 503);

        // never leak sensitive words
        $lower = strtolower($json);
        foreach (['password', 'secret', 'gmb_app', 'stack'] as $word) {
            $this->assertStringNotContainsString($word, $lower);
        }
    }
}