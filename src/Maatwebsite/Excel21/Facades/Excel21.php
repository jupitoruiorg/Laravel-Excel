<?php namespace Maatwebsite\Excel21\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * LaravelExcel Facade
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel21
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Excel21 extends Facade {

    /**
     * Return facade accessor
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel21';
    }
}