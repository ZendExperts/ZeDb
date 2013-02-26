<?php
return array(
    'factories'=>array(
        'ZeDbManager' => 'ZeDb\Service\DatabaseManagerFactory',
        'Zend\Db\Adapter\Adapter'=>'ZeDb\Service\AdapterFactory',
    ),
);