<?php
/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 14.03.14
 * Time: 10:21
 */

namespace veyselsahin\giix\crud\providers;

use yii\base\Model;
use yii\helpers\Inflector;

class RelationProvider extends \veyselsahin\giix\base\Provider
{
    public function activeField($column)
    {
        $relation = $this->generator->getRelationByColumn($this->generator->modelClass, $column);
        if ($relation)
        {
            switch (true)
            {
                case (!$relation->multiple):
                    $pk = key($relation->link);

                    $q = new $relation->modelClass;
                    $attrib = "ad";
                    if (!$q->hasAttribute("ad"))
                    {
                        $attrib = "username";
                    }

                    $name = $this->generator->getModelNameAttribute($relation->modelClass);

                    $method = __METHOD__;
                    $code = <<<EOS
// generated by {$method}
\$form->field(\$model, '{$column->name}')->dropDownList(
    \yii\helpers\ArrayHelper::map({$relation->modelClass}::find()->all(),'{$pk}','{$attrib}'),
    ['prompt'=>'Seçin']
);
EOS;
                    return $code;
                default:
                    return null;

            }
        }
    }

    public function attributeFormat($column)
    {
        // do not handle columns with a primary key, TOOD: review(!) should not be omitted in every case
        if ($column->isPrimaryKey)
        {
            return null;
        }
        if ($column->name == "olusturan")
        {
            return $this->olusturanGuncelleyen($column);
        } else if ($column->name == "guncelleyen")
        {
            return $this->olusturanGuncelleyen($column);
        }
        $relation = $this->generator->getRelationByColumn($this->generator->modelClass, $column);


        if ($relation)
        {
            if ($relation->multiple)
            {
                return null;
            }
            $title = $this->generator->getModelNameAttribute($relation->modelClass);
            $route = $this->generator->createRelationRoute($relation, 'view');
            $relationGetter = 'get' . Inflector::id2camel(
                    str_replace('_id', '', $column->name),
                    '_'
                ) . '()'; // TODO: improve detection
            $params = "'id' => \$model->{$column->name}";

            $relationModel = new $relation->modelClass;
            $pks = $relationModel->primaryKey();
            $paramArrayItems = "";
            foreach ($pks as $attr)
            {
                $paramArrayItems .= "'{$attr}' => \$model->{$relationGetter}->one()->{$attr},";
            }
            $q = new $relation->modelClass;
            $title = "ad";
            if (!$q->hasAttribute("ad"))
            {
                $title = "username";
            }
            $method = __METHOD__;
            $code = <<<EOS
// generated by {$method}
[
    'format'=>'html',
    'attribute'=>'$column->name',
    'value' => (\$model->{$relationGetter}->one() ? Html::a(\$model->{$relationGetter}->one()->{$title}, ['{$route}', {$paramArrayItems}]) : '<span class="label label-warning">?</span>'),
]
EOS;
            return $code;
        }
    }

    public function olusturanGuncelleyen($column)
    {
        $method = __METHOD__;
        if ($column->name == "olusturan")
        {
            $code = <<<EOS
// generated by {$method}
[
    'format'=>'html',
    'attribute'=>'$column->name',
    'value' => (\$model->getCreateBy() ? Html::a(\$model->getCreateBy()->username, ['user/profile', 'id'=>\$model->getCreatedBy()->id]) : '<span class="label label-warning">?</span>'),
]
EOS;
            return $code;
        } else if($column->name == "guncelleyen")
        {
            $code = <<<EOS
// generated by {$method}
[
    'format'=>'html',
    'attribute'=>'$column->name',
    'value' => (\$model->getUpdateBy() ? Html::a(\$model->getUpdateBy()->username, ['user/profile', 'id'=>\$model->getUpdateBy()->id]) : '<span class="label label-warning">?</span>'),
]
EOS;
            return $code;

        }
    }

