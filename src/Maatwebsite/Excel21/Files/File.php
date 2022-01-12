<?php namespace Maatwebsite\Excel21\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel21\Excel21;
use Maatwebsite\Excel21\Exceptions\LaravelExcelException;

abstract class File {

    /**
     * @var Application
     */
    protected $app;

    /**
     * Excel instance
     * @var Excel21
     */
    protected $excel21;

    /**
     * Loaded file
     * @var \Maatwebsite\Excel21\Readers\LaravelExcelReader
     */
    protected $file;

    /**
     * @param Application $app
     * @param Excel21     $excel21
     */
    public function __construct(Application $app, Excel21 $excel21)
    {
        $this->app = $app;
        $this->excel21 = $excel21;
    }

    /**
     * Handle the import/export of the file
     * @param $type
     * @throws LaravelExcelException
     * @return mixed
     */
    public function handle($type)
    {
        // Get the handler
        $handler = $this->getHandler($type);

        // Call the handle method and inject the file
        return $handler->handle($this);
    }

    /**
     * Get handler
     * @param $type
     * @throws LaravelExcelException
     * @return mixed
     */
    protected function getHandler($type)
    {
        return $this->app->make(
            $this->getHandlerClassName($type)
        );
    }

    /**
     * Get the file instance
     * @return mixed
     */
    public function getFileInstance()
    {
        return $this->file;
    }

    /**
     * Get the handler class name
     * @throws LaravelExcelException
     * @return string
     */
    protected function getHandlerClassName($type)
    {
        // Translate the file into a FileHandler
        $class = get_class($this);
        $handler = substr_replace($class, $type . 'Handler', strrpos($class, $type));

        // Check if the handler exists
        if (!class_exists($handler))
            throw new LaravelExcelException("$type handler [$handler] does not exist.");

        return $handler;
    }
}