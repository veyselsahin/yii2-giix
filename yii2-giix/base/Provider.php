<?php
/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 19.03.14
 * Time: 01:02
 */

namespace veyselsahin\giix\base;


use yii\base\Object;

class Provider extends Object
{
    /**
     * @var \fproject\giix\crud\Generator
     */
    public $generator;
    public $columnNames = [''];
} 