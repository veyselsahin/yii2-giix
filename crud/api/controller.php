<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\web\View $this
 * @var veyselsahin\giix\crud\Generator $generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass.'Search';
}

$pks = $generator->getTableSchema()->primaryKey;
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$actionParamCommentsCreate = $generator->generateCreateActionParamComments();

echo "<?php\n";

$class = $generator->modelClass;
$pks = $class::primaryKey();
if(count($pks)>0){
    $pks="id";
}
?>
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use <?= ltrim($generator->modelClass, '\\') ?>;

use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\web\ServerErrorHttpException;
/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    public $modelClass = '<?=$generator->modelClass?>';
    /*  public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        unset($actions['search']);

     return $actions;
    }*/

    /**
    * @SWG\Get(path="/<?=\yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass))?>",
    *     tags={"<?=StringHelper::basename($generator->modelClass)?>"},
    *     summary="Index",
    *     description="Lists all <?=$modelClass?>",
    *     produces={"application/json","application/xml"},
    *       @SWG\Response(
    *         response = 200,
    *         description = "User collection response",
    *         @SWG\Schema(ref = "<?=$modelClass?>")
    *     ))
    */
	public function actionIndex()
	{
        $modelClass = $this->modelClass;

        return \Yii::createObject([
        'class' => ActiveDataProvider::className(),
        'query' => $modelClass::find(),
        ]);
	}

    /**
    * @SWG\Get(path="/<?=\yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass))?>/view",
    *     tags={"<?=StringHelper::basename($generator->modelClass)?>"},
    *     summary="View",
    *     description="View a <?=$modelClass?>",
    *     produces={"application/json","application/xml"},
    *     @SWG\Parameter(
    *        in = "query",
    *        name = "<?=$pks?>",
    *        required = true,
    *        type = "integer"
    *     ),
    *     @SWG\Response(
    *         response = 200,
    *         description = "view data response",
    *         @SWG\Schema(ref = "<?=$modelClass?>")
    *     ))
    *
    */
	public function actionView(<?= $actionParams ?>)
	{
        Url::remember();
        return $this->render('view', [
			'model' => $this->findModel(<?= $actionParams ?>),
		]);
	}

    /**
    * @SWG\Post(path="/<?=\yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass))?>/create",
    *     tags={"<?=StringHelper::basename($generator->modelClass)?>"},
    *     summary="Create",
    *     description="Create a <?=$modelClass?>",
    *     produces={"application/json","application/xml"},
    <?php foreach ($actionParamCommentsCreate as $comment) {
        echo $comment;
    }?>
    *       @SWG\Response(
    *         response = 200,
    *         description = "<?=$modelClass?> create response",
    *         @SWG\Schema(ref = "<?=$modelClass?>")
    *     ))
    *
    */
    public function actionCreate()
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
        'scenario' => $this->scenario,
        ]);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(201);
        $id = implode(',', array_values($model->getPrimaryKey(true)));
        $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
        throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }
    /**
    * @SWG\Put(path="/<?=\yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass))?>/update",
    *     tags={"<?=StringHelper::basename($generator->modelClass)?>"},
    *     summary="Update",
    *     description="Update a <?=$modelClass?>",
    *     produces={"application/json","application/xml"},
    <?php foreach ($actionParamCommentsCreate as $comment) {
        echo $comment;
    }?>
    *     @SWG\Parameter(
    *        in = "query",
    *        name = "<?=$pks?>",
    *        required = true,
    *        type = "string"
    *     ),
    *   @SWG\Response(
    *         response = 200,
    *         description = "<?=$modelClass?> update response",
    *         @SWG\Schema(ref = "<?=$modelClass?>")
    *     ))
    *
    */
	public function actionUpdate(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);

        $model->scenario = $this->scenario;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
        throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return $model;
	}

    /**
    * @SWG\Delete(path="/<?=\yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass))?>/delete",
    *     tags={"<?=StringHelper::basename($generator->modelClass)?>"},
    *     summary="Delete",
    *     description="Deletes a <?=$modelClass?>",
    *     produces={"application/json","application/xml"},
    *     @SWG\Parameter(
    *        in = "query",
    *        name = "<?=$pks?>",
    *        required = true,
    *        type = "integer"
    *     ),
    *       @SWG\Response(
    *         response = 200,
    *         description = "<?=$modelClass?> delete response",
    *         @SWG\Schema(ref = "<?=$modelClass?>")
    *     ))
    *
    */
	public function actionDelete(<?= $actionParams ?>)
	{

        $model = $this->findModel(<?= $actionParams ?>);


        if ($model->delete() === false) {
        throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        \Yii::$app->getResponse()->setStatusCode(204);
	}


}
