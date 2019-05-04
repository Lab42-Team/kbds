<?php

namespace app\modules\software_component\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\software_component\models\TransformationModel;

/**
 * TransformationModelSearch represents the model behind the search form about `app\modules\software_component\models\TransformationModel`.
 */
class TransformationModelSearch extends TransformationModel
{
    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'software_component', 'source_metamodel', 'target_metamodel'], 'integer'],
            [['name', 'description'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TransformationModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'software_component' => $this->software_component,
            'source_metamodel' => $this->source_metamodel,
            'target_metamodel' => $this->target_metamodel,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}