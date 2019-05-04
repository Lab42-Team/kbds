<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\user\models\User;

/**
 * UserSearch represents the model behind the search form about `app\modules\user\models\User`.
 */
class UserSearch extends User
{
    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'status'], 'integer'],
            [['username', 'email', 'role'], 'safe'],
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
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        // Сортировка
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'username',
                'email',
                'status',
                'role' => [
                    'asc' => ['kbds_auth_assignment.item_name' => SORT_ASC],
                    'desc' => ['kbds_auth_assignment.item_name' => SORT_DESC],
                    // Для MySQL на хостинге:
                    //'asc' => ['auth_assignment.item_name' => SORT_ASC],
                    //'desc' => ['auth_assignment.item_name' => SORT_DESC],
                    'label' => 'Role',
                    'default' => SORT_ASC
                ],
                'created_at'
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Выборка по роли пользователя
        $query->join('LEFT JOIN', 'kbds_auth_assignment',
            'kbds_auth_assignment.user_id = CAST(kbds_user.id AS VARCHAR(64))')
            ->andFilterWhere(['kbds_auth_assignment.item_name' => $this->role]);
        // Для MySQL на хостинге:
        //$query->join('LEFT JOIN', 'auth_assignment',
        //    'auth_assignment.user_id = CAST(user.id AS CHAR) COLLATE utf8_unicode_ci')
        //    ->andFilterWhere(['auth_assignment.item_name' => $this->role]);

        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'status' => $this->status
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'email_confirm_token', $this->email_confirm_token])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}