<?php namespace Maatwebsite\Excel21\Files;

interface ExportHandler {

    /**
     * Handle the export
     * @param $file
     * @return mixed
     */
    public function handle($file);

} 