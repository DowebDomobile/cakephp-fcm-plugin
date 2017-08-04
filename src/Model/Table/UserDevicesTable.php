<?php
/**
 *
 */
namespace Dwdm\Fcm\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Dwdm\Fcm\Model\Entity\UserDevice;

/**
 * UserDevices Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method UserDevice get($primaryKey, $options = [])
 * @method UserDevice newEntity($data = null, array $options = [])
 * @method UserDevice[] newEntities(array $data, array $options = [])
 * @method UserDevice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method UserDevice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method UserDevice[] patchEntities($entities, array $data, array $options = [])
 * @method UserDevice findOrCreate($search, callable $callback = null, $options = [])
 */
class UserDevicesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('user_devices');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('token', 'create')
            ->notEmpty('token');

        $validator
            ->requirePresence('system', 'create')
            ->notEmpty('system');

        $validator
            ->requirePresence('version', 'create')
            ->notEmpty('version');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Extract possible systems.
     *
     * @param Query $query
     * @param array $options
     * @return Query
     */
    public function findSystemList(Query $query, array $options = [])
    {
        return $query->find('list', ['keyField' => 'system', 'valueField' => 'system'])->group('system');
    }

    /**
     * Extract possible versions.
     *
     * @param Query $query
     * @param array $options
     * @return Query
     */
    public function findVersionList(Query $query, array $options = [])
    {
        return $query->find('list', ['keyField' => 'version', 'valueField' => 'system'])->group(['system', 'version']);
    }
}
