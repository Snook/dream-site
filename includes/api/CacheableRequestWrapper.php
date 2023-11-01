<?php
require_once 'includes/CLog.inc';

/**
 * abstract Class CacheableRequestWrapper: implement api request caching
 * using transient data store (database)
 *
 */
abstract class CacheableRequestWrapper
{

	private $cachedData = null;

	protected abstract function setFromCache($data);

	protected abstract function getDataToCache();

	protected abstract function createCacheHashKey();

	protected abstract function getExpirationTime();

	protected abstract function getDatabaseLookupKey();

	/**
	 * @return bool true if a valid (unexpired) cached value exists
	 * that matched the properties of this wrapper instance
	 */
	public function isCached()
	{
		if ($this->restoreFromCache() !== null)
		{
			return true;
		}

		return false;
	}

	/**
	 * @return DataWrapper populated from cache instead of from
	 * call to API service
	 */
	public function restoreFromCache()
	{
		if ($this->doesCacheExist() && !$this->isCacheExpired())
		{
			$this->setFromCache($this->cachedData->data);

			//$this->rateResponseArray = $this->cachedData ->rates;
			return $this;
		}

		//only restore from DB if necessary
		$this->cachedData = $this->fetchPersistedCache();
		if ($this->doesCacheExist() && !$this->isCacheExpired())
		{
			$this->setFromCache($this->cachedData->data);

			//$this->rateResponseArray = $this->cachedData->rates;
			return $this;
		}

		return null;
	}

	/**
	 * Stores response data into persistence cache storage
	 *
	 */
	public function cacheData()
	{

		$this->cachedData = new stdClass();
		$this->cachedData->timestame = time();
		$this->cachedData->data = $this->getDataToCache();
		$this->cachedData->key = $this->createCacheHashKey();

		TransientDataStore::storeData($this->getDatabaseLookupKey(), $this->cachedData->key, json_encode($this->getDataToCache()), $this->getExpirationTime(),true);
	}

	/**
	 *
	 */
	protected function deleteCached()
	{
		$this->cachedData = null;
	}

	private function doesCacheExist()
	{
		if ($this->cachedData !== null && $this->cachedData->key == $this->createCacheHashKey())
		{
			return true;
		}

		return false;
	}

	//return null if not found
	private function fetchPersistedCache()
	{

		$fetched = TransientDataStore::retrieveData($this->getDatabaseLookupKey(), $this->createCacheHashKey());
		$cachedData = null;
		if ($fetched['successful'])
		{
			$cachedData = new stdClass();
			$cachedData->expires_timestamp = strtotime($fetched['expires']);
			$cachedData->expires = $fetched['expires'];
			$cachedData->expires_date = date_create($fetched['expires']);
			$cachedData->data = $fetched['data'];
			$cachedData->key = $fetched['data_reference'];
		}

		return $cachedData;
	}

	private function isCacheExpired()
	{
		$now = new DateTime();

		if($this->cachedData->expires_date < $now) {
			echo true;
		}

		return false;
	}

	public function jsonDecoder($string, $isAssociative = false){
		$string = ltrim($string, '"');
		$string = rtrim($string, '"');
		$result = json_decode($string,$isAssociative);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				break;
			case JSON_ERROR_DEPTH:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Maximum stack depth exceeded');
				break;
			case JSON_ERROR_STATE_MISMATCH:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Underflow or the modes mismatch');
				break;
			case JSON_ERROR_CTRL_CHAR:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Unexpected control character found');
				break;
			case JSON_ERROR_SYNTAX:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Syntax error, malformed JSON');
				break;
			case JSON_ERROR_UTF8:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Malformed UTF-8 characters, possibly incorrectly encoded');
				break;
			default:
				CLog::RecordNew(CLog::ERROR, 'CacheableRequestWrapper - JSON DECODE Error - Unknown error');
				break;
		}

		return $result;
	}
}
