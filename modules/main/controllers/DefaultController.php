<?php

namespace app\modules\main\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Главная страница сайта.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Страница помощи.
     * @return string
     */
    public function actionHelp()
    {
        return $this->render('help');
    }
}