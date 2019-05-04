<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Exception;
use yii\console\Controller;
use app\modules\software_component\models\SoftwareComponent;

/**
 * SoftwareComponentController реализует консольные команды для работы с программными компонентами.
 * Создание программных компонент по умолчанию.
 */
class SoftwareComponentController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii software-component/create' . PHP_EOL;
        echo 'yii software-component/remove' . PHP_EOL;
        echo 'yii software-component/all-remove' . PHP_EOL;
    }

    /**
     * Команда создания программных компонент по умолчанию.
     */
    public function actionCreate()
    {
        $integrated_ont_analysis_component = new SoftwareComponent();
        // Если не создан ни один программный компонент
        if ($integrated_ont_analysis_component->find()->count() == 0) {
            // Создание программного компонента генерации (RULE-CLIPS)
            $integrated_clips_generation_component = new SoftwareComponent();
            $integrated_clips_generation_component->name = 'Генератор продукционной модели в CLIPS';
            $integrated_clips_generation_component->description = 'Генерация кода базы знаний в формате CLIPS на основе преобразования продукционной модели.';
            $integrated_clips_generation_component->type = SoftwareComponent::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT;
            $integrated_clips_generation_component->status = SoftwareComponent::STATUS_GENERATED;
            $integrated_clips_generation_component->author = 1;
            $this->log($integrated_clips_generation_component->save());

            // Создание программного компонента генерации (ONT-OWL)
            $integrated_owl_generation_component = new SoftwareComponent();
            $integrated_owl_generation_component->name = 'Генератор онтологической модели в OWL';
            $integrated_owl_generation_component->description = 'Генерация кода базы знаний (онтологии) в формате OWL на основе преобразования онтологической модели.';
            $integrated_owl_generation_component->type = SoftwareComponent::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT;
            $integrated_owl_generation_component->status = SoftwareComponent::STATUS_GENERATED;
            $integrated_owl_generation_component->author = 1;
            $this->log($integrated_owl_generation_component->save());

            // Создание программного компонента анализа (CM-RULE)
            $integrated_rule_analysis_component = new SoftwareComponent();
            $integrated_rule_analysis_component->name = 'Генератор UML-RULES';
            $integrated_rule_analysis_component->description = 'Данный программный компонент предназначен для анализа UML-моделей (диаграмм классов). Компонент извлекает понятия, свойства и их отношения. На основе выделенных элементов формируется продукционная модель (внутреннее представление системы).';
            $integrated_rule_analysis_component->type = SoftwareComponent::TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT;
            $integrated_rule_analysis_component->status = SoftwareComponent::STATUS_DESIGN;
            $integrated_rule_analysis_component->author = 1;
            $this->log($integrated_rule_analysis_component->save());

            // Создание программного компонента анализа (CM-ONT)
            $integrated_ont_analysis_component->name = 'Генератор UML-ONT';
            $integrated_ont_analysis_component->description = 'Данный программный компонент предназначен для анализа UML-моделей (диаграмм классов). Компонент извлекает понятия, свойства и их отношения. На основе выделенных элементов формируется модель онтологии (внутреннее представление системы).';
            $integrated_ont_analysis_component->type = SoftwareComponent::TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT;
            $integrated_ont_analysis_component->status = SoftwareComponent::STATUS_DESIGN;
            $integrated_ont_analysis_component->author = 1;
            $this->log($integrated_ont_analysis_component->save());

            // Создание программного компонента генерации (CM-CLIPS)
            $autonomous_clips_generation_component = new SoftwareComponent();
            $autonomous_clips_generation_component->name = 'Прямой генератор CLIPS';
            $autonomous_clips_generation_component->description = 'Прямой генератор CLIPS из концептуальных моделей.';
            $autonomous_clips_generation_component->type = SoftwareComponent::TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT;
            $autonomous_clips_generation_component->status = SoftwareComponent::STATUS_DESIGN;
            $autonomous_clips_generation_component->author = 1;
            $this->log($autonomous_clips_generation_component->save());

            // Создание программного компонента генерации (CM-OWL)
            $autonomous_owl_generation_component = new SoftwareComponent();
            $autonomous_owl_generation_component->name = 'Прямой генератор OWL';
            $autonomous_owl_generation_component->description = 'Прямой генератор OWL из концептуальных моделей.';
            $autonomous_owl_generation_component->type = SoftwareComponent::TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT;
            $autonomous_owl_generation_component->status = SoftwareComponent::STATUS_DESIGN;
            $autonomous_owl_generation_component->author = 1;
            $this->log($autonomous_owl_generation_component->save());
        } else
            $this->stdout('Software default components are created!', Console::FG_GREEN, Console::BOLD);
    }

    /**
     * Команда удаления программного компонента по наименованию.
     */
    public function actionRemove()
    {
        $name = $this->prompt('Name:', ['required' => true]);
        $model = $this->findModel($name);
        $this->log($model->delete());
    }

    /**
     * Команда удаления всех программных компонентов.
     */
    public function actionAllRemove()
    {
        $model = new SoftwareComponent();
        $this->log($model->deleteAll());
    }

    /**
     * Поиск программного компонента по наименованию.
     * @param string $name
     * @throws \yii\console\Exception
     * @return SoftwareComponent the loaded model
     */
    private function findModel($name)
    {
        if (!$model = SoftwareComponent::findOne(['name' => $name])) {
            throw new Exception('Software component not found!');
        }
        return $model;
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}