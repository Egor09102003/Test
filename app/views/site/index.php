<?php

/**
 * @var \yii\data\ArrayDataProvider $dataProvider
 * @todo: implement grid view (title, PHP version, TYPO3 version with status, Extensions status, last update)
 */
$this->registerCss('
    .green-checkmark {
        color: green;
        font-size: 20px;
    }
    .red-exclamation {
        color: red;
        font-size: 20px; 
        font-weight: bold;
    }
');

echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout'=>"{items}",
    'columns' => [
        [
            'attribute' => 'name',
            'label' => 'Website'
        ],
        [
            'attribute' => 'PHP',
            'label' => 'PHP'
        ],
        [
            'attribute' => 'TYPO3',
            'label' => 'TYPO3',
            'contentOptions' => function ($data) {
                if ($data['typo3_support'] == 2) {
                    return ['class' => 'danger'];
                } else if ($data['typo3_support'] == 1) {
                    return ['class' => 'warning'];
                }
                return [];
            }
        ],
        [
            'attribute' => 'extension',
            'label' => 'Extensions',
            'format' => 'html',
            'value' => function ($model) {
                return $model['extension'] == 1 ? '<span class="green-checkmark">&#10004;</span>' : '<span class="red-exclamation">&#9888;</span>';
            },
            'contentOptions' => function ($data) {
                if ($data['extension']) {
                    $class = 'success';
                } else {
                    $class = 'danger';
                }
                return ['class' => $class, 'style' => 'text-align:center'];
            }
        ],
        [
            'attribute' => 'last_update',
            'label' => 'Last update'
        ]
    ]
]);

