<?php
namespace Google\Cloud\Samples\Auth;

// Imports the Cloud Storage client library.
use Google\Cloud\Storage\StorageClient;

/**
 * Authenticate to a cloud client library using a service account explicitly.
 *
 * @param string $projectId           The Google project ID.
 * @param string $serviceAccountPath  Path to service account credentials JSON.
 */
function auth_cloud_explicit($projectId, $serviceAccountPath)
{
    # Explicitly use service account credentials by specifying the private key
    # file.
    $config = [
        'keyFilePath' => $serviceAccountPath,
        'projectId' => $projectId,
    ];
    $storage = new StorageClient($config);

    # Make an authenticated API request (listing storage buckets)
    foreach ($storage->buckets() as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->name());
    }
}

auth_cloud_explicit("coin73", __DIR__."/service-account.json");
?>
