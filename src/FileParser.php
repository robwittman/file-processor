<?php

namespace FileProcessor;

class FileParser
{
    protected $file;

    protected $headers = array();

    protected $rows = array();

    public function __construct(\SplFileObject $file)
    {
        $this->file = $file;
    }

    public function parse()
    {
        $this->headers = $this->file->fgetcsv();
        while (!$this->file->eof()) {
            $data = $this->file->fgetcsv();
            $this->rows[] = array_change_key_case(array_combine($this->headers, $data), CASE_LOWER);
        }
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
