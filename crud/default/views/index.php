<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use veyselsahin\giix\crud\providers\RelationProvider;

if (!function_exists("getColumn"))
{
    function getColumn($uptOrCrt)
    {

        $attr = $uptOrCrt == "Update" ? "guncelleyen" : "olusturan";
        $c = <<<EOS
    [
        "class" => yii\grid\DataColumn::className(),
        "attribute" => "{$attr}",
        "value" => function(\$model){
        \$user=\$model->get{$uptOrCrt}User();
        if(isset(\$user->username)){
                return yii\helpers\Html::a(\$user->username,["user/profile", 'id' => \$user->id],["data-pjax"=>0]);
                }
                else{
                return '';
                }
            },
            "format" => "raw"
]
EOS;
        return $c;
    }
}
/**
 * @var yii\web\View $this
 * @var veyselsahin\giix\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

    use yii\helpers\Html;
    use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

    /**
    * @var yii\web\View $this
    * @var yii\data\ActiveDataProvider $dataProvider
    * @var <?= ltrim($generator->searchModelClass, '\\') ?> $searchModel
    */

    $this->title = '<?= Inflector::camel2words(StringHelper::basename($generator->modelClass)) ?>';
    $this->params['breadcrumbs'][] = $this->title;
    ?>

    <div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-index">

        <?=
        "<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>
        echo $this->render('_search', ['model' =>$searchModel]);
        ?>

        <div class="clearfix">
            <p class="pull-left">
                <?= "<?= " ?>Html::a('<span class="glyphicon glyphicon-plus"></span>
                Yeni <?= Inflector::camel2words(StringHelper::basename($generator->modelClass)) ?>', ['create'],
                ['class' => 'btn btn-success']) ?>
            </p>

            <p class="pull-left">
                <?= \yii\helpers\Html::a('<span class="glyphicon glyphicon-shopping-cart"></span>
                Çöp Kutusu', 'cop',
                    ['class' => 'btn btn-success']) ?>
            </p>

            <div class="pull-right">


                <?php
                $items = [];
                $model = new $generator->modelClass;
                ?>
                <?php foreach ($generator->getModelRelations($model) AS $relation): ?>
                    <?php
                    // relation dropdown links
                    $iconType = ($relation->multiple) ? 'arrow-right' : 'arrow-left';
                    if ($generator->isPivotRelation($relation))
                    {
                        $iconType = 'random';
                    }
                    $controller = $generator->pathPrefix . Inflector::camel2id(
                            StringHelper::basename($relation->modelClass),
                            '-',
                            true
                        );
                    $route = $generator->createRelationRoute($relation, 'index');
                    $label = Inflector::titleize(StringHelper::basename($relation->modelClass), '-', true);
                    $items[] = [
                        'label' => '<i class="glyphicon glyphicon-' . $iconType . '"> ' . $label . '</i>',
                        'url' => [$route]
                    ]
                    ?>
                <?php endforeach; ?>

                <?= "<?php \n" ?>
                echo \yii\bootstrap\ButtonDropdown::widget(
                [
                'id' => 'giix-relations',
                'encodeLabel' => false,
                'label' => '<span class="glyphicon glyphicon-paperclip"></span> İlişkili',
                'dropdown' => [
                'options' => [
                'class' => 'dropdown-menu-right'
                ],
                'encodeLabels' => false,
                'items' => <?= \yii\helpers\VarDumper::export($items) ?>
                ],
                ]
                );
                <?= "?>" ?>
            </div>
        </div>

        <?php if ($generator->indexWidgetType === 'grid'): ?>
            <?= "<?php " ?>echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
            <?php
            $count = 0;
            echo "\n"; // code-formatting
            foreach ($generator->getTableSchema()->columns as $column)
            {
                if ($column->name == "olusturan")
                {
                    $format = getColumn("Create");
                } else if ($column->name == "guncelleyen")
                {
                    $format = getColumn("Update");
                } else
                {
                    $format = trim($generator->columnFormat($column, $model));
                }

                if ($column->name != "silindi")
                {
                    if ($format == false) continue;
                    if (++$count < 8)
                    {
                        echo "\t\t\t{$format},\n";
                    } else
                    {
                        echo "\t\t\t{$format},\n";
                    }
                }
            }
            ?>
            [
            'class' => '<?= $generator->actionButtonClass ?>',
            'urlCreator' => function($action, $model, $key, $index) {
            // using the column name as key, not mapping to 'id' like the standard generator
            $params = is_array($key) ? $key : [$model->primaryKey()[0] => (string) $key];
            $params[0] = \Yii::$app->controller->id ? \Yii::$app->controller->id . '/' . $action : $action;
            return \yii\helpers\Url::toRoute($params);
            },
            'contentOptions' => ['nowrap'=>'nowrap']
            ],
            ],
            ]); ?>
        <?php else: ?>
            <?= "<?php " ?>echo ListView::widget([
            'dataProvider' => $dataProvider,
            'itemOptions' => ['class' => 'item'],
            'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
            },
            ]); ?>
        <?php endif; ?>

    </div>


<?php

?>