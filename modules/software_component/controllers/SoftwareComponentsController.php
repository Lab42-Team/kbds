<?php

namespace app\modules\software_component\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\software_component\models\SoftwareComponent;
use app\modules\software_component\models\SoftwareComponentSearch;

/**
 * SoftwareComponentsController implements the CRUD actions for SoftwareComponent model.
 */
class SoftwareComponentsController extends Controller
{
    public $layout = "/column1";

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all SoftwareComponent models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new SoftwareComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SoftwareComponent model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SoftwareComponent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SoftwareComponent();

        if ($model->load(Yii::$app->request->post())) {
            $model->author = Yii::$app->user->identity->getId();
            if ($model->save()) {
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_MESSAGE_CREATE_SOFTWARE_COMPONENT'));

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SoftwareComponent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_MESSAGE_UPDATED_SOFTWARE_COMPONENT'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SoftwareComponent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->setFlash('success',
            Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_MESSAGE_DELETED_SOFTWARE_COMPONENT'));

        return $this->redirect(['list']);
    }

    /**
     * Displays transformation model code TMRL for this software component.
     * @param integer $id
     * @return mixed
     */
    public function actionViewTmrlCode($id)
    {
        $model = $this->findModel($id);
        $tmrl_code = "";
        // Открытие TMRL-файла модели трансформации для чтения
        $file_handle = fopen($model->file_name, "r");
        // Пока не достигнут конец файла
        while (!feof($file_handle)) {
            // Получение строки из TMRL-файла модели трансформации
            $line = fgets($file_handle);
            // Поиск определенных нетерминалов
            $pos1 = strpos($line, '{');
            $pos2 = strpos($line, 'Elements [');
            $pos3 = strpos($line, ']');
            $pos4 = strpos($line, 'Relationships [');
            $pos5 = strpos($line, '[');
            $pos6 = strpos($line, '}');
            // Условия вывода полученных строк
            if ($pos1 || $pos6 !== false)
                $tmrl_code .= $line . "<br />";
            if ($pos2 || $pos3 == 1 || $pos4 || ($pos2 === false && $pos5))
                $tmrl_code .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $line . "<br />";
            if ($pos1 == false && $pos2 == false && $pos3 != 1 && $pos6 === false && $pos5 == false)
                $tmrl_code .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $line . "<br />";
        }

        return $this->render('view-tmrl-code', [
            'model' => $this->findModel($id),
            'tmrl_code' => $tmrl_code
        ]);
    }

    /**
     * Finds the SoftwareComponent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SoftwareComponent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SoftwareComponent::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}
