<?php

namespace veyselsahin\giix;

use yii\base\Application;
use yii\base\BootstrapInterface;


/**
 * Class Bootstrap
 * @package veyselsahin\giix
 * @author Veysel Şahin <hello@veyselsahin.com.tr>
 */
class Bootstrap implements BootstrapInterface
{
    

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
   
        if ($app->hasModule('gii')) {

            if (!isset($app->getModule('gii')->generators['giix-model'])) {
                $app->getModule('gii')->generators['giix-model'] = 'veyselsahin\giix\model\Generator';
            }
            if (!isset($app->getModule('gii')->generators['giix-crud'])) {
                $app->getModule('gii')->generators['giix-crud'] = 'veyselsahin\giix\crud\Generator';
            }
            if ($app instanceof \yii\console\Application) {
                $app->controllerMap['giix-batch'] = 'veyselsahin\giix\commands\BatchController';
            }
        }
    }
}