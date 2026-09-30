<?php

namespace App\Services;

use App\Models\BackupSetting;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Str;

class BackupStorageService
{
    /**
     * Test S3 connection with comprehensive validation
     * Returns ['success' => bool, 'message' => string, 'details' => array]
     */
    public function testConnection(BackupSetting $settings): array
    {
        if (empty($settings->endpoint) || empty($settings->bucket) || empty($settings->access_key_id) || empty($settings->secret_access_key)) {
            return [
                'success' => false,
                'message' => 'Incomplete storage configuration. Please provide endpoint, bucket, access key ID, and secret access key.',
                'details' => ['step' => 'validation'],
            ];
        }

        try {
            $client = $this->createS3Client($settings);

            // Step 1: Test credentials / list buckets
            try {
                $client->listBuckets();
            } catch (AwsException $e) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with S3 provider: '.($e->getAwsErrorMessage() ?: $e->getMessage()),
                    'details' => [
                        'step' => 'authentication',
                        'error' => $e->getAwsErrorMessage() ?: $e->getMessage(),
                    ],
                ];
            }

            // Step 2: Test bucket accessibility
            try {
                $client->headBucket([
                    'Bucket' => $settings->bucket,
                ]);
            } catch (AwsException $e) {
                return [
                    'success' => false,
                    'message' => 'Bucket not accessible or not found: '.($e->getAwsErrorMessage() ?: $e->getMessage()),
                    'details' => [
                        'step' => 'bucket_access',
                        'bucket' => $settings->bucket,
                        'error' => $e->getAwsErrorMessage() ?: $e->getMessage(),
                    ],
                ];
            }

            // Step 3: Write, read, delete test marker object
            $markerKey = ($settings->path_prefix ? rtrim($settings->path_prefix, '/').'/' : '').'.connection-test-'.Str::uuid();
            $markerContent = 'DFM Backup Test - '.now()->toIso8601String();

            try {
                $client->putObject([
                    'Bucket' => $settings->bucket,
                    'Key' => $markerKey,
                    'Body' => $markerContent,
                ]);

                $result = $client->getObject([
                    'Bucket' => $settings->bucket,
                    'Key' => $markerKey,
                ]);

                $readContent = (string) $result['Body'];
                if ($readContent !== $markerContent) {
                    throw new \Exception('Read content mismatch');
                }

                $client->deleteObject([
                    'Bucket' => $settings->bucket,
                    'Key' => $markerKey,
                ]);

                return [
                    'success' => true,
                    'message' => 'Connection test passed. Endpoint, bucket, and read/write/delete permissions verified.',
                    'details' => [
                        'endpoint' => $settings->endpoint,
                        'bucket' => $settings->bucket,
                        'region' => $settings->region,
                    ],
                ];
            } catch (AwsException $e) {
                return [
                    'success' => false,
                    'message' => 'Write/Read/Delete permission test failed: '.($e->getAwsErrorMessage() ?: $e->getMessage()),
                    'details' => [
                        'step' => 'permissions',
                        'error' => $e->getAwsErrorMessage() ?: $e->getMessage(),
                    ],
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
                'details' => [
                    'step' => 'initialization',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Perform actual database backup to S3
     */
    public function performBackup(BackupSetting $settings, string $trigger = 'manual'): array
    {
        $startTime = now();
        $tempFile = null;
        $gzTempFile = null;

        try {
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");

            $timestamp = now()->format('Y-m-d_His');
            $siteName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $settings->site->name);
            $filename = "backup-{$siteName}-{$timestamp}.sql";
            $gzFilename = "{$filename}.gz";

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFile = $tempDir.'/'.$filename;
            $gzTempFile = $tempDir.'/'.$gzFilename;

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s 2>&1',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? '3306'),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($tempFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('mysqldump failed: '.implode("\n", $output));
            }

            // Compress
            exec(sprintf('gzip -c %s > %s 2>&1', escapeshellarg($tempFile), escapeshellarg($gzTempFile)), $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($gzTempFile)) {
                throw new \Exception('gzip compression failed');
            }

            $fileSize = filesize($gzTempFile);

            // Upload to S3
            $client = $this->createS3Client($settings);

            $year = now()->format('Y');
            $month = now()->format('m');
            $objectKey = $this->buildObjectKey($settings, $year, $month, $gzFilename);

            $client->putObject([
                'Bucket' => $settings->bucket,
                'Key' => $objectKey,
                'Body' => fopen($gzTempFile, 'r'),
                'ContentType' => 'application/gzip',
            ]);

            // Verify
            $result = $client->headObject([
                'Bucket' => $settings->bucket,
                'Key' => $objectKey,
            ]);

            $uploadedSize = $result['ContentLength'];
            if ($uploadedSize != $fileSize) {
                throw new \Exception("Size mismatch: local={$fileSize}, uploaded={$uploadedSize}");
            }

            // Clean up
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
            if ($gzTempFile && file_exists($gzTempFile)) {
                unlink($gzTempFile);
            }

            return [
                'success' => true,
                'message' => 'Backup completed successfully',
                'details' => [
                    'object_key' => $objectKey,
                    'size_bytes' => $fileSize,
                    'duration_seconds' => now()->diffInSeconds($startTime),
                ],
            ];
        } catch (\Exception $e) {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
            if ($gzTempFile && file_exists($gzTempFile)) {
                unlink($gzTempFile);
            }

            return [
                'success' => false,
                'message' => 'Backup failed: '.$e->getMessage(),
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    private function createS3Client(BackupSetting $settings): S3Client
    {
        $config = [
            'version' => 'latest',
            'region' => $settings->region,
            'endpoint' => $settings->endpoint,
            'use_path_style_endpoint' => $settings->use_path_style,
            'credentials' => [
                'key' => $settings->access_key_id,
                'secret' => $settings->secret_access_key,
            ],
        ];

        if (str_contains($settings->endpoint, 'localhost') || str_contains($settings->endpoint, '127.0.0.1')) {
            $config['http'] = ['verify' => false];
        }

        return new S3Client($config);
    }

    private function buildObjectKey(BackupSetting $settings, string $year, string $month, string $filename): string
    {
        $parts = array_filter([
            $settings->path_prefix,
            preg_replace('/[^a-zA-Z0-9_-]/', '_', $settings->site->name),
            $year,
            $month,
            $filename,
        ]);

        return implode('/', $parts);
    }
}
