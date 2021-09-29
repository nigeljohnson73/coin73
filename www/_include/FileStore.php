<?php
use Google\Cloud\Storage\StorageClient;

class FileStore {
	private static $instances = [ ];

	protected function __clone() {
	}

	public function __wakeup() {
		throw new \Exception ( "Cannot unserialize a singleton." );
	}

	public static function getInstance() {
		$cls = static::class;
		if (! isset ( self::$instances [$cls] )) {
			self::$instances [$cls] = new static ();
		}

		return self::$instances [$cls];
	}

	protected function __construct($name, $options = [ ]) {
		$this->storage = null;
		$this->bucket = null;
		$this->bucket_name = strtolower ( getDataNamespace() . "_" . $name );
		$this->options = $options;
		$this->init ();
	}

	protected function init() {
		// $bucket_name = strtolower ( $this->namespace . "_" . $name );
		// $bucket_name = getDataNamespace () . "_" . $name;
		$s_options = [ 
				"suppressKeyFileNotice" => true,
				"projectId" => getProjectId ()
		];

		try {
			$this->storage = new StorageClient ( $s_options );
			//logger ( LL_DBG, "FileStore::FileStore(): Created StorageClient" );
		} catch ( Exception $e ) {
			$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::FileStore(): Cannot create StorageClient: " . $e->message );
		}

		if ($this->storage) {
			$b_options = [ ];

			// What type of storage: "STANDARD", "NEARLINE", "COLDLINE" and "ARCHIVE"
			// https://cloud.google.com/storage/docs/storage-classes
			$b_options ["storageClass"] = $this->options ["storageClass"] ?? ("STANDARD");

			// Default access: "authenticatedRead", <<"bucketOwnerFullControl">>, "bucketOwnerRead", "private", "projectPrivate", "publicRead"
			$b_options ["defaultObjectAcl"] = $this->options ["defaultObjectAcl"] ?? "projectPrivate";
			$b_options ["predefinedAcl"] = $b_options ["defaultObjectAcl"];
			// Defaults to US
			$b_options ["location"] = "EU"; // TODO: store this in a config field???

			try {
				$this->bucket = $this->storage->bucket ( $this->bucket_name );
			} catch ( Exception $e ) {
				logger ( LL_ERR, "FileStore::FileStore(): Cannot open Bucket: " . $e->getMessage () );
				$this->bucket = null;
			}

			try {
				if (! ($this->bucket && $this->bucket->exists ())) {
					logger ( LL_WRN, "FileStore::FileStore(): Attempting to create Bucket: '" . $this->bucket_name . "'" );
					try {
						$this->bucket = $this->storage->createBucket ( $this->bucket_name, $b_options );
						logger ( LL_DBG, "FileStore::FileStore(): Created Bucket '" . $this->bucket_name . "'" );
					} catch ( Exception $e ) {
						$e = json_decode ( $e->getMessage () )->error;
						logger ( LL_ERR, "FileStore::FileStore(): Cannot create Bucket: " . $e->message );
						$this->bucket = null;
					}
				} else {
					logger ( LL_DBG, "FileStore::FileStore(): Opened Bucket '" . $this->bucket_name . "'" );
				}
			} catch ( Exception $e ) {
				$e = json_decode ( $e->getMessage () )->error;
				logger ( LL_ERR, "FileStore::FileStore(): bucket is broken: " . $e->message );
				$this->bucket = null;
			}
		}
	}

	public function putContents($filename, $contents) {
		if (! $this->bucket) {
			return false;
		}

		$ret = false;
		try {
			$this->bucket->upload ( $contents, [ 
					"name" => $filename
			] );
			logger ( LL_DBG, "FileStore::putContents(): Uploaded '" . $filename . "'" );
			$ret = true;
		} catch ( Exception $e ) {
			$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::putContents(): Cannot upload '" . $filename . "': " . $e->message );
		}
		return $ret;
	}

	public function getContents($filename) {
		if (! $this->bucket) {
			return false;
		}

		$ret = false;
		try {
			$object = $this->bucket->object ( $filename );
			$ret = $object->downloadAsString ();
			logger ( LL_DBG, "FileStore::getContents(): Downloaded '" . $filename . "'" );
		} catch ( Exception $e ) {
			// $e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::putContents(): Cannot download '" . $filename . "': " . $e->getMessage () );
		}
		return $ret;
	}