    public function columnFormat($column, $model)
    {
        // do not handle columns with a primary key, TOOD: review(!) should not be omitted in every case
        if ($column->isPrimaryKey)
        {
            return null;
        }

        $relation = $this->generator->getRelationByColumn($model, $column);
        if ($relation)
        {
            if ($relation->multiple)
            {
                return null;
            }
            $title = $this->generator->getModelNameAttribute($relation->modelClass);
            $route = $this->generator->createRelationRoute($relation, 'view');
            $method = __METHOD__;
            $relationGetter = 'get' . Inflector::id2camel(
                    str_replace('_id', '', $column->name),
                    '_'
                ) . '()'; // TODO: improve detection

            $pk = key($relation->link);
            $relationModel = new $relation->modelClass;
            $pks = $relationModel->primaryKey();
            $paramArrayItems = "";
            foreach ($pks as $attr)
            {
                $paramArrayItems .= "'{$attr}' => \$rel->{$attr},";
            }

            if ($column->name == "olusturan")
            {

                $code = <<<EOS
[
            "class" => yii\grid\DataColumn::className(),
            "attribute" => "olusturan",
            "value" => function(\$model){
                \$user=\$model->getUpdateUser();
                return yii\helpers\Html::a(\$user->username,["user/profile", 'id' => \$user->id],["data-pjax"=>0]);
            },
            "format" => "raw"
]
EOS;
                return $code;
            }


            $code = <<<EOS
[
            "class" => yii\\grid\\DataColumn::className(),
            "attribute" => "{$column->name}",
            "value" => function(\$model){
                if (\$rel = \$model->{$relationGetter}->one()) {
                if (isset(\$rel->ad))
                {
                return yii\helpers\Html::a(\$rel->ad,["{$route}", {$paramArrayItems}],["data-pjax"=>0]);
                }
                if (isset(\$rel->username))
                {
                return yii\helpers\Html::a(\$rel->username,["{$route}", {$paramArrayItems}],["data-pjax"=>0]);
                }
                return yii\helpers\Html::a(\$rel->{$title},["{$route}", {$paramArrayItems}],["data-pjax"=>0]);
                } else {
                    return '';
                }
            },
            "format" => "raw",
]
EOS;

            return $code;
        }
    }


    // TODO: params is an array, because we need the name, improve params
    public function relationGrid($data)
    {
        $name = $data[1];
        $relation = $data[0];
        $showAllRecords = isset($data[2]) ? $data[2] : false;
        $model = new $relation->modelClass;
        $counter = 0;
        $columns = '';

        foreach ($model->attributes AS $attr => $value)
        {
            // max seven columns
            if ($counter > 8)
            {
                continue;
            }
            // skip virtual attributes
            if (!isset($model->tableSchema->columns[$attr]))
            {
                continue;
            }
            // don't show current model
            if (key($relation->link) == $attr)
            {
                continue;
            }

            $code = $this->generator->columnFormat($model->tableSchema->columns[$attr], $model);
            if ($code == false)
            {
                continue;
            }
            $columns .= $code . ",\n";
            $counter++;
        }

        $reflection = new \ReflectionClass($relation->modelClass);
        if (!$this->generator->isPivotRelation($relation))
        {
            $template = '{view} {update}';
            $deleteButtonPivot = '';
        } else
        {
            $template = '{view} {delete}';
            $deleteButtonPivot = <<<EOS
'delete' => function (\$url, \$model) {
                return Html::a('<span class="glyphicon glyphicon-remove"></span>', \$url, [
                    'class' => 'text-danger',
                    'title' => Yii::t('yii', 'Remove'),
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete the related item?'),
                    'data-method' => 'get',
                    'data-pjax' => '0',
                ]);
            },
'view' => function (\$url, \$model) {
                return Html::a(
                    '<span class="glyphicon glyphicon-cog"></span>',
                    \$url,
                    [
                        'data-title'  => Yii::t('yii', 'View Pivot Record'),
                        'data-toggle' => 'tooltip',
                        'data-pjax'   => '0',
                        'class'        => 'text-muted'
                    ]
                );
            },
EOS;
        }

        $controller = $this->generator->pathPrefix . Inflector::camel2id($reflection->getShortName(), '-', true);
        $actionColumn = <<<EOS
[
    'class'      => 'yii\grid\ActionColumn',
    'template'   => '$template',
    'contentOptions' => ['nowrap'=>'nowrap'],
    'urlCreator' => function(\$action, \$model, \$key, \$index) {
        // using the column name as key, not mapping to 'id' like the standard generator
        \$params = is_array(\$key) ? \$key : [\$model->primaryKey()[0] => (string) \$key];
        \$params[0] = '$controller' . '/' . \$action;
        return \yii\helpers\Url::toRoute(\$params);
    },
    'buttons'    => [
        $deleteButtonPivot
    ],
    'controller' => '$controller'
]
EOS;
        $columns .= $actionColumn . ",";

        $query = $showAllRecords ?
            "'query' => \\{$relation->modelClass}::find()" :
            "'query' => \$model->get{$name}()";
        $code = '';
        $code .= <<<EOS
\\yii\\grid\\GridView::widget([
    'dataProvider' => new \\yii\\data\\ActiveDataProvider([{$query}, 'pagination' => ['pageSize' => 10]]),
    'columns' => [$columns]
]);
EOS;
        return $code;
    }


}