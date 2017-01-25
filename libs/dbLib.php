<?php
    //============================================================================
    // Name        : dbLib.php
    // Author      : Lucas Woltmann
    // Version     : 1.0
    // Date        : 01-2017
    // Description : Provides functions for managing the answers of the evaluation 
    //               for students and tutors.
    //============================================================================

    define ("LOGDB", "db/eseeva.db");

    /**
     * Reads the database with the specified name and writes all log data to the corresponding arrays.
     * Returns true if the log file has been successfully read, otherwise false.
     * Is guaranteed to initialize the specified log arrays, even when the database file could not be
     * read in which case they will simply be empty.
     * 
     * @param string $fileName    The database from which to read.
     * @param int $student        Whether the user is a student(1) or a tutor(0).
     * @param array $questionData Will be created by this function and is passed by reference.
     *                            Is an array of arrays that consists of the unique id, the question
     *                            and the fields for the possible response options which contain
     *                            the number of times that option has been selected.
     * @param array $tutorData    Will be created by this function and is passed by reference.
     *                            Is an array of arrays that consists of the tutor name and the
     *                            fields for the possible response options which contain the number
     *                            of times that option has been selected.
     * @param array $commentData  Will be created by this function and is passed by reference.
     *                            Is an array of string that resemble the different comments that
     *                            have been made.
     * @return boolean
     */
    function ReadLogDatabase($fileName, $student, &$questionData, &$tutorData, &$commentData)
    {
        // init arrays regardless of the file not being found or eventual errors
        $questionData = array();
        $tutorData = array();
        $commentData = array();

        $questions = array();
        $tutors = array();
        $comments = array();
        // return if the file does not exist
        if (file_exists($fileName) == false)
            return false;
        // open the db
        $handle = new SQLite3($fileName, SQLITE3_OPEN_READONLY);
        if (!$handle)
            return false;

        $stmt = $handle->prepare("SELECT Answer FROM answers WHERE student=:student;");
        if ($stmt)
        {
            $stmt->bindValue(':student', $student, SQLITE3_INTEGER);

            $result = $stmt->execute();
            //read to ararys, commentData does not need any further attention
            PrepareAnswer($result, $questions, $tutors, $commentData);
        }
        else
        {
            $handle->close();
            return false;
        }
        
        $handle->close();

        //convert data into legacy data structure
        ReadLogQuestionData($questions, $questionData);
        ReadLogTutorData($tutors, $tutorData);
        return true;
    }
    /**
     * Writes the specified log arrays to a database with the specified name.
     * Expects data in the same format as in the legacy version, but combines them to a single array 
     * which is going to be saved in the database.
     * Returns true if the file was written successfully, otherwise false.
     *
     * @param string $fileName The name of the database to which the log data should be written.
     * @param int $student Whether the user is a student(1) or a tutor(0).
     * @param array $questionData The question data that should be written to the log file. Passed by reference.
     * @param array $tutorData The tutor data that should be written to the log file. Passed by reference.
     * @param array $commentData The list of comments that should be written to the log file. Passed by reference.
     * @param string $key The key the user used.
     * @return boolean
     */
    function WriteLogDatabase($fileName, $student, &$questionData, &$tutorData, &$commentData, $key)
    {
        $answer = array($questionData, $tutorData, $commentData);
        $handle = new SQLite3($fileName, SQLITE3_OPEN_READWRITE);
        if (!$handle)
            return false;

        $stmt = $handle->prepare("UPDATE answers SET Status='activated', Student=:student, Answer=:answer WHERE KeyId=:keyid;");
        if ($stmt)
        {
            $stmt->bindValue(':student', $student, SQLITE3_INTEGER);
            $stmt->bindValue(':answer', serialize($answer), SQLITE3_BLOB);
            $stmt->bindValue(':keyid', $key, SQLITE3_TEXT);

            $result = $stmt->execute();
        }
        else
        {
            $handle->close(); 
            return false;
        }
        
        $handle->close(); 
        return true;
    }
    /**
     * Legacy data management for the question data.
     * Generates an array of arrays with all the questions.
     * The first index of each subarray is the full title and the remaining indices are the votes (answers).
     *
     * @param array $formData $_POST reference. Passed by reference.
     * @param array $questionData The array data that should be written to. Passed by reference.
     * @param array $questionnaire The general structure (questions) of the questionnaire. Passed by reference.
     */
    function AddQuestionData(&$formData, &$questionData, &$questionnaire)
    {
        foreach($formData as $id => $value)
        {
            if (!isset($questionnaire[$id]))
                continue;
            // get the type of the element with the same id as the form element from the questionnaire
            $type = $questionnaire[$id][0];
            // check if the element is a question on continue with the next one if that is not the case
            if ($type != "Question")
                continue;
            // if there is not field for the current element in the question dsta array, create a new
            // blank field containing the question and zeros for the number of times each answer was picked
            if (array_key_exists($id, $questionData) == false)
                $questionData[$id] = array($questionnaire[$id][1], 0, 0, 0, 0, 0, 0);
            // increment the answer that was selected in the formular by one
            $questionData[$id][(int)$value]++;
        }
    }
    /**
     * Legacy data management for the tutor data.
     * Generates an array of arrays with the tutor.
     * The first index of each subarray is the full name and the remaining indices are the votes (answers).
     *
     * @param array $formData $_POST reference. Passed by reference.
     * @param array $tutorData The array data that should be written to. Passed by reference.
     */
    function AddTutorData(&$formData, &$tutorData)
    {
        // get the name of the tutor from the form
        $tutorName = $formData["tutorName"];
        // get the selected answer of the tutorRating from the form
        $tutorValue = $formData["tutorRating"];
        // if there is no field for the current tutor in the tutor array, create a new
        // nlank one with zeros for the number of times each answer was picked
        if (array_key_exists($tutorName, $tutorData) == false)
            $tutorData[$tutorName] = array(0, 0, 0, 0, 0, 0);
        // increment the answer that was selected in the form by one
        $tutorData[$tutorName][$tutorValue - 1]++;
    }
    /**
     * Legacy data management for the comment data.
     * Checks if a comment was left and writes it into the passed reference.
     *
     * @param array $formData $_POST reference. Passed by reference.
     * @param string $commentData The array data that should be written to. Passed by reference.
     */
    function AddCommentData(&$formData, &$commentData)
    {
        // if the comment field was filled, the comment
        // array is appended by the new comment
        if (trim($formData["comment"]) != "")
        {
            $commentData = $formData["comment"];
        }
    }
    /**
     * Reads all question data out of a provided log file array from the database.
     * This function should NOT BE CALLED directly, instead the ReadLogDatabase function should be
     * used to read a log file as a whole.
     * All data is written to the specified question data array and
     * True is returned if a valid question data block was found, otherwise false.
     *
     * @param array $questions An array from the database. Passed by reference.
     * @param array $questionData An array to which all question data is written to. Passed by reference.
     * @return boolean.
     */
    function ReadLogQuestionData(&$questions, &$questionData)
    {
        foreach ((array)$questions as $user) {
            foreach ($user as $id => $values) {
                if (isset($questionData[$id]))
                {
                    $questionData[$id] = SumArrays($values, $questionData[$id]);
                }
                else
                {
                    $questionData[$id] = $values;
                }
            }
        }
        return true;
    }
    /**
     * Reads all tutor data out of a provided log file array from the database.
     * This function should NOT BE CALLED directly, instead the ReadLogDatabase function should be
     * used to read a log file as a whole.
     * All data is written to the specified question data array and
     * True is returned if a valid tutor data block was found, otherwise false.
     *
     * @param array $tutors An array from the database. Passed by reference.
     * @param array $tutorData An array to which all question data is written to. Passed by reference.
     * @return boolean.
     */
    function ReadLogTutorData(&$tutors, &$tutorData)
    {
        foreach ((array)$tutors as $user) {
            foreach ($user as $id => $values) {
                if (isset($tutorData[$id]))
                {
                    $tutorData[$id] = SumArrays($values, $tutorData[$id]);
                }
                else
                {
                    $tutorData[$id] = $values;
                }
            }
        }
        return true;
    }
    /**
     * Reads all comment data out of a provided log file array from the database.
     * This function should NOT BE CALLED directly, instead the ReadLogDatabase function should be
     * used to read a log file as a whole.
     * All data is written to the specified question data array and
     * True is returned if a valid comment data block was found, otherwise false.
     *
     * @param array $comments An array from the database. Passed by reference.
     * @param array $commentData An array to which all question data is written to. Passed by reference.
     * @return boolean.
     */
    function ReadLogCommentData(&$comments, &$commentData)
    {
        foreach ((array)$comments as $user) {
            foreach ($user as $id => $values) {
                if (isset($commentData[$id]))
                {
                    $commentData[$id] = SumArrays($values, $commentData[$id]);
                }
                else
                {
                    $commentData[$id] = $values;
                }
            }
        }
        return true;
    }
    /**
     * Splits the three arrays from the 'Answer' column in the database into the legacy ararys.
     *
     * @param array $resultSet An array from the database. Passed by reference.
     * @param array $questionsa An array to which all question data is written to. Passed by reference.
     * @param array $tutors An array to which all tutor data is written to. Passed by reference.
     * @param array $comments An array to which all comments data is written to. Passed by reference.
     */
    function PrepareAnswer(&$resultSet, &$questions, &$tutors, &$comments)
    {
        $i = 0;

        while($res = $resultSet->fetchArray(SQLITE3_ASSOC))
        {
            $res = unserialize($res["Answer"]);
            $questions[$i] = $res[0];
            $tutors[$i] = $res[1];
            $comments[$i] = $res[2];
            $i++;
        }
    }
    /**
     * Little helper function for adding up single user answers to a global statistic.
     * Could be compared with a group by.
     *
     * @param array $array1 First array to merge/sum.
     * @param array $array2 Second array to merge/sum.
     * @return array Summed array by index.
     */
    function SumArrays(&$array1, &$array2)
    {
        $result = array();
        //the name
        $result[0] = $array2[0];
        $count = count($array2); 

        //start one index into counting, because index 0 is the name
        for ($i=1; $i < $count; $i++) { 
            $result[$i] = $array1[$i] + $array2[$i];
        }

        return $result;
    }
?>
