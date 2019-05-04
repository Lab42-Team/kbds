<?php

namespace app\modules\editor\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\OntologyClass;
use app\modules\knowledge_base\models\Object;
use app\modules\knowledge_base\models\Relationship;
use app\modules\knowledge_base\models\ObjectRelationship;
use app\modules\knowledge_base\models\Property;
use app\modules\knowledge_base\models\PropertyValue;
use app\modules\knowledge_base\models\LeftHandSide;
use app\modules\knowledge_base\models\RightHandSide;

/**
 * OntologyEditorController implements the actions for ontology editor.
 */
class OntologyEditorController extends Controller
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
                        'roles' => ['developer']
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays a single knowledge base (ontology model) in graphical form.
     * @param integer $id - идентификатор модели онтологии
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        // Деактивация правого меню
        $this->layout = "/main";
        // Получение модели онтологии по ее id
        $model = $this->findModel($id);
        // Поиск всех типов данных принадлежащие данной модели онтологии
        $data_types = DataType::find()->where(array('knowledge_base' => $model->id))->asArray()->all();
        // Поиск всех классов принадлежащие данной модели онтологии
        $classes = OntologyClass::find()->where(array('ontology' => $model->id))->asArray()->all();
        // Поиск всех объектов принадлежащие данной модели онтологии
        $objects = Object::find()->where(array('ontology' => $model->id))->asArray()->all();
        // Поиск всех отношений принадлежащие данной модели онтологии
        $relationships = Relationship::find()->where(array('ontology' => $model->id))->asArray()->all();
        // Поиск всех свойств классов
        $properties = Property::find()->asArray()->all();
        // Поиск всех значений свойств классов
        $property_values = PropertyValue::find()->asArray()->all();
        // Поиск всех отношений объектов
        $object_relationships = ObjectRelationship::find()->asArray()->all();
        // Поиск всех левых частей отношений классов
        $left_hand_sides = LeftHandSide::find()->asArray()->all();
        // Поиск всех правых частей отношений классов
        $right_hand_sides = RightHandSide::find()->asArray()->all();

        return $this->render('index', [
            'model' => $model,
            'data_types' => $data_types,
            'classes' => $classes,
            'objects' => $objects,
            'relationships' => $relationships,
            'properties' => $properties,
            'property_values' => $property_values,
            'object_relationships' => $object_relationships,
            'left_hand_sides' => $left_hand_sides,
            'right_hand_sides' => $right_hand_sides
        ]);
    }

    /**
     * Finds the KnowledgeBase model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return KnowledgeBase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = KnowledgeBase::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}