<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

/**
 * Health check endpoint.
 *
 * GET /api/v1/health
 *
 * Returns 200 when the application and database are reachable,
 * and 503 when the database cannot be reached.
 *
 * Security: the response never contains credentials, database names,
 * usernames, stack traces or raw error messages. Details go to the
 * server log (writable/logs) only.
 */
class Health extends BaseController
{
    public function index(): ResponseInterface
    {
        $databaseUp = $this->isDatabaseUp();

        $payload = [
            'success' => $databaseUp,
            'message' => $databaseUp
                ? 'Service is healthy.'
                : 'Service is degraded: database unavailable.',
            'data' => [
                'service'     => 'gbp-manager-api',
                'status'      => $databaseUp ? 'ok' : 'degraded',
                'environment' => ENVIRONMENT,
                'time_utc'    => gmdate('Y-m-d\TH:i:s\Z'),
                'database'    => [
                    'status' => $databaseUp ? 'up' : 'down',
                ],
            ],
        ];

        return $this->response
            ->setStatusCode($databaseUp ? 200 : 503)
            ->setJSON($payload);
    }

    /**
     * Runs a trivial query to prove the database connection works.
     */
    private function isDatabaseUp(): bool
    {
        try {
            $db     = Database::connect();
            $result = $db->query('SELECT 1 AS ok');

            if ($result === false) {
                return false;
            }

            $row = $result->getRowArray();

            return isset($row['ok']) && (int) $row['ok'] === 1;
        } catch (Throwable $e) {
            log_message('error', 'Health check: database unavailable. ' . $e->getMessage());

            return false;
        }
    }
}