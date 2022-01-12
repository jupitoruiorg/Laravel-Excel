<?php namespace Maatwebsite\Excel21\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel21\Excel21;
use Maatwebsite\Excel21\Exceptions\LaravelExcelException;

abstract class ExcelFile extends File {

    /**
     * @var bool|string
     */
    protected $delimiter;

    /**
     * @var bool|string
     */
    protected $enclosure;

    /**
     * @var null
     */
    protected $encoding = null;

    /**
     * @param Application $app
     * @param Excel21     $excel21
     */
    public function __construct(Application $app, Excel21 $excel21)
    {
        parent::__construct($app, $excel21);
        $this->file = $this->loadFile();
    }

    /**
     * Get file
     * @return string
     */
    abstract public function getFile();

    /**
     * Get delimiter
     * @return string
     */
    protected function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Get enclosure
     * @return string
     */
    protected function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Get filters
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * Start importing
     */
    public function handleImport()
    {
        return $this->handle(
            get_class($this)
        );
    }

    /**
     * Load the file
     * @return \Maatwebsite\Excel21\Readers\LaravelExcelReader
     */
    public function loadFile()
    {
        // Load filters
        $this->loadFilters();

        // Load base settings
        $this->loadBaseSettings();

        // Load the file
        $file = $this->excel21->load(
            $this->getFile(),
            null,
            $this->encoding
        );

        return $file;
    }

    /**
     * Load the filter
     * @return void
     */
    protected function loadFilters()
    {
        // Register the filters
        $this->excel21->registerFilters(
            $this->getFilters()
        );

        // Loop through the filters
        foreach($this->getFilters() as $filter)
        {
            // Enable the filter
            $this->excel21->filter($filter);
        }
    }

    /**
     * Load base settings
     */
    protected function loadBaseSettings()
    {
        $this->loadCSVSettings();
    }

    /**
     * Load CSV Settings
     */
    protected function loadCSVSettings()
    {
        // Get user provided delimiter
        $delimiter = $this->getDelimiter();

        // Set it when given
        if($delimiter)
            $this->excel21->setDelimiter($delimiter);

        // Get user provided enclosure
        $enclosure = $this->getEnclosure();

        // Set it when given
        if($enclosure)
            $this->excel21->setEnclosure($enclosure);
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
