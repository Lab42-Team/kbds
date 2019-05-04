<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * KnowledgeBaseSearch represents the model behind the search form about `app\modules\knowledge_base\models\KnowledgeBase`.
 */
class KnowledgeBaseSearch extends KnowledgeBase
{
    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'type', 'status', 'author', 'subject_domain'], 'integer'],
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
     * @param integer $user_id
     * @return ActiveDataProvider
     */
    public function search($params, $user_id)
    {
        $query = KnowledgeBase::find();

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
            'type' => $this->type,
            'status' => $this->status,
            'author' => $user_id == null ? $this->author : $user_id, // выборка БЗ по автору, если указан его id
            'subject_domain' => $this->subject_domain,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}