	public function delete($filename) {
		if (! $this->bucket) {
			return false;
		}

		$ret = false;
		try {
			$object = $this->bucket->object ( $filename );
			$object->delete ();
			logger ( LL_DBG, "FileStore::delete(): Deleted '" . $filename . "'" );
			$ret = true;
		} catch ( Exception $e ) {
			// $e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::delete(): Cannot delete '" . $filename . "': " . $e->getMessage () );
		}
		return $ret;
	}
}

function __testFileStore() {

	class TestFileStore extends FileStore {

		protected function __construct() {
			logger ( LL_DBG, "TestFileStore::TestFileStore()" );
			parent::__construct ( "TestFileStore" );
		}
	}

	global $logger;
	$ll = $logger->getLevel ();
	$logger->setLevel ( LL_DBG );

	$store = TestFileStore::getInstance ();
	$contents = "Welcome to the jungle!";
	$filename = "welcome.txt";

	if (! $store->putContents ( $filename, $contents )) {
		logger ( LL_ERR, "Unable to store file contents" );
		return false;
	}
	logger ( LL_INF, "Stored file contents" );

	$c = $store->getContents ( $filename );
	if ($c === false) {
		logger ( LL_ERR, "Unable to retrieve file contents" );
		return false;
	}
	logger ( LL_INF, "Retrieved file contents" );

	if ($c !== $contents) {
		logger ( LL_ERR, "Retrieved file contents do not match put file contents" );
		return false;
	}
	logger ( LL_INF, "File contents match expected" );

	if (! $store->delete ( $filename )) {
		logger ( LL_ERR, "Unable to delete file" );
		return false;
	}
	logger ( LL_INF, "Deleted file" );
	$logger->setLevel ( $ll );
}

function __testFileStoreRaw() {
	$cli_options = [ 
			"suppressKeyFileNotice" => true,
			"projectId" => getProjectId ()
	];
	$storage = null;

	try {
		$storage = new StorageClient ( $cli_options );
		logger ( LL_INF, "Created StorageClient" );
	} catch ( Exception $e ) {
		$e = json_decode ( $e->getMessage () )->error;
		logger ( LL_ERR, "Cannot create StorageClient: " . $e->message );
	}

	if ($storage) {

		$bucket_name = getDataNamespace () . '_public';
		$bucket = null;

		try {
			$bucket = $storage->bucket ( $bucket_name );
			logger ( LL_INF, "Opened Bucket '" . $bucket_name . "'" );
		} catch ( Exception $e ) {
			$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "Cannot open Bucket: " . $e->message );
		}

		if (! $bucket) {
			try {
				$bucket = $storage->createBucket ( $bucket_name );
				logger ( LL_INF, "Created Bucket '" . $bucket_name . "'" );
			} catch ( Exception $e ) {
				$e = json_decode ( $e->getMessage () )->error;
				logger ( LL_ERR, "Cannot create Bucket: " . $e->message );
			}
		}

		if ($bucket) {
			$file_name = "README.md";
			try {
				$bucket->upload ( file_get_contents ( __DIR__ . '/' . $file_name ), [ 
						'name' => $file_name,
						'predefinedAcl' => 'publicRead'
				] );
				logger ( LL_INF, "Uploaded '" . $file_name . "'" );
			} catch ( Exception $e ) {
				$e = json_decode ( $e->getMessage () )->error;
				logger ( LL_ERR, "Cannot upload '" . $file_name . "': " . $e->message );
			}

			try {
				$object = $bucket->object ( $file_name );
				$object->downloadToFile ( __DIR__ . '/zzz_deleteme_' . $file_name );
				logger ( LL_INF, "Downloaded '" . $file_name . "'" );
			} catch ( Exception $e ) {
				$e = json_decode ( $e->getMessage () )->error;
				logger ( LL_ERR, "Cannot download '" . $file_name . "': " . $e->message );
			}
		} else {
			logger ( LL_ERR, "Cannot access storage bucket" );
		}
	}
}
?>