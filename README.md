ZeDb
====

ZeDb is a zend framework module that provides quick access to the database by
creating entities for tables and allowing you to focus on your entities instead of models.

Installation
------------

The module can be installed using Composer by adding the following lines to your composer.json file:

    "require": {
        "zendexperts/ze-db": "dev-master"
    }
    
In order to use the module you need to add it in the list of modules from `config/application.config.php`
and set the list of entities and models you have along with the database settings in a configuration file:

    'zendexperts_zedb' => array(
        'adapter' => array(
            'driver'    => 'Pdo_Mysql',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => ''
        ),
        'models' => array(
            'Application\Model\User' => array(
                'tableName' => 'users',
                'entityClass' => 'Application\Entity\User',
            ),
        ),
    )


Documentation
-------------

ZeDb allows you to focus on the business side of your application by saving you time when 
working with the database.
It provides you with a powerful set of magic functions that allow you to execute queries easily 
without the need to write sql queries.

ZeDb works out of the box with entity classes, an even though it provides a base Entity class,
you also have the posibility to write your own custom Entities by implementing  ZeDb\EntityInterface.

ZeDb works in a similar way with Doctrine, in the fact that it uses a Manager to retrieve model instances.
These model instances contain magic functions for accessing the database in order to retrieve Entity objects,
remove data or count records.

The Model class defined the following functions:

- `save(EntityInterface $entity)`: saves an entity record into the database and returns it's id
- `persist($entities)`: stores one or more entities locally before saving them into the database
- `flush()`: saves all persisted objects into the database
- `get($id)`: returns an Entity class based on the id of the record

Apart from the above function the model also defines a set of magic functions that can handle combination of 
table columns, order by or limit.

The pattern used by these methods is as follows:

1. Function name prefix, which can be one of the following: 
 - `removeBy`: removes one or more records from the table
 - `getAll`: returns all the records from the table
 - `getBy`: returns a single Entity class based on the values in the specified fields
 - `getAllBy`: same as `getBy` only it returns more that one record, if found
 - `getLike`: returns a single Entity class based on the values in the specified fields using `LIKE` instead of `=`
 - `getAllLike`: same as `getLike` only it returns more that one record, if found
 - `getByColumns`: allows you to specify an array of keys and values that can be passed over to the where method of the Select instance before returning a single entity.
 - `getAllByColumns`: same as `getByColumns` only it returns more that one record

2. A list of field names in camelCase separated by the `And` word. Ex: `$model->getByUsernameAndStatus('paul', 'active');`. This is only needed for the following functions: removeBy, getBy, getAllBy, getLike, getAllLike.

3. The `OrderBy` text followed by a list of field names in camelCase separated by the `And` word where each field name can be suffixed by either `Asc` or `Desc`. This section is optional for all functions.

4. The `Limit` text followed by a number representing the maximum number of records that should be returned. If limit is defined then you may also specify an offest from which to start by adding the text `From` followed by the offset number.
    
The Entity class defined two methods that can help you work with the database faster:
- save(): saves the current entity instance in the database
- delete(): removes the entity from the database

Currently the fields for each Entity instance are kept in a data array for fast access and conversion between object and array, but you can always implement the EntityInterface and create your custom Entity classes.
    
Examples
--------

1. Get a user entity by id in a controller action:

    public function indexAction()
    {
        $manager = $this->getServiceLocator()->get('ZeDbManager');
        $model = $manager->get('Application\Entity\User');
        $user = $model->getById(1);
        return array();
    }
