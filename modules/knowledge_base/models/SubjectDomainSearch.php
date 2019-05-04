<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\knowledge_base\models\SubjectDomain;

/**
 * SubjectDomainSearch represents the model behind the search form about `app\modules\knowledge_base\models\SubjectDomain`.
 */
class SubjectDomainSearch extends SubjectDomain
{
    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'author'], 'integer'],
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
        $query = SubjectDomain::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5,
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
            'author' => $this->author,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
