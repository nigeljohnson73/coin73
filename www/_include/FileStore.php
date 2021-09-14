<?php
use Google\Cloud\Storage\StorageClient;

class FileStore {

	public function __construct($name, $options = [ ]) {
		$this->storage = null;
		$this->bucket = null;
		$bucket_name = strtolower(getDataNamespace () . "_" . $name);
		//$bucket_name = getDataNamespace () . "_" . $name;
		
		$s_options = [ 
				"suppressKeyFileNotice" => true,
				"projectId" => getProjectId ()
		];

		try {
			$this->storage = new StorageClient ( $s_options );
			logger ( LL_DBG, "FileStore::FileStore(): Created StorageClient" );
		} catch ( Exception $e ) {
			$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::FileStore(): Cannot create StorageClient: " . $e->message );
		}

		if ($this->storage) {
			$b_options = [ ];

			// What type of storage: "STANDARD", "NEARLINE", "COLDLINE" and "ARCHIVE"
			// https://cloud.google.com/storage/docs/storage-classes
			$b_options ["storageClass"] = isset ( $options ["storageClass"] ) ? ($options ["storageClass"]) : ("STANDARD");

			// Default access: "authenticatedRead", <<"bucketOwnerFullControl">>, "bucketOwnerRead", "private", "projectPrivate", "publicRead"
			$b_options ["defaultObjectAcl"] = isset ( $options ["defaultObjectAcl"] ) ? ($options ["defaultObjectAcl"]) : ("projectPrivate");
			$b_options ["predefinedAcl"] = $b_options ["defaultObjectAcl"];
			// Defaults to US
			$b_options ["location"] = "EU"; // TODO: store this in a config field???
			
			try {
				$this->bucket = $this->storage->bucket ( $bucket_name );
			} catch ( Exception $e ) {
				logger ( LL_ERR, "FileStore::FileStore(): Cannot open Bucket: " . $e->getMessage () );
				$this->bucket = null;
			}

			try {
				if (! ($this->bucket && $this->bucket->exists ())) {
					logger ( LL_WRN, "FileStore::FileStore(): Attempting to create Bucket: '" . $bucket_name . "'" );
					try {
						$this->bucket = $this->storage->createBucket ( $bucket_name, $b_options );
						logger ( LL_DBG, "FileStore::FileStore(): Created Bucket '" . $bucket_name . "'" );
					} catch ( Exception $e ) {
						$e = json_decode ( $e->getMessage () )->error;
						logger ( LL_ERR, "FileStore::FileStore(): Cannot create Bucket: " . $e->message );
						$this->bucket = null;
					}
				} else {
					logger ( LL_DBG, "FileStore::FileStore(): Opened Bucket '" . $bucket_name . "'" );
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
			//$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::putContents(): Cannot download '" . $filename . "': " . $e->getMessage() );
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
			$ret = $object->delete ();
			logger ( LL_DBG, "FileStore::delete(): Deleted '" . $filename . "'" );
		} catch ( Exception $e ) {
			//$e = json_decode ( $e->getMessage () )->error;
			logger ( LL_ERR, "FileStore::delete(): Cannot delete '" . $filename . "': " . $e->getMessage() );
		}
		return $ret;
	}
}

?>