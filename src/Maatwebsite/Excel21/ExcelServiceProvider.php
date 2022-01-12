<?php namespace Maatwebsite\Excel21;

use PHPExcel_Settings;
use PHPExcel_Shared_Font;
use Maatwebsite\Excel21\Readers\Html;
use Maatwebsite\Excel21\Classes\Cache;
use Maatwebsite\Excel21\Classes\PHPExcel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel21\Parsers\CssParser;
use Maatwebsite\Excel21\Parsers\ViewParser;
use Maatwebsite\Excel21\Classes\FormatIdentifier;
use Maatwebsite\Excel21\Readers\LaravelExcelReader;
use Maatwebsite\Excel21\Writers\LaravelExcelWriter;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Laravel\Lumen\Application as LumenApplication;

/**
 *
 * LaravelExcel Excel ServiceProvider
 *
 * @category   Laravel Excel
 * @package    maatwebsite/excel21
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ExcelServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */

    public function boot()
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->configure('excel21');
        } else {
            $this->publishes([
                __DIR__ . '/../../config/excel21.php' => config_path('excel21.php'),
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/excel21.php', 'excel21'
        );

        //Set the autosizing settings
        $this->setAutoSizingSettings();
        
        //Enable an "export" method on Eloquent collections. ie: model::all()->export('file');
	    if (method_exists(Collection::class, 'macro')) {
            Collection::macro('export', function($filename, $type = 'xlsx', $method = 'download') {
                $model = $this;
                Facades\Excel21::create($filename, function($excel21) use ($model, $filename) {
                    $excel21->sheet($filename, function($sheet) use ($model) {
                    $sheet->fromModel($model);
                });
            })->$method($type);
        });
	}
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindClasses();
        $this->bindCssParser();
        $this->bindReaders();
        $this->bindParsers();
        $this->bindPHPExcelClass();
        $this->bindWriters();
        $this->bindExcel();
    }

    /**
     * Bind PHPExcel classes
     * @return void
     */
    protected function bindPHPExcelClass()
    {
        // Set object
        $me = $this;

        // Bind the PHPExcel class
        $this->app->singleton('phpexcel21', function () use ($me)
        {
            // Set locale
            $me->setLocale();

            // Set the caching settings
            $me->setCacheSettings();

            // Init phpExcel
            $excel21 = new PHPExcel();
            $excel21->setDefaultProperties();
            return $excel21;
        });

        $this->app->alias('phpexcel21', PHPExcel::class);
    }

    /**
     * Bind the css parser
     */
    protected function bindCssParser()
    {
        // Bind css parser
        $this->app->singleton('excel21.parsers.css', function ()
        {
            return new CssParser(
                new CssToInlineStyles()
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindReaders()
    {
        // Bind the laravel excel21 reader
        $this->app->singleton('excel21.reader', function ($app)
        {
            return new LaravelExcelReader(
                $app['files'],
                $app['excel21.identifier'],
                $app['Illuminate\Contracts\Bus\Dispatcher']
            );
        });

        // Bind the html reader class
        $this->app->singleton('excel21.readers.html', function ($app)
        {
            return new Html(
                $app['excel21.parsers.css']
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindParsers()
    {
        // Bind the view parser
        $this->app->singleton('excel21.parsers.view', function ($app)
        {
            return new ViewParser(
                $app['excel21.readers.html']
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindWriters()
    {
        // Bind the excel21 writer
        $this->app->singleton('excel21.writer', function ($app)
        {
            return new LaravelExcelWriter(
                $app->make(Response::class),
                $app['files'],
                $app['excel21.identifier']
            );
        });
    }

    /**
     * Bind Excel class
     * @return void
     */
    protected function bindExcel()
    {
        // Bind the Excel class and inject its dependencies
        $this->app->singleton('excel21', function ($app)
        {
            $excel21 = new Excel21(
                $app['phpexcel21'],
                $app['excel21.reader'],
                $app['excel21.writer'],
                $app['excel21.parsers.view']
            );

            $excel21->registerFilters($app['config']->get('excel21.filters', array()));

            return $excel21;
        });

        $this->app->alias('excel21', Excel21::class);
    }

    /**
     * Bind other classes
     * @return void
     */
    protected function bindClasses()
    {
        // Bind the format identifier
        $this->app->singleton('excel21.identifier', function ($app)
        {
            return new FormatIdentifier($app['files']);
        });
    }

    /**
     * Set cache settings
     * @return Cache
     */
    public function setCacheSettings()
    {
        return new Cache();
    }

    /**
     * Set locale
     */
    public function setLocale()
    {
        $locale = config('app.locale', 'en_us');
        PHPExcel_Settings::setLocale($locale);
    }

    /**
     * Set the autosizing settings
     */
    public function setAutoSizingSettings()
    {
        $method = config('excel21.export.autosize-method', PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
        PHPExcel_Shared_Font::setAutoSizeMethod($method);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'excel21',
            'phpexcel21',
            'excel21.reader',
            'excel21.readers.html',
            'excel21.parsers.view',
            'excel21.writer'
        ];
    }
}
