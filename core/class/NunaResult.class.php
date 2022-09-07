<?php
/**
 * Description of ForbizResult
 *
 * @author hoksi
 * @property CI_DB_oci8_result $nrResult
 */
class NunaResult
{
    protected $nrResult = false;
    
    public function __construct($result)
    {
        $this->nrResult = $result;
    }

    /**
     * Retrieve the results of the query. Typically an array of
     * individual data rows, which can be either an 'array', an
     * 'object', or a custom class name.
     * @param string $type
     * @return mixed
     */
    public function getResult($type = 'object')
    {
        return (is_object($this->nrResult) ? $this->nrResult->result($type) : false);
    }

    public function num_rows()
    {
        return (is_object($this->nrResult) ? $this->nrResult->num_rows() : false);
    }

    /**
     * Returns the results as an array of arrays.
     *
     * If no results, an empty array is returned.
     *
     * @return array
     */
    public function getResultArray()
    {
        return (is_object($this->nrResult) ? $this->nrResult->result_array() : false);
    }

    /**
     * Wrapper object to return a row as either an array, an object, or
     * a custom class.
     *
     * If row doesn't exist, returns null.
     *
     * @param int    $n    The index of the results to return
     * @param string $type The type of result object. 'array', 'object' or class name.
     *
     * @return mixed
     */
    public function getRow($n = 0, $type = 'object')
    {
        return (is_object($this->nrResult) ? $this->nrResult->row($n, $type) : false);
    }

    /**
     * Returns a single row from the results as an array.
     *
     * If row doesn't exist, returns null.
     *
     * @param int $n
     *
     * @return mixed
     */
    public function getRowArray($n = 0)
    {
        return (is_object($this->nrResult) ? $this->nrResult->row_array($n) : false);
    }

    /**
     * Returns an unbuffered row and move the pointer to the next row.
     *
     * @param string $type
     *
     * @return mixed
     */
    public function getUnbufferedRow($type = 'object')
    {
        return (is_object($this->nrResult) ? $this->nrResult->unbuffered_row($type) : false);
    }

    /**
     * Frees the current result.
     *
     * @return mixed
     */
    public function freeResult()
    {
        (is_object($this->nrResult) ? $this->nrResult->free_result() : false);
    }

    /**
     * Next the current result.
     *
     * @return mixed
     */
    public function nextResult()
    {
        if(get_class($this->nrResult) == 'CI_DB_mysqli_result') {
            if (is_object($this->nrResult) && is_object($this->nrResult->conn_id)) {
                if (mysqli_more_results($this->nrResult->conn_id)) {
                    return mysqli_next_result($this->nrResult->conn_id);
                }
            }
        }
    }
}