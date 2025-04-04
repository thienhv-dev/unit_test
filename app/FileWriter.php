<?php

namespace App;

class FileWriter
{
    /**
     * Opens a file with the specified mode.
     *
     * @param string $filename The name of the file to open
     * @param string $mode The mode in which to open the file
     * @return resource|false The file handle resource on success, or false on failure
     */
    public function open(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * Writes a line of data to a CSV file.
     *
     * @param resource $fileHandle The file handle resource
     * @param array $data The data to write to the CSV file
     * @return int|false The length of the written string, or false on failure
     */
    public function writeCsv($fileHandle, array $data)
    {
        return fputcsv($fileHandle, $data);
    }

    /**
     * Closes an open file handle.
     *
     * @param resource $fileHandle The file handle resource to close
     * @return void
     */
    public function close($fileHandle)
    {
        fclose($fileHandle);
    }
}