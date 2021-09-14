<?php
include_once (__DIR__ . "/FileStore.php");

class KeyStore extends FileStore {

	public function __construct() {
		logger ( LL_DBG, "KeyStore::KeyStore()" );

		parent::__construct ( "KeyStore" );
	}

	protected function genFilename($email) {
		return hash ( "sha1", strtolower ( getDataNamespace () . "_" . $email ) );
	}

	public function getKeys($email) {
		logger ( LL_DBG, "KeyStore::getKeys('" . $email . "')" );
		$filename = $this->genFilename ( $email );
		$json = parent::getContents ( $filename );
		return json_decode ( $json );
	}

	public function putKeys($email, $public, $private) {
		logger ( LL_DBG, "KeyStore::putKeys('" . $email . "', ...)" );
		$filename = $this->genFilename ( $email );
		$value = new StdClass ();
		$value->public = $public;
		$value->private = $private;
		$contents = json_encode ( $value );

		return parent::putContents ( $filename, $contents );
	}

	public function deleteKeys($email) {
		logger ( LL_DBG, "KeyStore::deleteKeys('" . $email . "')" );
		$filename = $this->genFilename ( $email );
		return parent::delete ( $filename );
	}
}
?>