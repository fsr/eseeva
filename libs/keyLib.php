<?php
//============================================================================
// Name        : keyLib.php
// Author      : Patrick Reipschläger, Lucas Woltmann
// Version     : 2.0
// Date        : 01-2017
// Description : Provides several functions for creating and handling keys
//               for the ESE evaluation for students and tutors.
//============================================================================

	// Constant for the file which contains the keys, should be used by all other scripts so it can easily be changed
	define ("KEYFILE", "db/eseeva.db");
	// Constants for the different key states that are possible, should always be used when altering or checking the state of a key
	define ("KEYSTATE_NONEXISTENT", "nonexistent");
	define ("KEYSTATE_UNISSUED", "unissued");
	define ("KEYSTATE_ISSUED", "issued");
	define ("KEYSTATE_ACTIVATED", "activated");
	define ("KEYSTATE_USED", "used");
	
	/**
	 * Generates a key from the specified hash value.
	 *
	 * @return string
	 */
	function GenerateKey()
	{
		// create a hash using a unique id based on the servers system time and the sha1 hashing algorithm
		$hash = strtoupper(hash("sha1", uniqid()));
		// extract the desired number of digits out of the hash to generate the key
		return substr($hash, 0, 4) . "-" . substr($hash, 4, 4) . "-" . substr($hash, 8, 4) . "-" . substr($hash, 12, 4);
		//return substr($hash, 0, 2) . "-" . substr($hash, 2, 2) . "-" . substr($hash, 4, 2) . "-" . substr($hash, 6, 2);
	}
	/**
	 * Generates the specified amount of keys. Be aware that this function just
	 * creates an array of keys with no state assigned.
	 *
	 * @param integer $amount The amount of keys that should be generated
	 * @return array
	 */
	function GenerateKeys($amount)
	{
		$keys = array();
		for ($i = 0; $i < $amount; $i++)
		{
			array_push($keys, GenerateKey());
			usleep(1);
		}
		return array_unique($keys);
	}
	/**
	 * Generates a new keys in the database.
	 * Returns true if the key file was created successfully, otherwise false.
	 *
	 * @param integer $keyAmount The amount of keys that should be generated.
	 * @param string $fileName The name of the key database that should be created.
	 */
	function CreateKeys($keyAmount, $fileName)
	{
		$handle = new SQLite3($fileName, SQLITE3_OPEN_READWRITE);
		if (!$handle)
			return false;
		$keys = GenerateKeys($keyAmount);
		$count = count($keys);
		
		for ($i = 0; $i < $count; $i++)
		{
			$stmt = $handle->prepare("INSERT INTO answers (KeyId, Status) VALUES (:key,:status);");
			if ($stmt)
	        {
	            $stmt->bindValue(':key', $keys[$i], SQLITE3_TEXT);
	            $stmt->bindValue(':status', KEYSTATE_UNISSUED, SQLITE3_TEXT);
				$result = $stmt->execute();
	        }
	        else
	        {
	            $handle->close(); 
	            return false;
	        }
    	}

		$handle->close();
		return true;
	}
	/**
	 * Opens the database with the specified name and reads all key data that the database contains.
	 * The resulting data type will be an array of arrays consisting of the key and its state.
	 * Returns null if the key file could not be found or read.
	 *
	 * @param string $fileName The database which should be read.
	 * @return array
	 */
	function ReadKeys($fileName)
	{
		if (!file_exists($fileName))
			return null;
		$handle = new SQLite3($fileName, SQLITE3_OPEN_READONLY);
		if (!$handle)
			return null;
		$data = $handle->query("SELECT KeyId, Status FROM answers;");
		$data = PrepareResult($data);
		$handle->close();

		if ($data) 
		{
			return $data;
		}
		
		return array();
	}
	/**
	 * Get the current state of the specified key from the database, which will be one of the defines
	 * KEYSTATE constants.
	 *
	 * @param array $fileName The database with the keys.
	 * @param string $key The key which state should be got. Passed by reference.
	 * @return integer
	 */
	function GetKeyState($fileName, &$key)
	{
		if (!file_exists($fileName))
			return null;
		$handle = new SQLite3($fileName, SQLITE3_OPEN_READONLY);
		if (!$handle)
			return null;

		$stmt = $handle->prepare("SELECT Status FROM answers WHERE KeyId=:keyid;");
		if ($stmt)
        {
            $stmt->bindValue(':keyid', $key, SQLITE3_TEXT);
			$result = $stmt->execute();
			$result = $result->fetchArray(SQLITE3_ASSOC);

			$handle->close(); 
			return $result["Status"];
        }

        $handle->close(); 
		return KEYSTATE_NONEXISTENT;
	}
	/**
	 * Set the state of the specified key to the specified state.
	 * Returns true if the key was found within the key data and the state has been changed, otherwise false.
	 *
	 * @param array $fileName The database in which the key should be found.
	 * @param string $key The key which state should be changed.
	 * @param integer $newState The new state of the specified key. Must be on of the KEYSTATE constants.
	 * @return boolean
	 */
	function SetKeyState($fileName, &$key, $newState)
	{	
		if (!file_exists($fileName))
			return false;
		$handle = new SQLite3($fileName, SQLITE3_OPEN_READWRITE);
		if (!$handle)
			return false;

		//secure override by checking if key has been uesed already
		$stmt = $handle->prepare("UPDATE answers SET Status=:status WHERE KeyId=:keyid AND Status!=:status;");
		if ($stmt)
        {
            $stmt->bindValue(':keyid', $key, SQLITE3_TEXT);
            $stmt->bindValue(':status', $newState, SQLITE3_TEXT);
			$result = $stmt->execute();

			$handle->close(); 
			return true;
        }

        $handle->close();
        return false;
	}
	/**
	 * Deletes a single key in the database if it has not been used yet.
	 * Returns true if the key was deleted, otherwise false.
	 *
	 * @param array $fileName The database in which the key should be found.
	 * @param string $key The key which should be deleted.
	 * @return boolean
	 */
	function DeleteKey($fileName, &$key)
	{
		if (!file_exists($fileName))
			return false;
		$handle = new SQLite3($fileName, SQLITE3_OPEN_READWRITE);
		if (!$handle)
			return false;

		//secure deleting by checking if key has been uesed already
		$stmt = $handle->prepare("DELETE FROM answers WHERE KeyId=:keyid AND Answer!=NULL;");
		if ($stmt)
        {
            $stmt->bindValue(':keyid', $key, SQLITE3_TEXT);
			$result = $stmt->execute();

			$handle->close();
			return true;
        }

        $handle->close();
        return false;
	}
	/**
	 * Prepares the results from the database to an array of keys with their state.
	 *
	 * @param array $resultSet Cursor from the sqlite database. Passed by reference.
	 * @return array
	 */
	function PrepareResult(&$resultSet)
	{
		$result = array();
		$i = 0;

		while($res = $resultSet->fetchArray(SQLITE3_ASSOC))
		{
			foreach ($res as $key => $value) {
				$result[$i][$key] = $value;
			}
			$i++;
		} 

		return $result;
	}
?>
