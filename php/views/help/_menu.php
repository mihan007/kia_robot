<?php
/**
 * @var $topic string
 */
use yii\helpers\Html;

$items = [];
if (\Yii::$app->user->isAdmin) {
    $items['turbodealer'] = 'Сотруднику Турбодилера';
    $items['leadManager'] = 'Руководителю салона';
    $items['manager'] = 'Менеджеру салона';
} elseif (\Yii::$app->user->isLeadManager) {
    $items['leadManager'] = 'Руководителю салона';
    $items['manager'] = 'Менеджеру салона';
} else {
    $items['manager'] = 'Менеджеру салона';
}
?>
<div class="list-group">
    <?php foreach ($items as $topicSlug => $topicName): ?>
        <?php $class = ($topic == $topicSlug) ? 'list-group-item active' : 'list-group-item'; ?>
        <?php echo Html::a($topicName, ['/help/topic', 'topic' => $topicSlug], ['class' => $class]) ?>
    <?php endforeach; ?>
</div>