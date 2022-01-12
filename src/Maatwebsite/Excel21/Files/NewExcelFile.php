<?php namespace Maatwebsite\Excel21\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel21\Excel;

abstract class NewExcelFile extends File {

    /**
     * @param Application $app
     * @param Excel       $excel21
     */
    public function __construct(Application $app, Excel $excel21)
    {
        parent::__construct($app, $excel21);
        $this->file = $this->createNewFile();
    }

    /**
     * Get file
     * @return string
     */
    abstract public function getFilename();

    /**
     * Start importing
     */
    public function handleExport()
    {
        return $this->handle( 
            get_class($this) 
        );
    }


    /**
     * Load the file
     * @return \Maatwebsite\Excel21\Readers\LaravelExcelReader
     */
    public function createNewFile()
    {
        // Load the file
        $file = $this->excel21->create(
            $this->getFilename()
        );

        return $file;
    }

    /**
     * Dynamically call methods
     * @param  string $method
     * @param  array  $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([$this->file, $method], $params);
    }

}